<?php

namespace App\Http\Controllers;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Redirect;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
//use Tzsk\Payu\Facade\Payment;
use App\User;
use Sender\TransactionalSms;
use Illuminate\Support\Facades\DB;
use Srmklive\PayPal\Services\ExpressCheckout;


use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;


class PaymentController extends Controller
{
    public function __construct()
    {
       $paypal_conf = \Config::get('paypal');
       $this->_api_context = new ApiContext(new OAuthTokenCredential($paypal_conf['sandbox_client_id'], $paypal_conf['sandbox_secret']));
       $this->_api_context->setConfig($paypal_conf['settings']);

    }
    public function payment(Request $request)
    {
        $input = array_except($request->all(), array('_token'));
        //print_r($input);die;
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($input['name'])
            ->setCurrency('INR')
            ->setQuantity(1)
            ->setPrice($input['Amount']);

        $item_list = new ItemList();
        $item_list->setItems(array($item));

        $amount = new Amount();
        $amount->setCurrency('INR')
            ->setTotal($input['Amount']);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription($input['itinerary']);

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::route('paypal.status'))
            ->setCancelUrl(URL::route('paypal.status'));

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));

        try{
            $payment->create($this->_api_context);
        }catch(\PayPal\Exception\PPConnectionException $ex){
            print_r($ex);
        }

        foreach ($payment->getLinks() as $link) {
            if($link->getRel() === 'approval_url'){
                $redirect_url = $link->getHref();
                break;
            }
        }

        Session::put('paypal_payment_id', $payment->getId());
        if(isset($redirect_url)){
            return Redirect::away($redirect_url);
        }

        Session::put('error', 'Unknown error occurred');
        return redirect()->back()->with('error', 'Payment failed!');
    }
    public function getPaypalStatus()
    {
        $payment_id = Session::get('paypal_payment_id');

        Session::forget('paypal_payment_id');

        if(empty(Input::get('PayerID')) || empty(Input::get('token'))) {
            \Session::put('error', 'Payment failed');
            return redirect()->back()->with('error', 'Payment failed!');
        }

        $payment = Payment::get($payment_id, $this->_api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId(Input::get('PayerID'));

        $result = $payment->execute($execution, $this->_api_context);
        if($result->getState() === 'approved'){
            \Session::put('success', 'Payment success');
            return redirect()->route('userDashboard')->with('paymentDone','Payment Done.!'); 
        }
        \Session::put('error', 'Payment failed');
         return redirect()->back()->with('error', 'Payment failed!');
    }

    public function status()
    {
    	$payment = Payment::capture();
    	if($payment->isCaptured()){
    		$userData = User::where('email',Session::get('User_email'))->first();
    		$data=array('account'=>$payment->account, 'payable_id'=>0, 'payable_type'=>'', 'txnid'=>$payment->txnid, 'mihpayid'=> $payment->mihpayid, 'firstname'=>$userData->name, 'lastname'=>$userData->name, 'email'=>$userData->email,'phone'=>$userData->phone,'amount'=>$payment->net_amount_debit, 'discount'=> $payment->discount, 'net_amount_debit'=> $payment->net_amount_debit, 'data'=>$payment->data, 'status'=>$payment->status, 'unmappedstatus'=>$payment->unmappedstatus, 'mode'=>$payment->mode,'bank_ref_num'=>$payment->bank_ref_num,'bankcode'=> ''/*$payment->bankcode*/,'cardnum'=>$payment->cardnum,'name_on_card'=>$payment->name_on_card,'issuing_bank'=>$payment->issuing_bank,'card_type'=>'','itinerary'=>$itinerary, 'carInfo'=>$info);
    		$paid=DB::table('payu_payments')->insert($data);
            //print_r($paid);
    		if($paid){
                $message = "Hello ".$userData->name.", You're trip for ". Session::get('itinerary') ." booked with ".Session::get('info')." is confirm. Total amount is ".$payment->net_amount_debit." Thank you.";
                $phone = "91".$request->phone;
                $sample = [ 
                    'message'      => $message,
                    'sender'       => 'ATSONI',
                    'country'      => 91,
                ];

                $sms = new TransactionalSms("274119AWWVwpDgv3Y5cc3205c");
                $sms->sendTransactional($phone,$sample);
                
                Session::forget('itinerary');
                Session::forget('info');
                
    			return redirect()->route('userDashboard')->with('paymentDone','Payment booked.!');
    		}

    	}else{
    		return redirect()->back()->with('error', 'Payment failed!');
    	}
    }
    public function cancelBooking($id)
    {
        $cancel_user = DB::table('payu_payments')->where('id',$id)->get();
        foreach ($cancel_user as $data) {
            $message = "Cancel booking of ".$data->itinerary." by ".$data->firstname." on ".$data->trip_start_date;
            $phone = "918200919647";
            $sample = [ 
                'message'      => $message,
                'sender'       => 'ATSONI',
                'country'      => 91,
            ];

            $sms = new TransactionalSms("274119AWWVwpDgv3Y5cc3205c");
            $sms->sendTransactional($phone,$sample);
            return redirect()->route('userDashboard')->with('paymentCancel','Payment cancel.!');            
        }
    }
    public function localPaymentCOD(Request $request)
    {
        print_r($request->all());die;
    /*
        $data=array('firstname'=>$request->name, 'email'=>$request->email,'phone'=>$request->phone,'amount'=>$request->Amount1, 'net_amount_debit'=> $request->Amount1, 'paymentMethod'=>'COD', 'itinerary'=>$request->itinerary, 'carInfo'=>$request->info, 'trip_start_date' => date( 'Y-m-d', strtotime($request->pickUpDate)), 'pickUpAddress'=>$request->pickUpAddress, 'toAreaName'=>Session::get('to'));
        $paid=DB::table('payu_payments')->insert($data);
        if($paid){
            $message = "Hello ".$request->name.", You're trip on".$request->pickUpDate.' '.$request->pickUpTime." for ". $request->itinerary ." booked with ".$request->info." is confirm. Total amount is ".$request->Amount1." Thank you.";
                
                
                $phone = "91".$request->phone;
                $sample = [ 
                    'message'      => $message,
                    'sender'       => 'ATSONI',
                    'country'      => 91,
                ];

                $sms = new TransactionalSms("274119AWWVwpDgv3Y5cc3205c");
                $sms->sendTransactional($phone,$sample);
                
                
                $admin_message = $request->name.", Has booked on ".$request->pickUpDate.' '.$request->pickUpTime .' '. $request->info ." for ".$request->itinerary." is confirm. Total Amount ".$request->Amount1." pay by C.O.D. Thank you.";
                
                
                $phone = "918200919647";
                $sample = [ 
                    'message'      => $message,
                    'sender'       => 'ATSONI',
                    'country'      => 91,
                ];

                $sms = new TransactionalSms("274119AWWVwpDgv3Y5cc3205c");
                $sms->sendTransactional($phone,$sample);
                
                
                Session::forget('itinerary');
                Session::forget('info');

                Session::forget('from');
                Session::forget('to');
                Session::forget('carName');
                Session::forget('hrs');
                Session::forget('km');
                Session::forget('price');
                
                return redirect()->route('userDashboard')->with('paymentDone','Payment booked.!');
        }*/
    }
    public function paymentCOD(Request $request)
    {
        //print_r($request->all());die;
        
        $userData = User::where('email',Session::get('User_email'))->first();
            
            $request->session()->put('itinerary',$request->itinerary);
            $request->session()->put('info',$request->info);
            
            $itinerary = Session::get('itinerary');
            $info = Session::get('info');
                
            $data=array('firstname'=>$userData->name, 'email'=>$userData->email,'phone'=>$userData->phone,'amount'=>$request->Amount1, 'net_amount_debit'=> $request->Amount1, 'paymentMethod'=>'COD', 'itinerary'=>$itinerary, 'carInfo'=>$info, 'pickUpAddress' => $request->pickUpAddress, 'toAreaName'=> '');
            
            $paid=DB::table('payu_payments')->insert($data);
            
            
            //print_r($paid);
            if($paid){
                
                $message = "Hello ".$userData->name.", You're trip for ". $itinerary ." booked with ".$info." is confirm. Total amount is ".$request->Amount1." Thank you.";
                
                
                $phone = "91".$request->phone;
                $sample = [ 
                    'message'      => $message,
                    'sender'       => 'ATSONI',
                    'country'      => 91,
                ];

                $sms = new TransactionalSms("274119AWWVwpDgv3Y5cc3205c");
                $sms->sendTransactional($phone,$sample);
                
                
                $admin_message = $userData->name.", Has booked". $info ." for ".$itinerary." is confirm. Total Amount ".$request->Amount1." pay by C.O.D. Thank you.";
                
                
                $phone = "918200919647";
                $sample = [ 
                    'message'      => $message,
                    'sender'       => 'ATSONI',
                    'country'      => 91,
                ];

                $sms = new TransactionalSms("274119AWWVwpDgv3Y5cc3205c");
                $sms->sendTransactional($phone,$sample);
                
                
                Session::forget('itinerary');
                Session::forget('info');
                
                return redirect()->route('userDashboard')->with('paymentDone','Payment booked.!');
            }
    }
    public function showBooking()
    {
        $row = DB::table('payu_payments')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.manage_booking',compact('row'));
    }
}
