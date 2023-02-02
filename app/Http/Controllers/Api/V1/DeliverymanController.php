<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\DeliveryHistory;
use App\Model\DeliveryMan;
use App\Model\OrderHistory;
use App\Model\OrderDetail;
use App\Model\OrderType;
use App\Model\Review;
use App\Model\Product;
use App\Model\Order;
use App\Model\SubscriptionOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeliverymanController extends Controller
{

    public function changeStatusString($status = NULL){
        if($status == "delivered"){
            $retrunVal = "Delivered";
        } else if($status == "paid"){
            $retrunVal = "Paid";
        } else if($status == "cod"){
            $retrunVal = "COD";
        } else if($status == "direct"){
            $retrunVal = "Direct";
        } else if($status == "unpaid"){
            $retrunVal = "Unpaid";
        } else if($status == "pending"){
            $retrunVal = "Pending";
        } else if($status == "confirmed"){
            $retrunVal = "Confirmed";
        } else if($status == "processing"){
            $retrunVal = "Processing";
        } else if($status == "out_for_delivery"){
            $retrunVal = "Out For Delivery";
        } else if($status == "returned"){
            $retrunVal = "Returned";
        } else if($status == "failed"){
            $retrunVal = "Failed";
        } else if($status == "canceled"){
            $retrunVal = "Canceled";
        } else {
            $retrunVal = $status;
        }
        return $retrunVal;
    }

    public function update_subscription_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deliveryman_id' => 'required',
            'order_id' => 'required',
            'status' => 'required',
            'order_date' => 'required'
        ]);
        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }
        $signature = "";
        if (!empty($request->file('signature'))) {
            $signature = Helpers::upload('order/', 'png', $request->file('signature'));
        }
        $dm = DeliveryMan::where(['id' => $request['deliveryman_id']])->first();
        if (isset($dm) == false) {
            $response['status'] = 'fail';
            $response['message'] = 'Delivery Man Not Found';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        if(isset($request['cancel_reason']) && $request['cancel_reason'] != ""){
            $cancelReason = $request['cancel_reason'];
        } else {
            $cancelReason = NULL;
        }

        $checkDelivery = DB::table('delivery_histories')->where(['order_id' => $request['order_id'], 'deliveryman_id' => $request['deliveryman_id'], 'order_date' => $request['order_date']])->first();
        if(isset($checkDelivery) && !empty($checkDelivery)){
            DB::table('delivery_histories')->where(['order_id' => $request['order_id'], 'deliveryman_id' => $request['deliveryman_id'], 'order_date' => $request['order_date']])->update([
                'delivery_status' => $request['status'],
                'cancel_reason' => $cancelReason,
              	'signature' => $signature
            ]);
            
          	$sOrder=SubscriptionOrders::where('order_id', $request['order_id'])->first();
            $orderHistoryArray = json_decode($sOrder->order_history);
            foreach($orderHistoryArray as $historyArray){
            	if($historyArray->date == $request['order_date']){
                  $historyArray->delivery_status = $request['status'];
                }
            }
          
          	SubscriptionOrders::where('order_id', $request['order_id'])->update([
              'order_history' => json_encode($orderHistoryArray)
            ]);
          
          	$fcm_token=$sOrder->customer->cm_firebase_token;

            if($request['status'] == "completed"){
              $value=Helpers::order_status_update_message('delivery_boy_delivered');
            } elseif($request['status'] == "canceled"){
              $value=Helpers::order_status_update_message('returned_message');
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

                    $response['status'] = 'success';
                    $response['state'] = 'update_subscription_status';
                    $response['message'] = 'Status updated';
                    $response['data'] = [];
                    return response()->json($response, 200);

                }
            } catch (\Exception $e) {

            }
          
          	$response['status'] = 'success';
            $response['state'] = 'update_subscription_status';
            $response['message'] = 'Status updated';
            $response['data'] = [];
            return response()->json($response, 200);
          
        } else {
            $response['status'] = 'fail';
            $response['message'] = 'Order For This Delivery Man Not Found';
            $response['data'] = [];
            return response()->json($response, 200);
        }
    }
    

    public function get_all_subscription(Request $request){

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

        $deliveryOrders = DB::table('delivery_histories')->where(['deliveryman_id' => $request['user_id']])->latest()->get();

        if(isset($deliveryOrders) && !empty($deliveryOrders) && !empty($deliveryOrders[0])){

            $deliveryArray = [];
            foreach($deliveryOrders as $order){
                $orderId = $order->order_id;
              	$deliveryStatus = $order->delivery_status;
              
              	$singleOrderArray['date'] = $order->order_date;
              	$singleOrderArray['delivery_status'] = $order->delivery_status;
              	$singleOrderArray['order_id'] = $order->order_id;
              
              	$mainOrders = SubscriptionOrders::with(['customer','delivery_address','product'])->where('order_id', $orderId)->first();
                if(isset($mainOrders) && !empty($mainOrders)){
                  	$singleOrderArray['order_detail'] = $mainOrders;
                }
                

                $deliveryArray[$deliveryStatus][] = $singleOrderArray;
            }
        }
      
      
      	if(isset($deliveryArray) && !empty($deliveryArray)){
          $response['status'] = 'success';
          $response['message'] = 'Subscription List.';
          $response['data'][] = $deliveryArray;
          return response()->json($response, 200);
        } else {
          $response['status'] = 'fail';
          $response['message'] = 'No Order Found.';
          $response['data'] = [];
          return response()->json($response, 200);
        }

    }


    public function available_status(Request $request){
        $validator = Validator::make($request->all(), [
            'delivery_man_id' => 'required',
            'available_status' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $userId = $request['delivery_man_id'];
        $availableStatus = $request['available_status'];

        if($availableStatus == "0"){
            DeliveryMan::where(['id' => $userId])->update([
                'is_available' => "0"
            ]);
            $response['status'] = 'success';
            $response['message'] = 'Status Updated';
            $response['data'] = [];
            return response()->json($response, 200);
        } else {
            $todayDate = date('Y-m-d');
            $orders = Order::where(['delivery_man_id' => $userId, 'delivery_date' => $todayDate])->where('order_type', '!=', 4)->where('order_type', '!=', 2)->first();
            if(isset($orders) && !empty($orders)){
                $response['status'] = 'fail';
                $response['message'] = 'You have pending orders today';
                $response['data'] = [];
                return response()->json($response, 200);
            } else {
                DeliveryMan::where(['id' => $userId])->update([
                    'is_available' => "1"
                ]);
                $response['status'] = 'success';
                $response['message'] = 'Status Updated';
                $response['data'] = [];
                return response()->json($response, 200);
            }
        }

    }


    public function notifications(Request $request){

        $validator = Validator::make($request->all(), [
            'delivery_man_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $userId = $request['delivery_man_id'];

        //$cancalReasons = DB::table('delivery_man_notifications')->where('status', 1)->orWhere('delivery_man_id', $userId)->orWhere('delivery_man_id', NULL)->get();
        $cancalReasons = DB::table('delivery_man_notifications')->orWhere('delivery_man_id', '=', $userId)->orWhere('delivery_man_id', '=', NULL)->get();
        $response['status'] = 'success';
        $response['data'] = $cancalReasons;
        $response['message'] = 'Delivery Notifications.';
        return response()->json($response, 200);
    }

    public function cancel_reasons(Request $request){
        $cancalReasons = DB::table('cancel_resons')->where('status', 1)->get();
        $response['status'] = 'success';
        $response['data'] = $cancalReasons;
        $response['message'] = 'Reasons Found';
        return response()->json($response, 200);
    }

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

        $deliveryManData = DeliveryMan::where(['phone' => $request['phone'], 'status' => 1])->first();
        
        if(!empty($deliveryManData)){

            $i = mt_rand(1, 9999);
            $randno = str_pad($i, 4, '0', STR_PAD_LEFT);
            DeliveryMan::where(['phone' => $request['phone']])->update([
                'otp' => $randno
            ]);

            if(isset($request['firebase_key']) && $request['firebase_key'] != ""){
                DeliveryMan::where(['phone' => $request['phone']])->update([
                    'fcm_token' => $request['firebase_key']
                ]);
            }

            // $dltId = "1307165294037975142";
            // $senderId = "INFBUY";
            // $pretag = urlencode('<#> ');
            // $authKey = "377364AWhOWdwDOTQS628b49baP1";
            // $message = urlencode("Hi User, Your one time password for phone verification is ".$randno.". Team Infinbuy Private Limited");
            // $route = "4";
            // $postData = array(
            //     'authkey' => $authKey,
            //     'mobiles' => "91".$request['phone'],
            //     'message' => $message,
            //     'sender' => $senderId,
            //     'route' => $route,
            //     'DLT_TE_ID' => $dltId
            // );
            // $url="https://api.msg91.com/api/sendhttp.php";
            // $ch = curl_init();
            // curl_setopt_array($ch, array(
            //     CURLOPT_URL => $url,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_POST => true,
            //     CURLOPT_POSTFIELDS => $postData
            // ));
            // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // $output = curl_exec($ch);
            // if(curl_errno($ch)){
            //     echo 'error:' . curl_error($ch);
            // }
            // curl_close($ch);
            $dmPhone = $request['phone'];
            $smsData = file_get_contents('https://api.msg91.com/api/sendhttp.php?authkey=378531A3SYlMChc0qI62b1a431P1&mobiles=91'.$dmPhone.'&message=Dear%20user%2C%20'.$randno.'%20is%20your%20OTP%20for%20registration%20at%20TESMART.%20Happy%20shopping%21&sender=TESMAT&route=4&DLT_TE_ID=1307164908009671091');

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
        // return response()->json($dm, 200);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $deliveryManData = DeliveryMan::where(['id' => $request['user_id']])->get();
        
        if(!empty($deliveryManData)){

            $response['status'] = 'success';
            $response['message'] = 'Deliveryman Data Found.';
            $response['data'][] = $deliveryManData;
            return response()->json($response, 200);

        } else {

            $response['status'] = 'fail';
            $response['message'] = 'Deliveryman Not Found.';
            $response['data'] = [];
            return response()->json($response, 200);

        }

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
            'deliveryman_id' => 'required',
            'order_id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
            //return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if(isset($request['collected_amount']) && $request['collected_amount'] !== ""){
            $collectedAmount = $request['collected_amount'];
        } else {
            $collectedAmount = NULL;
        }

        $signature = "";
        if (!empty($request->file('signature'))) {
            $signature = Helpers::upload('order/', 'png', $request->file('signature'));
        }
        
        
        $dm = DeliveryMan::where(['id' => $request['deliveryman_id']])->first();
        if (isset($dm) == false) {
            $response['status'] = 'fail';
            $response['message'] = 'Delivery Man Not Found';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        Order::where(['id' => $request['order_id'], 'delivery_man_id' => $dm['id']])->update([
            'order_status' => $request['status']
        ]);

        $order=Order::find($request['order_id']);
        $fcm_token=$order->customer->cm_firebase_token;

        if ($request['status']=='out_for_delivery'){
            $value=Helpers::order_status_update_message('ord_start');
            $statusReason = "out_for_delivery";
        }elseif ($request['status']=='delivered'){
            $value=Helpers::order_status_update_message('delivery_boy_delivered');
            $statusReason = "delivered";
        }elseif ($request['status']=='canceled'){
            $value=Helpers::order_status_update_message('canceled');
            $statusReason = "canceled";
        }

        if ($value) {
            $data = [
                'title' => 'Order',
                'description' => $value,
                'order_id' => $request['order_id'],
                'image' => '',
            ];
            Helpers::send_push_notif_to_device($fcm_token, $data);
        }

        $fcm_token = $order->delivery_man->fcm_token;
        if ($value) {
            $data = [
                'title' => 'Order',
                'description' => $value,
                'order_id' => $request['order_id'],
                'image' => '',
            ];
            Helpers::send_push_notif_to_device($fcm_token, $data);
        }

        if(isset($request['status_reason']) && $request['status_reason'] != ""){
            $statusReason = $request['status_reason'];
        }
      
      	
      	if(isset($request['reason_id']) && $request['reason_id'] != "" && $request['reason_id'] != NULL  && $request['reason_id'] != "null"  && $request['reason_id'] != " "){
            $reasonId = $request['reason_id'];
          
            $complaint = OrderHistory::create([
              'order_id' => $request['order_id'],
              'user_id' => $dm['id'],
              'user_type' => 'delivery_man',
              'status_captured' => $request['status'],
              'reason_id' => $reasonId,
              'status_reason' => $statusReason,
              'collected_amount' => $collectedAmount,
              'signature' => $signature
            ]);
        } else {
            
        	$complaint = OrderHistory::create([
                'order_id' => $request['order_id'],
                'user_id' => $dm['id'],
                'user_type' => 'delivery_man',
                'status_captured' => $request['status'],
                'status_reason' => $statusReason,
                'collected_amount' => $collectedAmount,
                'signature' => $signature
            ]);
          
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

                $response['status'] = 'success';
                $response['state'] = 'register';
                $response['message'] = 'Status updated';
                $response['data'] = [];
                return response()->json($response, 200);

            }
        } catch (\Exception $e) {

        }

        

        //return response()->json(['message' => 'Status updated'], 200);
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

      	$orderdata = Order::with(['customer', 'delivery_man.rating', 'time_slot', 'delivery_address', 'final_cart'])->where(['id' => $request['order_id']])->get();
        $orderdata[0]->order_status_str = $this->changeStatusString($orderdata[0]->order_status);
        $orderdata[0]->payment_status_str = $this->changeStatusString($orderdata[0]->payment_status);
        $orderdata[0]->payment_method_str = $this->changeStatusString($orderdata[0]->payment_method);

        $delivery_address_id = $orderdata[0]->delivery_address_id;
        $addressData = DB::table('customer_addresses')->where('id', $delivery_address_id)->get();
      	//echo '<pre />'; print_r($addressData);
      
        $details = OrderDetail::where(['order_id' => $request['order_id']])->get();

        if ($details->count() > 0) {
            foreach ($details as $det) {
                $det['variation'] = json_decode($det['variation']);
                $det['review_count'] = Review::where(['order_id' => $det['order_id'], 'product_id' => $det['product_id']])->count();
                $product = Product::where('id', $det['product_id'])->first();
                $det['product_details'] = isset($product) ? Helpers::product_data_formatting($product) : '';
            }

            $orderHistoryData = OrderHistory::where(['order_id' => $det['order_id'], 'status_captured' => 'delivered'])->first();
          
          	$response['status'] = 'success';
            $response['message'] = 'Order detail fetched successfully!';
          	$response['order_data'] = $orderdata;
          	$response['address_data'] = $addressData;
            $response['data'] = $details;
            $response['item_count'] = $details->count();
            $response['order_history'][] = $orderHistoryData;
          	return response()->json($response, 200);
          
            //return response()->json($details, 200);
        } else {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => 'not found!']
                ], 'status' => 'fail'
            ], 401);
        }
    }

    // public function get_order_details(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'token' => 'required'
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }
    //     $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
    //     if (isset($dm) == false) {
    //         return response()->json([
    //             'errors' => [
    //                 ['code' => 'delivery-man', 'message' => 'Invalid token!']
    //             ]
    //         ], 401);
    //     }
    //     $order = Order::with(['details'])->where(['delivery_man_id' => $dm['id'], 'id' => $request['order_id']])->first();
    //     $details = $order->details;
    //     foreach ($details as $det) {
    //         $det['add_on_ids'] = json_decode($det['add_on_ids']);
    //         $det['add_on_qtys'] = json_decode($det['add_on_qtys']);
    //         $det['variation'] = json_decode($det['variation']);
    //         $det['product_details'] = Helpers::product_data_formatting(json_decode($det['product_details'], true));
    //     }
    //     return response()->json($details, 200);
    // }

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

        $codToday = 0;
        $todayStartDate = date("Y-m-d 00:00:00"); $todayEndDate = date("Y-m-d 23:59:59");
        $todayOrders = OrderHistory::where('user_type', 'delivery_man')->where('status_captured', 'delivered')->whereBetween('created_at', [$todayStartDate, $todayEndDate])->get();
        if(isset($todayOrders) && isset($todayOrders[0]) && !empty($todayOrders[0])){
            //echo '<pre />'; print_r($todayOrders);
            foreach($todayOrders as $codOrder){
                //echo '<pre />'; print_r($codOrder);
              	$codToday += $codOrder->collected_amount;
            }
        }
        
        $orders = Order::with(['delivery_address','customer'])->where(['delivery_man_id' => $dm['id']])->where('order_type', '!=', 4)->where('order_type', '!=', 2)->get();
        
        $orderArray = array();
        $orderArray['today_cod'] = $codToday;
        if(isset($orders) && isset($orders[0])){
            $orderArray['out_for_delivery'] = 0;
            $orderArray['delivered'] = 0;
            $orderArray['pending'] = 0;

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
        
        //$orders = Order::with(['delivery_address','customer', 'time_slot'])->where(['delivery_man_id' => $dm['id']])->latest()->get();
        $orders = Order::with(['delivery_address','customer', 'time_slot'])->where('delivery_man_id', $dm['id'])->where('order_type', '!=', 4)->where('order_type', '!=', 2)->latest()->get();
        
        $orderArray = array();
        //$orderArray['out_for_delivery'] = [];
        $orderArray['delivered'] = [];
        $orderArray['pending'] = [];
        if(isset($orders) && isset($orders[0])){
            foreach($orders as $order){
                $orderStatus = $order['order_status'];
                if($orderStatus == "confirmed" || $orderStatus == "processing" || $orderStatus == "out_for_delivery"){
                    $orderStatus = "pending";
                }
                if($orderStatus == "returned" || $orderStatus == "failed" || $orderStatus == "canceled"){
                    $orderStatus = "delivered";
                }
                $order['order_status_str'] = $this->changeStatusString($orderStatus);
                $order['payment_status_str'] = $this->changeStatusString($order['payment_status']);
              	$order['payment_method_str'] = $this->changeStatusString($order['payment_method']);
                $orderArray[$orderStatus][] = $order;
            }

            $response['status'] = 'success';
            $response['message'] = 'Orders Found.';
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

    // public function get_all_orders(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required'
    //     ]);
    //     if ($validator->fails()) {
    //         $response['status'] = 'fail';
    //         $response['message'] = 'Plese send all inputs.';
    //         $response['data'] = [];
    //         return response()->json($response, 200);
    //     }
    //     $dm = DeliveryMan::where(['id' => $request['user_id']])->first();
    //     if (isset($dm) == false) {
    //         $response['status'] = 'fail';
    //         $response['message'] = 'Delivery Man Not Found.';
    //         $response['data'] = [];
    //         return response()->json($response, 200);
    //     }
    //     $orders = Order::with(['delivery_address','customer', 'time_slot'])->where('delivery_man_id', $dm['id'])->where('order_type', '!=', 4)->where('order_type', '!=', 2)->latest()->get();
    //     $orderArray = array();
    //     $orderArray['delivered'] = [];
    //     $orderArray['pending'] = [];
    //     if(isset($orders) && isset($orders[0])){
    //         foreach($orders as $order){
    //             $orderStatus = $order['order_status'];
    //             if($orderStatus == "confirmed" || $orderStatus == "processing" || $orderStatus == "out_for_delivery"){
    //                 $orderStatus = "pending";
    //             }
    //             if($orderStatus == "returned" || $orderStatus == "failed" || $orderStatus == "canceled"){
    //                 $orderStatus = "delivered";
    //             }
    //             $orderArray[$orderStatus][] = $order;
    //         }
    //         $response['status'] = 'success';
    //         $response['message'] = 'Orders Found.';
    //         $response['data'][] = $orderArray;
    //         return response()->json($response, 200);
    //     } else {
    //         $response['status'] = 'success';
    //         $response['message'] = 'No Order Found.';
    //         $response['data'] = [];
    //         return response()->json($response, 200);
    //     }
    //     return response()->json($orders, 200);
    // }

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
