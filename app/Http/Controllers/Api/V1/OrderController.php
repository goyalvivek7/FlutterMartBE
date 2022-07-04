<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use App\Model\DMReview;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderHistory;
use App\Model\Product;
use App\Model\Review;
use App\Model\CartFinal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;
use Redirect;
use Session;

class OrderController extends Controller
{
  
    public function captured(Request $request){

        $validator = Validator::make($request->all(), [
            'orderId' => 'required',
            'sigId' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $orderId = $request['orderId'];
        $sigId = $request['sigId'];

        $paymentId = "";
        if(isset($request['paymentId'])){
            $paymentId = $request['paymentId'];
        }

        $orderData = DB::table('orders')->where('order_id', $orderId)->get();
        if(isset($orderData) && !empty($orderData) && !empty($orderData[0])){

            if($orderData[0]->order_status == "created"){

                $finalCartId = $orderData[0]->cart_id;
                $orderstatus = DB::table('orders')->where('order_id', $orderId)->update([
                    'signature_id' => $sigId,
                    'payment_id' => $paymentId,
                    'order_status' => 'pending',
                    'payment_status' => 'paid'
                ]);


                $fCartStatus = DB::table('cart_final')->where('id', $finalCartId)->update([
                    'cart_status' => 'success'
                ]);

                $finalCart = DB::table('cart_final')->where('id', $finalCartId)->get();
                $cartArray = json_decode($finalCart[0]->cart_list);
                $walletBalance = $finalCart[0]->wallet_balance;
                $userId = $finalCart[0]->user_id;

                for($i=0; $i<count($cartArray); $i++){
                    $cartId = $cartArray[$i];
                    $fCartStatus = DB::table('cart')->where('id', $cartId)->update([
                        'status' => 'sucess'
                    ]);
                }

                if($walletBalance != 0){

                    $historyArray = [
                        'user_id' => $userId,
                        'amount' => $walletBalance,
                        'status' => 'debit',
                        'recharge_id' => $orderId
                    ];
                    DB::table('wallet_histories')->insert($historyArray);

                    $walletData = DB::table('wallet')->where('user_id', $userId)->get();
                    $previousAmount = $walletData[0]->balance;
                    $newAmount = $previousAmount - $orderAmount;
                    $walletUpdate = DB::table('wallet')->where('user_id', $userId)->update([
                        'balance' => $newAmount
                    ]);

                }

                $orderUpdate = DB::table('orders')->where('order_id', $orderId)->get();
                $response['status'] = 'success';
                $response['message'] = 'Order successfully placed.';
                $response['order_type'] = 'cart';
                $response['data'] = $orderUpdate;
                return response()->json($response, 200);

            } else {

                $response['status'] = 'fail';
                $response['message'] = 'Order already placed';
                $response['data'] = [];
                return response()->json($response, 200);

            }
        }


        $orderData = DB::table('wallet_orders')->where('order_id', $orderId)->get();
        if(isset($orderData) && !empty($orderData) && !empty($orderData[0])){
            $orderStatus = $orderData[0]->status;

            if($orderStatus == "created"){
                $orderAmount = $orderData[0]->amount;
                $userId = $orderData[0]->user_id;
                
                $walletStatus = DB::table('wallet_orders')->where('order_id', $orderId)->update([
                    'signature_id' => $sigId,
                    'payment_id' => $paymentId,
                    'status' => 'captured'
                ]);

                $historyArray = [
                    'user_id' => $userId,
                    'amount' => $orderAmount,
                    'status' => 'credit',
                    'recharge_id' => $orderId
                ];
                DB::table('wallet_histories')->insert($historyArray);

                $walletData = DB::table('wallet')->where('user_id', $userId)->get();
                if(isset($walletData) && !empty($walletData) && !empty($walletData[0])){
                    $previousAmount = $walletData[0]->balance;
                    $newAmount = $previousAmount + $orderAmount;

                    $walletUpdate = DB::table('wallet')->where('user_id', $userId)->update([
                        'balance' => $newAmount
                    ]);

                } else {
                    $walletArray = [
                        'user_id' => $userId,
                        'balance' => $orderAmount
                    ];
                    DB::table('wallet')->insert($walletArray);
                }

                $orderUpdate = DB::table('wallet_orders')->where('order_id', $orderId)->get();
                $response['status'] = 'success';
                $response['message'] = 'Order successfully placed.';
                $response['order_type'] = 'wallet';
                $response['data'] = $orderUpdate;
                return response()->json($response, 200);

            } else {
                $response['status'] = 'fail';
                $response['message'] = 'Already recharged';
                $response['data'] = [];
                return response()->json($response, 200);
            }
        }
    }
  
  	public function order_history(Request $request){

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = [];

            return response()->json($response, 200);
        }

        $userId = $request['user_id'];
        $orderId = $request['order_id'];

        $orderHistoryData = OrderHistory::Where(['order_id' => $orderId, 'user_id' => $userId])->get();
        
        if ($orderHistoryData->count() > 0) {

            $response['status'] = 'successs';
            $response['message'] = 'Order History Found';
            $response['data'] = $orderHistoryData;
            return response()->json($response, 200);

        } else {

            $response['status'] = 'fail';
            $response['message'] = 'Order History Not Found';
            $response['data'] = [];
            return response()->json($response, 200);

        }

    }
  
  	
  	public function create_order(Request $request){

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'amount' => 'required',
            'order_type' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = [];

            return response()->json($response, 200);
        }
        
        if($request['user_id'] != ""){

            $userId = $request['user_id'];
            $amount = $request['amount'];
            $orderType = $request['order_type'];

            if($orderType == "wallet"){
                $lastWallet = DB::table('wallet_orders')->whereNotNull('receipt_id')->limit(1)->orderBy('id', 'DESC')->get();
                if(count($lastWallet) > 0){
                    $receiptId = (($lastWallet[0]->receipt_id) + 1);
                } else {
                    $receiptId = 1;
                }
            }

            if($orderType == "cart"){
              
              	$finalCart = CartFinal::where('user_id', $userId)->where('cart_status', 'pending')->get();
              	//echo '<pre />'; print_r($finalCart); die;
                if(!empty($finalCart) && isset($finalCart[0])){
                    $amount = (int) $finalCart[0]->final_amount;
                  	$cartId = $finalCart[0]->id;
                } else {
                    $response['status'] = 'fail';
                    $response['message'] = 'Error in getting order data.';
                    $response['data'] = [];

                    return response()->json($response, 200);
                }
              
                $lastWallet = DB::table('orders')->whereNotNull('receipt_id')->limit(1)->orderBy('id', 'DESC')->get();
                if(count($lastWallet) > 0){
                    $receiptId = (($lastWallet[0]->receipt_id) + 1);
                } else {
                    $receiptId = 1;
                }
            }
          	$amount = ($amount * 100);
          
            $api = new Api(config('razor.razor_key'), config('razor.razor_secret'));
            $orderData = $api->order->create(
                array(
                    'receipt' => $receiptId, 
                    'amount' => $amount, 
                    'currency' => 'INR', 
                    'notes'=> array(
                        'user_id'=> $userId,
                        'order_type'=> $orderType
                    )
                )
            );
            
            if(isset($orderData) && $orderData['status'] == "created"){

                $orderId = $orderData['id'];
                $orderAmount = ($orderData['amount']/100);
                $orderReceipt = $orderData['receipt'];
                $orderNotes = $orderData['notes'];
                $orderUserId = $orderNotes->user_id;
                $orderOrderType = $orderNotes->order_type;

                
                if($orderOrderType == "wallet"){
                    
                    $walletOrders = [
                        'user_id' => $orderUserId,
                        'order_id' => $orderId,
                        'amount' => $orderAmount,
                        'receipt_id' => $orderReceipt,
                        'status' => $orderData['status'],
                        'order_date' => date('Y-m-d h:i:s')
                    ];
                    
                    DB::table('wallet_orders')->insert($walletOrders);

                }

                if($orderOrderType == "cart"){
                    
                    $walletOrders = [
                        'user_id' => $orderUserId,
                        'order_id' => $orderId,
                        'order_amount' => $orderAmount,
                        'receipt_id' => $orderReceipt,
                      	'cart_id' => $cartId,
                        'order_status' => $orderData['status']
                    ];
                    
                    DB::table('orders')->insert($walletOrders);

                }

                $orderAray['order_id'] = $orderId;
                $orderAray['user_id'] = $orderUserId;
                $orderAray['amount'] = $orderAmount;
                $orderAray['receipt_id'] = $orderReceipt;
                $orderAray['status'] = $orderData['status'];
                $orderAray['order_type'] = $orderOrderType;

                $response['status'] = 'success';
                $response['message'] = 'Order Created';
                $response['data'][] = $orderAray;
                return response()->json($response, 200);

            } else {

                $response['status'] = 'fail';
                $response['message'] = 'Getting some error in generating order';
                $response['data'] = [];
                return response()->json($response, 200);

            }
        }
    }
  
  
    public function track_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            //return response()->json(['errors' => Helpers::error_processor($validator)], 403);
            $response['status'] = 'fail';
      	    $response['message'] = 'Please send all input Param';
            $response['data'] = [];
        } else {
            //return response()->json(OrderLogic::track_order($request['order_id']), 200);
            $response['status'] = 'success';
            $response['message'] = 'Order tracked successfully!';

            $trackOrder = OrderLogic::track_order($request['order_id']);

            if(isset($trackOrder)){
                $delivery_address_id = $trackOrder['delivery_address_id'];
                $addressData = DB::table('customer_addresses')->where('id', $delivery_address_id)->get();
                
                $orderData = Order::withCount('details')->where(['id' => $request['order_id']])->get();
                if(isset($orderData) && !empty($orderData) && isset($orderData[0]) && !empty($orderData[0])){
                    $itemCount = $orderData[0]->details_count;
                } else {
                    $itemCount = 0;
                }
                $response['item_count'] = $itemCount;

                $response['address_data'] = $addressData;
                $response['data'][] = $trackOrder;
            } else {

                $response['status'] = 'fail';
                $response['message'] = 'No Order Found';
                $response['data'] = [];

            }

          	
          	
        }
      	
      	return response()->json($response, 200);
    }

    public function place_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_amount'        => 'required',
            'delivery_address_id' => 'required',
            'order_type'          => 'required|in:self_pickup,delivery',
            'branch_id'           => 'required',
            'distance'            => 'required',
        ]);

        foreach ($request['cart'] as $c) {
            $product = Product::find($c['product_id']);
            $type = $c['variation'][0]['type'];
            foreach (json_decode($product['variations'], true) as $var) {
              if ($type == $var['type'] && $var['stock'] < $c['quantity']) {
                $validator->getMessageBag()->add('stock', 'Stock is insufficient! available stock ' . $var['stock']);
              }
            }
        }

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        try {
            $or = [
                'id'                     => 100000 + Order::all()->count() + 1,
                //'user_id'                => $request->user()->id,
              	'user_id'                => $request['user_id'],
                'order_amount'           => $request['order_amount'],
                'coupon_discount_amount' => $request['coupon_discount_amount'],
                'coupon_discount_title'  => $request['coupon_discount_title'] == 0 ? null : 'coupon_discount_title',
                'payment_status'         => 'unpaid',
                'order_status'           => 'pending',
                'coupon_code'            => $request['coupon_code'],
                'payment_method'         => $request['payment_method'],
                'transaction_reference'  => null,
                'order_note'             => $request['order_note'],
                'order_type'             => $request['order_type'],
                'branch_id'              => $request['branch_id'],
                'delivery_address_id'    => $request['delivery_address_id'],
                'time_slot_id'           => $request['time_slot_id'],
                'delivery_date'          => $request['delivery_date'],

                'date'                   => date('Y-m-d'),
                'delivery_charge'        => Helpers::get_delivery_charge($request['distance']),
                'created_at'             => now(),
                'updated_at'             => now(),
            ];

            $o_id = DB::table('orders')->insertGetId($or);
            $o_time = $or['time_slot_id'];
            $o_delivery = $or['delivery_date'];
            foreach ($request['cart'] as $c) {
                $product = Product::find($c['product_id']);
              	
              	if (count(json_decode($product['variations'], true)) > 0) {
                    $price = Helpers::variation_price($product, json_encode($c['variation']));
                } else {
                    $price = $product['price'];
                }
              
              	$or_d = [
                    'order_id'            => $o_id,
                    'product_id'          => $c['product_id'],
                    'time_slot_id'        => $o_time,
                    'delivery_date'       => $o_delivery,
                    'product_details'     => $product,
                    'quantity'            => $c['quantity'],
                    'price'               => $price,
                    'unit'                => $product['unit'],
                    'tax_amount'          => Helpers::tax_calculate($product, $price),
                    'discount_on_product' => Helpers::discount_calculate($product, $price),
                    'discount_type'       => 'discount_on_product',
                    'variant'             => json_encode($c['variant']),
                    'variation'           => json_encode($c['variation']),
                    'is_stock_decreased'  => 1,

                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
              	
				$type = $c['variation'][0]['type'];
                $var_store = [];
              	
              	foreach (json_decode($product['variations'], true) as $var) {
                    if ($type == $var['type']) {
                        $var['stock'] -= $c['quantity'];
                    }
                    array_push($var_store, $var);
                }
              	
              	Product::where(['id' => $product['id']])->update([
                    'variations'  => json_encode($var_store),
                    'total_stock' => $product['total_stock'] - $c['quantity'],
                ]);
				
              	DB::table('order_details')->insert($or_d);
            }

            //$fcm_token = $request->user()->cm_firebase_token;
            $value = Helpers::order_status_update_message('pending');
          	
          	try {
                if ($value) {
                    $data = [
                        'title'       => 'Order',
                        'description' => $value,
                        'order_id'    => $o_id,
                        'image'       => '',
                    ];
                    //Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            } catch (\Exception $e) {
            }
          	$rData['order_id'] = $o_id;
          	$data2[] = $rData;
            return response()->json([
                'status' => 'success',
                'message'  => 'Order placed successfully!',
                'data' => $data2,
            ], 200);

            /*Mail::to($email)->send(new \App\Mail\OrderPlaced($o_id));*/
        } catch (\Exception $e) {          
            $response['status'] = 'fail';
            $response['error'] = $e;
            return response()->json($response, 403);
        }
    }

    public function get_order_list(Request $request)
    {
        //$orders = Order::with(['customer', 'delivery_man.rating'])->withCount('details')->where(['user_id' => $request->user()->id])->get();
      	$orders = Order::with(['customer', 'delivery_man.rating'])->withCount('details')->where(['user_id' => $request['user_id']])->get();

        $orders->map(function ($data) {
            $data['deliveryman_review_count'] = DMReview::where(['delivery_man_id' => $data['delivery_man_id'], 'order_id' => $data['id']])->count();
            return $data;
        });

        $response['status'] = 'success';
      	$response['message'] = 'Order fetched successfully!';
      	$response['data'] = $orders->map(function ($data) {
            $data->details_count = (integer)$data->details_count;
            return $data;
        });
      	
      	return response()->json($response, 200);
      	
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

      	$orderdata = Order::where(['id' => $request['order_id']])->get();
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
          
          	$response['status'] = 'success';
            $response['message'] = 'Order detail fetched successfully!';
          	$response['order_data'] = $orderdata;
          	$response['address_data'] = $addressData;
            $response['data'] = $details;
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

    public function cancel_order(Request $request){

        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            //return response()->json(['errors' => Helpers::error_processor($validator)], 403);
            $response['status'] = 'fail';
            $response['message'] = 'Please send all input';
            $response['data'] = [];
          	return response()->json($response, 200);
        }

        $userId = $request['user_id'];
        $orderId = $request['order_id'];

        if (Order::where(['user_id' => $userId, 'id' => $orderId])->first()) {

            $order = Order::with(['details'])->where(['user_id' => $userId, 'id' => $orderId])->first();
            $orderStatus = $order['order_status'];

            $resOrderStatus[] = 'out_for_delivery'; $resOrderStatus[] = 'delivered';
            $resOrderStatus[] = 'returned'; $resOrderStatus[] = 'failed'; $resOrderStatus[] = 'canceled';
            
            if(in_array($orderStatus, $resOrderStatus)){

                $response['status'] = 'fail';
                $response['message'] = 'Order not eligible for cancel request, Order out for delivery or deliverd.';
                $response['data'] = [];
                return response()->json($response, 200);
            } else {

                foreach ($order->details as $detail) {
                    if ($detail['is_stock_decreased'] == 1) {
                        $product = Product::find($detail['product_id']);
                        $type = json_decode($detail['variation'])[0]->type;
                        $var_store = [];
                        foreach (json_decode($product['variations'], true) as $var) {
                            if ($type == $var['type']) {
                                $var['stock'] += $detail['quantity'];
                            }
                            array_push($var_store, $var);
                        }
                        Product::where(['id' => $product['id']])->update([
                            'variations'  => json_encode($var_store),
                            'total_stock' => $product['total_stock'] + $detail['quantity'],
                        ]);
                        OrderDetail::where(['id' => $detail['id']])->update([
                            'is_stock_decreased' => 0,
                        ]);
                    }
                }
                Order::where(['user_id' => $userId, 'id' => $orderId])->update([
                    'order_status' => 'canceled',
                ]);

                $orderHistoryData = OrderHistory::create([
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'user_type' => 'user',
                    'status_captured' => 'canceled',
                    'status_reason' => "user cancel request"
                ]);

                $response['status'] = 'success';
                $response['message'] = 'Order canceled';
                $response['data'] = [];
                return response()->json($response, 200);

                //return response()->json(['message' => 'Order canceled'], 200);

            }
        }
        $response['status'] = 'fail';
        $response['message'] = 'Order Not Found.';
        $response['data'] = [];
        return response()->json($response, 200);
    }

    public function update_payment_method(Request $request)
    {
        if (Order::where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->first()) {
            Order::where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->update([
                'payment_method' => $request['payment_method'],
            ]);
            return response()->json(['message' => 'Payment method is updated.'], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => 'not found!'],
            ],
        ], 401);
    }
}
