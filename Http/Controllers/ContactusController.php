<?php

namespace App\Http\Controllers;

use App\contactus;
use Illuminate\Http\Request;

class ContactusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allContacts = contactus::paginate(10);
        return view('admin.manage_contact',compact('allContacts'));
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
        try{
            $status = contactus::create($request->except(['_token','submit']));
            return redirect()->route('contactUs')->with("success","Thank You. We'll contact back you soon..");
        }catch(\Exception $e){
            return redirect()->route('contactUs')->with("error","Something goes wrong please try again");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\contactus  $contactus
     * @return \Illuminate\Http\Response
     */
    public function show(contactus $contactus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\contactus  $contactus
     * @return \Illuminate\Http\Response
     */
    public function edit(contactus $contactus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\contactus  $contactus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, contactus $contactus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\contactus  $contactus
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $isRemove = contactus::where('id',$id)->delete();
            if($isRemove === 1){
                return redirect()->route('allContacts')->with('successRemove','Contact has Remove Successfully..'); 
            }else{
                return redirect()->route('allContacts')->with('error','Something goes wrong.please try again.!!!');
            }
        }catch(\Exception $error){
            report($error);
        }
    }
}
