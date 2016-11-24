<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CarModification;

use Validator;
use Cache;

class CarModificationController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.car_modification.car_modification_list', ['modelData' => CarModification::all()]);
    }

    public function edit(Request $request)
    {
        $id = 0;
        if(isset($request->id)){
            $id = $request->id;
        }

        $modelData = new \stdClass();
        if($id > 0){
            try{
                $modelData = CarModification::findOrFail($id);
            } catch (ModelNotFoundException $e){
                session()->flash('message', trans('admin_common.Invalid Car Modification'));
                return redirect(url('admin/carmodification'));
            }
        }

        /**
         * form is submitted check values and save if needed
         */
        if ($request->isMethod('post')) {

            /**
             * validate data
             */
            $rules = [
                'car_modification_name' => 'required|max:255'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }

            /**
             * get data from form
             */
            $data = $request->all();

            /**
             * save or update
             */
            if(!isset($modelData->car_modification_id)){
                CarModification::create($data);
            } else {
                $modelData->update($data);
            }

            /**
             * clear cache, set message, redirect to list
             */
            Cache::flush();
            session()->flash('message', trans('admin_common.Car Modification saved'));
            return redirect(url('admin/carmodification'));
        }

        return view('admin.car_modification.car_modification_edit', ['modelData' => $modelData]);
    }

    public function delete(Request $request)
    {
        //locations to be deleted
        $data = [];

        //check for single delete
        if(isset($request->id)){
            $data[] = $request->id;
        }

        //check for mass delete if no single delete
        if(empty($data)){
            $data = $request->input('car_modification_id');
        }

        //delete
        if(!empty($data)){
            CarModification::destroy($data);
            //clear cache, set message, redirect to list
            Cache::flush();
            session()->flash('message', trans('admin_common.Car Modification deleted'));
            return redirect(url('admin/carmodification'));
        }

        //nothing for deletion set message and redirect
        session()->flash('message', trans('admin_common.Nothing for deletion'));
        return redirect(url('admin/carmodification'));
    }
}