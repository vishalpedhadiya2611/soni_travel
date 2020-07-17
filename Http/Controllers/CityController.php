<?php

namespace App\Http\Controllers;
    
use Illuminate\Http\Request;
use App\City;
use App\Car;
use Session;
use Illuminate\Support\Facades\DB;
class CityController extends Controller
{
    public static function getLocalArea(){
        return DB::table('local_area')->get();
    }
    public static function getCarType(){
         return Car::select('type')->distinct()->get();
    }
    public function getLocalAreaData()
    {
        $data = DB::table('local_area')->where('name','LIKE',$_REQUEST['search']."%")->get()->toArray();
        if($data){
            $output = "";
            foreach ($data as $area) {
                $output.='<ul><li><a href='.$area->name.'>'.$area->name.'</a></li></ul>';
            }
            return Response($output);
        }
    }
    public function toFrom($from,$to,$km)
    {
        $cars = Car::orderBy('created_at','desc')->get();
        $traffis['from'] = $from;
        $traffis['to'] = $to;
        $traffis['km'] = $km;
        return view('carFilter',['cars'=>$cars],['traffis'=>$traffis]);
    }
    public function handleLocalArea()
    {
        session()->put('from',$_REQUEST['from']);
        session()->put('to',$_REQUEST['to']);
        session()->put('carName',$_REQUEST['carName']);
        session()->put('hrs',$_REQUEST['hrs']);
        session()->put('km',$_REQUEST['km']);
        session()->put('price',$_REQUEST['price']);
        /*$data['from'] = $_REQUEST['from'];
        $data['to'] = $_REQUEST['to'];
        $data['carName'] = $_REQUEST['carName'];
        $data['hrs'] = $_REQUEST['hrs'];
        $data['km'] = $_REQUEST['km'];
        $data['price'] = $_REQUEST['price'];
        session()->put('localData',$data);*/
        return 1;
    }
    public function localArea($to)
    {
        return view('localAreaWise', ['to' => $to]);
    }
    public function passengerDetail($from,$to,$km,$carId)
    {
        if(Session::has('User_email')){
            $data = Car::where('id',$carId)->get();
            $fair_charge=Car::where('id',$carId)->get()->pluck('fair_charge');
            $totalKM = rtrim($km," km");
            $totalKM=str_replace(",", "", $totalKM);
            $totalKM = (int)$totalKM;
            
            if ($totalKM < 300) {
                $totalKM = 300;
            }

            $tripdata['total'] = (($totalKM + $totalKM) * $fair_charge[0]) + 250;
            $tripdata['from'] = $from;
            $tripdata['to'] = $to;
            $tripdata['km'] = $km;
            return view('passenger_detail',['data'=>$data],['tripdata'=>$tripdata]);
        }else{
            return view('passenger_detail')->with('invalid','Please do login first');
        }
    }
    public function localPayment()
    {
        if(Session::has('User_email')){  
            $data['from'] = Session::get('from');
            $data['to'] = Session::get('to');
            $data['carName'] = Session::get('carName');
            $data['hrs'] = Session::get('hrs');
            $data['km'] = Session::get('km');
            $data['price'] = Session::get('price');
            return view('localPayment', ['localData' => $data]);    
        }else{
            return view('localPayment')->with('invalid','Please do login first');
        }   
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try{
            City::firstOrCreate($request->only('name'));
            return redirect()->route('add-city-page')->with('success','City Added Successfully..');
        }catch(\Exception $error){
            report($error);
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public static function show()
    {
        return City::get();
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
            $isUpdate = City::where($request->only('id'))->update($request->only('name'));
            if($isUpdate === 1){
                return redirect()->route('add-city-page')->with('successUpdate','City Update Successfully..'); 
            }else{
                return redirect()->route('add-city-page');
            }
        }catch(\Exception $error){
            report($error);
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
        try{
            $isRemove = City::where('id',$id)->delete();
            if($isRemove === 1){
                return redirect()->route('add-city-page')->with('successRemove','City Remove Successfully..'); 
            }else{
                return redirect()->route('add-city-page');
            }
        }catch(\Exception $error){
            report($error);
        }
    }
}
