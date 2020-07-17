<?php

namespace App\Http\Controllers;
use File;
use App\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try{
            $img = Input::file('image');
            if($img !== null){
                $filename = $request->name."_".rand(0,1000)."_".$img->getClientOriginalName();
                $status=Storage::disk('uploads')->put($filename, file_get_contents($img->getRealPath()));
                if($status == 1){
                    $allData=$request->except('image','_token');
                    $allData['image'] = $filename;

                    Car::firstOrCreate($allData);
                    return redirect()->route('add-car-page')->with('success','Car Added Successfully..');       

                }
            }else{
                return redirect()->route('add-car-page')->with('error','something goes wrong. Please Try Again..');
            }
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
    public function edit(Request $request)
    {   
        $id = $request->id;
        if($request->newImage){
            $oldImageURL = public_path('car_image/'.$request->image);
            /*$updateData = $request->except('image','_token','id');*/

            $img = Input::file('newImage');

            $filename = $request->name."_".rand(0,1000)."_".$img->getClientOriginalName();
            $status=Storage::disk('uploads')->put($filename, file_get_contents($img->getRealPath()));

           File::delete($oldImageURL);

            if($status == 1){
                $allData=$request->except('image','_token','id','newImage');
                $allData['image'] = $filename;
                
                $isUpdate = Car::where('id',$id)->update($allData);
                return ($isUpdate) ? redirect()->route('add-car-page')->with('success','Car Updated Successfully..') : redirect()->route('add-car-page')->with('error','Something goes wrong while updating..') ;
            }

        }else{
            $allData = $request->except('_token','id'); 
            $isUpdate = Car::where('id',$id)->update($allData);
            return ($isUpdate) ? redirect()->route('add-car-page')->with('success','Car Updated Successfully..') : redirect()->route('add-car-page')->with('error','Something goes wrong while updating..') ;
        }
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
        try{
            $image=Car::where('id',$id)->pluck('image')->toArray();
            $ImageURL = public_path('car_image/'.$image[0]);
            File::delete($ImageURL);
            $isRemove = Car::where('id',$id)->delete();
            if($isRemove === 1){
                return redirect()->route('add-car-page')->with('successRemove','Car Remove Successfully..'); 
            }else{
                return redirect()->route('add-car-page')->with('error','Something goes wrong while deleting car...');
            }
        }catch(\Exception $error){
            report($error);
        }
    }
}
