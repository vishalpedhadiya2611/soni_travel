<?php

namespace App\Http\Controllers;
use File;
use Illuminate\Http\Request;
use App\Admin;
use Session;
use App\partner;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;

class AdminAuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.login');
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $status=Admin::where('email',$request->email)->where('password',$request->password)->count();
        if($status){
            $request->session()->put('Admin_email',$request->email);
            return redirect()->route('admin-dashboard');
        }else{
            return redirect()->route('admin-login')->with('error','Invalid Email / Password...');

            //return redirect()->route('admin-login',['error'=>'Invalid Email / Password...']);
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
    public function destroy()
    {
        Session::flush();
        return redirect()->route('admin-login');
    }

    public function parnterGallary(Request $request)
    {
        try{
            $img = Input::file('image');
            if($img !== null){
                $filename = rand(0,1000)."_".$img->getClientOriginalName();
                $status=Storage::disk('prtnerImage')->put($filename, file_get_contents($img->getRealPath()));
                if($status == 1){
                    $allData=$request->except('image','_token');
                    $allData['image'] = $filename;

                    partner::firstOrCreate($allData);
                    return redirect()->route('parnterGallary')->with('success','Partner Added Successfully..');       

                }
            }else{
                return redirect()->route('parnterGallary')->with('error','something goes wrong. Please Try Again..');
            }
        }catch(\Exception $error){
            report($error);
        }
    }
    public static function getPartners()
    {
        return partner::get();  
    }
    public function deletePartner($id)
    {
       try{
            $image=partner::where('id',$id)->pluck('image')->toArray();
            $ImageURL = public_path('partner_image/'.$image[0]);
            File::delete($ImageURL);
            $isRemove = partner::where('id',$id)->delete();
            if($isRemove === 1){
                return redirect()->route('parnterGallary')->with('successRemove','Partner Remove Successfully..'); 
            }else{
                return redirect()->route('parnterGallary')->with('error','Something goes wrong while deleting partner...');
            }
        }catch(\Exception $error){
            report($error);
        } 
    }
}
