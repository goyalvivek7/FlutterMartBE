<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\CustomerAddress;
use App\Model\Order;
use App\Model\OrderDetail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class CustomerController extends Controller
{

    public function address_list(Request $request)
    {
        //return response()->json(CustomerAddress::where('user_id', $request->user()->id)->latest()->get(), 200);
      	$address = CustomerAddress::where('user_id', $request->user_id)->where('status', 1)->latest()->get();
        if(!empty($address) && $address != "" && $address != NULL && $address != [] && $address!= '[]'){
          return response()->json(['state' => 'Add Address', 'status' => 'success', 'data' => $address], 200);
        } else {
          return response()->json(['state' => 'Add Address', 'status' => 'fail', 'data' => []], 200);
        }                              
      	return response()->json(CustomerAddress::where('user_id', $request->user_id)->latest()->get(), 200);
    }

    public function add_new_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required'
        ]);

        if ($validator->fails()) {
            //return response()->json(['errors' => Helpers::error_processor($validator)], 403);
          	return response()->json(['state' => 'Add Address', 'status' => 'fail'], 200);
        }

        if($request->is_default && $request->is_default != ""){
            $isDefault = $request->is_default;

            if($request->is_default == 1){
                $defaultAddress = CustomerAddress::where('user_id', $request->user_id)->where('status', 1)->where('is_default', 1)->get();
                
                if(isset($defaultAddress) && !empty($defaultAddress) && !empty($defaultAddress[0])){
                    return response()->json(['state' => 'Default Address Already Exists.', 'status' => 'fail'], 200);
                }

            }
        } else {
            $isDefault = 0;
        }

        $address = [
            //'user_id' => $request->user()->id,
          	'user_id' => $request->user_id,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'is_default' => $isDefault,
            'created_at' => now(),
            'updated_at' => now()
        ];
        DB::table('customer_addresses')->insert($address);
        //return response()->json(['message' => 'successfully added!'], 200);
      	return response()->json(['state' => 'register', 'status' => 'success', 'message' => 'successfully added!'], 200);
    }

    public function update_address(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $address = [
            'user_id' => $request->user_id,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'created_at' => now(),
            'updated_at' => now()
        ];
        DB::table('customer_addresses')->where('id',$id)->update($address);
        //return response()->json(['message' => 'successfully updated!'], 200);
        return response()->json(['state' => 'register', 'status' => 'success', 'message' => 'successfully updated!'], 200);
    }

    public function delete_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['state' => 'Please send all params', 'status' => 'fail'], 200);
        }
        $addressId = $request['address_id'];
        $userId = $request['user_id'];

        DB::table('customer_addresses')->where(['user_id' => $userId, 'id' => $addressId])->update([
            'status' => '0'
        ]);
        return response()->json(['state' => 'Address Deleted', 'status' => 'success'], 200);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        // }

        // if (DB::table('customer_addresses')->where(['id' => $request['address_id'], 'user_id' => $request->user()->id])->first()) {
        //     DB::table('customer_addresses')->where(['id' => $request['address_id'], 'user_id' => $request->user()->id])->delete();
        //     return response()->json(['message' => 'successfully removed!'], 200);
        // }
        // return response()->json(['message' => 'No such data found!'], 404);
    }

    public function get_order_list(Request $request)
    {
        $orders = Order::where(['user_id' => $request->user()->id])->get();
        return response()->json($orders, 200);
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $details = OrderDetail::where(['order_id' => $request['order_id']])->get();
        foreach ($details as $det) {
            $det['product_details'] = Helpers::product_data_formatting(json_decode($det['product_details'], true));
        }

        return response()->json($details, 200);
    }

    public function info(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'phone' => 'required',
        ], [
            'f_name.required' => 'First name is required!',
            'l_name.required' => 'Last name is required!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $image = $request->file('image');

        if ($image != null) {
            $data = getimagesize($image);
            $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . 'png';
            if (!Storage::disk('public')->exists('profile')) {
                Storage::disk('public')->makeDirectory('profile');
            }
            $note_img = Image::make($image)->fit($data[0], $data[1])->stream();
            Storage::disk('public')->put('profile/' . $imageName, $note_img);
        } else {
            $imageName = $request->user()->image;
        }

        if ($request['password'] != null && strlen($request['password']) > 5) {
            $pass = bcrypt($request['password']);
        } else {
            $pass = $request->user()->password;
        }

        $userDetails = [
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'phone' => $request->phone,
            'image' => $imageName,
            'password' => $pass,
            'updated_at' => now()
        ];

        User::where(['id' => $request->user()->id])->update($userDetails);

        return response()->json(['message' => 'successfully updated!'], 200);
    }

    public function update_cm_firebase_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cm_firebase_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        DB::table('users')->where('id',$request->user()->id)->update([
            'cm_firebase_token'=>$request['cm_firebase_token']
        ]);

        return response()->json(['message' => 'successfully updated!'], 200);
    }
}
