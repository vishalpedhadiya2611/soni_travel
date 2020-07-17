<?php

namespace App\Http\Controllers;
use App\Http\Controllers\SendSMS;
use App\User;
use Session;
Use Redirect;
use Sender\TransactionalSms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | getUsers
    |--------------------------------------------------------------------------
    |
    | Author    : Nirav Panchani <niravpanchani13@gmail.com>
    | Purpose   : login for user
    | In Params : email, password, remember me(optional)
    | Date      : 10 April 2019
    |
     */
    public function info(Request $request){
        try{
            $is_exist=User::where('email',$request->email)->where('password',$request->password)->exists();
            if($is_exist == 1){
                $request->session()->put('User_email',$request->email);
                 return response()->json(["message" => "success", "status"=> 200, "user" => $request->all()]);
            }else{
                return response()->json(["message" => "error", "status"=> 404, "user" => $request->all()]);
            }
        }catch(\Exception $error){
             return response()->json(["message" => "error", "status"=> 500, "user" => $request->all()]);
            report($error);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | getUsers
    |--------------------------------------------------------------------------
    |
    | Author    : Nirav Panchani <niravpanchani13@gmail.com>
    | Purpose   : get all list of registred users
    | In Params : 
    | Date      : 05 April 2019
    |
     */
    public function index()
    {
        try{
            //list users in admin side
            if(Session::has('Admin_email')){
                $users = User::paginate(18);
                return view('admin.manage_users',compact('users'));
            }else{
                return redirect()->route('admin-login')->with('error','Login Require...');;
            }
        }catch(\Exception $error){
            report($error);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | postUser
    |--------------------------------------------------------------------------
    |
    | Author    : Nirav Panchani <niravpanchani13@gmail.com>
    | Purpose   : Registration for user
    | In Params : name, email, password
    | Date      : 09 April 2019
    |
     */
    public function create(Request $request)
    {
        try{
            $added=User::firstOrCreate($request->all());
            
            if($added){
                
                $message = "Hello ".$request->name.", You're become member of Soni Travels. Now you can hire car to travel different location by easily.";
                $phone = "91".$request->phone;
                $sample = [ 
                    'message'      => $message,
                    'sender'       => 'ATSONI',
                    'country'      => 91,
                ];

                $sms = new TransactionalSms("274119AWWVwpDgv3Y5cc3205c");
                $sms->sendTransactional($phone,$sample);

                return response()->json(["message" => "success", "status"=> 200, "user" => $request->all()]);
            }else{
                return response()->json(["message" => "error", "status"=> 404, "user" => $request->all()]);
            }    
        }catch(\Exception $error){
            return response()->json(["message" => "error", "status" => 500]);
        }
        
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }


    /*
    |--------------------------------------------------------------------------
    | postUser
    |--------------------------------------------------------------------------
    |
    | Author    : Nirav Panchani <niravpanchani13@gmail.com>
    | Purpose   : Get Current Login user info
    | In Params : 
    | Date      : 11 April 2019
    |
     */
    public static function show()
    {
        try{
            if(Session::has('User_email')){
                return User::where('email',Session::get('User_email'))->first();
            }
        }catch(\Exception $error){
            return response()->json(["message" => "error", "status"=> 500, "user" => array()]);
        }
    }

    public function forgetpassword(Request $request)
    {
        try{
            $isExist=User::where('email',$request->email)->exists();
            if($isExist == 1){
                $userData = User::where('email',$request->email)->first();
                $encrypted = base64_encode($userData->email);

                $link = url('resetpassword/'.$encrypted);
                $message = "You're password reset link ".$link;

                $phone = "91".$userData->phone;
                $sample = [ 
                    'message'      => $message,
                    'sender'       => 'FPSONI',
                    'country'      => 91,
                ];

                $sms = new TransactionalSms("274119AWWVwpDgv3Y5cc3205c");
                $sms->sendTransactional($phone,$sample);
                return response()->json(["message" => "success", "status"=> 200]);
            }else{
                return response()->json(["message" => "error", "status"=> 201]);
            }
        }catch(\Exception $error){
            return response()->json(["message" => "error", "status"=> 500]);
        }
    }

    public function resetpassword($email)
    {
        $decrypted = base64_decode($email);
        return view('resetpassword',['email'=>$decrypted]);
    }
    public function dochangePwd(Request $request)
    {
        $isUpdate=User::where('email',$request->email)->update(['password'=>$request->password]);
        if($isUpdate){
            return Redirect::back()->with('success', 'Password has been update Successfully.!');
        }else{
            return Redirect::back()->with('error', 'Please try again.!');
        }
    }

    public function changePassword(Request $request)
    {
        try{
            $exist = User::where('email',Session::get('User_email'))->where('password',$request->oldPassword)->exists();
            if($exist == 1){
                $isUpdate=User::where('email',Session::get('User_email'))->update(['password'=>$request->newPassword]);
                if($isUpdate == 1){
                    return response()->json(["message" => "success", "status"=> 200]);
                }else{
                    return response()->json(["message" => "error", "status"=> 201]);
                }
            }else{
                return response()->json(["message" => "error", "status"=> 404, "user" => array()]);
            }

        }catch(\Exception $error){
            return response()->json(["message" => "error", "status"=> 500, "user" => array()]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /*public function myBooking()
    {
        $email=DB::table('payu_payments')->where('email',Session::get('User_email'))->first();
        return view('')
    }*/

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try{
            $userData = $request->except('_token');
            $isUpdate = User::where('id',$request->id)->update($userData);
            
            return ($isUpdate) ? redirect()->route('userDashboard')->with('success','Profile Updated Successfully..') : redirect()->route('userDashboard')->with('error','Something goes wrong while updating..') ;
        }catch(\Exception $e){
            return redirect()->route('userDashboard')->with('error','Something goes wrong while updating..') ;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
