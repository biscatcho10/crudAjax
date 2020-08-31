<?php

namespace App\Http\Controllers;

use App\Ajax_crud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AjaxCrudController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            return datatables()->of(Ajax_crud::latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<button type="button" name="edit" id="' . $data->id . '" class="edit btn btn-primary btn-sm" >Edit</button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="delete" id="' . $data->id . '" class="delete btn btn-danger btn-sm" >Delete</button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('ajax-index');
    }

    public function store(Request $request)
    {
        $rules = [
            'frist_name' => 'required',
            'last_name' => 'required',
            'image' => 'required|image',
        ];

        $error = Validator::make($request->all(), $rules);

        if ($error->fails()) {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $image = $request->file('image');
        $new_name = rand() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $new_name);

        Ajax_crud::create([
            'frist_name' => $request->frist_name,
            'last_name' => $request->last_name,
            'image' => $new_name
        ]);

        return response()->json(['success' => 'Data Added Successfully.']);
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $data = Ajax_crud::findOrFail($id);
            return response()->json(['data' => $data]);
        }
    }

    public function update(Request $request)
    {
        $image_name = $request->hidden_image;
        $image = $request->file('image');
        if ($image != '') {
            $rules = array(
                'frist_name'    =>  'required',
                'last_name'     =>  'required',
                'image'         =>  'image|max:2048'
            );
            $error = Validator::make($request->all(), $rules);
            if ($error->fails()) {
                return response()->json(['errors' => $error->errors()->all()]);
            }

            $image_name = rand() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $image_name);
        } else {
            $rules = array(
                'frist_name'    =>  'required',
                'last_name'     =>  'required'
            );

            $error = Validator::make($request->all(), $rules);

            if ($error->fails()) {
                return response()->json(['errors' => $error->errors()->all()]);
            }
        }

        $form_data = array(
            'frist_name'       =>   $request->frist_name,
            'last_name'        =>   $request->last_name,
            'image'            =>   $image_name
        );
        Ajax_crud::whereId($request->hidden_id)->update($form_data);

        return response()->json(['success' => 'Data is successfully updated']);
    }

    public function destroy($id){
        $data = Ajax_crud::findOrFail($id);
        $data->delete();
    }
}
