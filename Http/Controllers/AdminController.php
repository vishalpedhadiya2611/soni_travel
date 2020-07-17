<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\Admin;
use App\User;
use App\City;
use App\Car;
use App\contactus;
use App\partner;
class AdminController extends Controller
{

    public function counter()
    {
        $c['user']=User::count();
        $c['city']=City::count();
        $c['car']=Car::count();
        $c['contactus']=contactus::count();
        $c['partner']=partner::count();
        $c['booking']=DB::table('payu_payments')->where('cancel',0)->sum('net_amount_debit');
        return $c;       
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Session::has('Admin_email')){
            $data = Admin::select('first_name','last_name')->where('email',Session::get('Admin_email'))->first()->toArray();
            $admin_name = $data['first_name']." ".$data['last_name'];
            session()->put('Admin_name',$admin_name);
            $count=$this->counter();
            return view('admin.admin_dashboard',$count);
        }else{
            return redirect()->route('admin-login')->with('error','Login Require...');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
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
