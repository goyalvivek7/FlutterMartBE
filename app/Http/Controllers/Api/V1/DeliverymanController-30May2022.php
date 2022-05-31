<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\DeliveryHistory;
use App\Model\DeliveryMan;
use App\Model\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeliverymanController extends Controller
{

    public function verify_otp(Request $request){
      
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $verify = DeliveryMan::where(['phone' => $request['phone'], 'otp' => $request['otp']])->first();
			
        if (isset($verify)) {
            $response['status'] = 'success';
            $response['message'] = 'OTP verified';
            $response['data'][] = $verify;
            return response()->json($response, 200);
            //return response()->json(['message' => 'OTP verified!', 'status' => 'success', 'data' => $verify], 200);
        }

        return response()->json(['message' => 'OTP fail!', 'status' => 'fail'], 200);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'phone' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $deliveryManData = DeliveryMan::where(['phone' => $request['phone'], 'status' => 1])->get();
        
        if(!empty($deliveryManData)){

            $i = mt_rand(1, 9999);
            $randno = str_pad($i, 4, '0', STR_PAD_LEFT);
            DeliveryMan::where(['phone' => $request['phone']])->update([
                'otp' => $randno
            ]);

            $dltId = "1307165294037975142";
            $senderId = "INFBUY";
            $pretag = urlencode('<#> ');
            $authKey = "377364AWhOWdwDOTQS628b49baP1";
            //$message = urlencode("Dear User,\n\nPlease use ".$randno." one-time verification code for login.\n\nBest Regards,\n\nTeam VW ABC 2021");
            $message = urlencode("Hi User, Your one time password for phone verification is ".$randno.". Team Infinbuy Private Limited");
            $route = "4";
            $postData = array(
                'authkey' => $authKey,
                'mobiles' => "91".$request['phone'],
                'message' => $message,
                'sender' => $senderId,
                'route' => $route,
                'DLT_TE_ID' => $dltId
            );
            $url="https://api.msg91.com/api/sendhttp.php";
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData
            ));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $output = curl_exec($ch);
            if(curl_errno($ch)){
                echo 'error:' . curl_error($ch);
            }
            curl_close($ch);    

            $otpArray = array();
            $otpArray['otp'] = $randno;

            $response['status'] = 'success';
            $response['message'] = 'Otp sent to your mobile.';
            $response['data'][] = $otpArray;
            return response()->json($response, 200);

        } else {

            $response['status'] = 'fail';
            $response['message'] = 'Deliveryman Not Found With This Phone No.';
            $response['data'] = [];
            return response()->json($response, 200);

        }
    }

    public function get_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (isset($dm) == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }
        return response()->json($dm, 200);
    }

    public function get_current_orders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (isset($dm) == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }
        $orders = Order::with(['delivery_address','customer'])->whereIn('order_status', ['pending', 'confirmed', 'processing', 'out_for_delivery'])
            ->where(['delivery_man_id' => $dm['id']])->get();
        return response()->json($orders, 200);
    }

    public function record_location_data(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (isset($dm) == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }
        DB::table('delivery_histories')->insert([
            'order_id' => $request['order_id'],
            'deliveryman_id' => $dm['id'],
            'longitude' => $request['longitude'],
            'latitude' => $request['latitude'],
            'time' => now(),
            'location' => $request['location'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json(['message' => 'location recorded'], 200);
    }

    public function get_order_history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'order_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (isset($dm) == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }

        $history = DeliveryHistory::where(['order_id' => $request['order_id'], 'deliveryman_id' => $dm['id']])->get();
        return response()->json($history, 200);
    }

    public function update_order_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'order_id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (isset($dm) == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }

        Order::where(['id' => $request['order_id'], 'delivery_man_id' => $dm['id']])->update([
            'order_status' => $request['status']
        ]);

        $order=Order::find($request['order_id']);
        $fcm_token=$order->customer->cm_firebase_token;

        if ($request['status']=='out_for_delivery'){
            $value=Helpers::order_status_update_message('ord_start');
        }elseif ($request['status']=='delivered'){
            $value=Helpers::order_status_update_message('delivery_boy_delivered');
        }

        try {
            if ($value){
                $data=[
                    'title'=>'Order',
                    'description'=>$value,
                    'order_id'=>$order['id'],
                    'image'=>'',
                ];
                Helpers::send_push_notif_to_device($fcm_token,$data);
            }
        } catch (\Exception $e) {

        }

        return response()->json(['message' => 'Status updated'], 200);
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (isset($dm) == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }
        $order = Order::with(['details'])->where(['delivery_man_id' => $dm['id'], 'id' => $request['order_id']])->first();
        $details = $order->details;
        foreach ($details as $det) {
            $det['add_on_ids'] = json_decode($det['add_on_ids']);
            $det['add_on_qtys'] = json_decode($det['add_on_qtys']);
            $det['variation'] = json_decode($det['variation']);
            $det['product_details'] = Helpers::product_data_formatting(json_decode($det['product_details'], true));
        }
        return response()->json($details, 200);
    }

    public function dashboard(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }
        
        $dm = DeliveryMan::where(['id' => $request['user_id']])->first();
        if (isset($dm) == false) {
            $response['status'] = 'fail';
            $response['message'] = 'Delivery Man Not Found.';
            $response['data'] = [];
            return response()->json($response, 200);
        }
        
        $orders = Order::with(['delivery_address','customer'])->where(['delivery_man_id' => $dm['id']])->get();
        
        $orderArray = array();
        if(isset($orders) && isset($orders[0])){
            $counter = 0;
            foreach($orders as $order){
                $orderStatus = $order['order_status'];
                if(isset($orderArray[$orderStatus]) && $orderArray[$orderStatus] != ""){
                    $orderArray[$orderStatus] = $orderArray[$orderStatus] + 1;
                } else {
                    $orderArray[$orderStatus] = 1;
                }
                
                $counter++;
                $orderArray['total_order'] = $counter;
            }

            $response['status'] = 'success';
            $response['message'] = 'No Order Found.';
            $response['data'][] = $orderArray;
            return response()->json($response, 200);
            
        } else {
            $response['status'] = 'success';
            $response['message'] = 'No Order Found.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        return response()->json($orders, 200);

    }

    public function get_all_orders(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'token' => 'required'
        // ]);
        // if ($validator->fails()) {
        //     return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        // }
        // $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        // if (isset($dm) == false) {
        //     return response()->json([
        //         'errors' => [
        //             ['code' => 'delivery-man', 'message' => 'Invalid token!']
        //         ]
        //     ], 401);
        // }
        // $orders = Order::with(['delivery_address','customer'])->where(['delivery_man_id' => $dm['id']])->get();
        // return response()->json($orders, 200);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }
        
        $dm = DeliveryMan::where(['id' => $request['user_id']])->first();
        if (isset($dm) == false) {
            $response['status'] = 'fail';
            $response['message'] = 'Delivery Man Not Found.';
            $response['data'] = [];
            return response()->json($response, 200);
        }
        
        $orders = Order::with(['delivery_address','customer'])->where(['delivery_man_id' => $dm['id']])->get();
        
        $orderArray = array();
        if(isset($orders) && isset($orders[0])){
            foreach($orders as $order){
                $orderStatus = $order['order_status'];
                $orderArray[$orderStatus][] = $order;
            }

            $response['status'] = 'success';
            $response['message'] = 'No Order Found.';
            $response['data'][] = $orderArray;
            return response()->json($response, 200);
            
        } else {
            $response['status'] = 'success';
            $response['message'] = 'No Order Found.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        return response()->json($orders, 200);

    }

    public function get_last_location(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $last_data = DeliveryHistory::where(['order_id' => $request['order_id']])->latest()->first();
        return response()->json($last_data, 200);
    }

    public function order_payment_status_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (isset($dm) == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }

        if (Order::where(['delivery_man_id' => $dm['id'], 'id' => $request['order_id']])->first()) {
            Order::where(['delivery_man_id' => $dm['id'], 'id' => $request['order_id']])->update([
                'payment_status' => $request['status']
            ]);
            return response()->json(['message' => 'Payment status updated'], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => 'not found!']
            ]
        ], 404);
    }

    public function update_fcm_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if (isset($dm) == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => 'Invalid token!']
                ]
            ], 401);
        }

        DeliveryMan::where(['id' => $dm['id']])->update([
            'fcm_token' => $request['fcm_token']
        ]);

        return response()->json(['message'=>'successfully updated!'], 200);
    }
}
