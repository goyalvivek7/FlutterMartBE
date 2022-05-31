<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use App\Model\DMReview;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{

    public function wallet_histories(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = [];
            $response['histories'] = [];

            return response()->json($response, 200);
        }

        if($request['user_id'] != ""){
            $wallet = DB::table('wallet')->where('user_id', $request['user_id'])->get();

            if (count($wallet)>0) {
                $response['status'] = 'success';
                $response['message'] = 'User Wallet';
                $response['data'] = $wallet;

                $walletHistories = DB::table('wallet_histories')->where('user_id', $request['user_id'])->get();
                $response['histories'] = $walletHistories;

            } else {
                $response['status'] = 'success';
                $response['message'] = 'User do not have any wallet balance';
                $response['data'] = [];
                $response['histories'] = [];
            }

        } else {

            $response['status'] = 'fail';
            $response['message'] = 'User Not Found.';
            $response['data'] = [];
            $response['histories'] = [];

        }

        return response()->json($response, 200);

    }


    public function update_order(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'order_id' => 'required',
            'trans_id' => 'required',
            'amount' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = [];

            return response()->json($response, 200);
        }

        if($request['user_id'] != "" && $request['order_id'] != ""){
            $userId = $request['user_id']; $orderId = $request['order_id'];
            $transId = $request['trans_id']; $amount = $request['amount'];
            $status = $request['status'];

            $walletOrders = DB::table('wallet_orders')->where('order_id', $orderId)->where('user_id', $userId)->orderBy('id', 'DESC')->get();

            if(count($walletOrders)>0){

                $currentDate = date('Y-m-d h:i:s');
                
                $lastWallet = DB::table('wallet_orders')->whereNotNull('receipt_id')->limit(1)->orderBy('id', 'DESC')->get();
                //echo '<pre />'; print_r($lastWallet); die;
                if(count($lastWallet) > 0){
                    $receiptId = (($lastWallet[0]->receipt_id) + 1);
                } else {
                    $receiptId = 1;
                }

                DB::table('wallet_orders')->where(['user_id' => $userId, 'order_id' => $orderId])->update([
                    'trans_id'  => $transId,
                    'amount'  => $amount,
                    'status'  => $status,
                    'order_date' => $currentDate,
                    'receipt_id' => $receiptId
                ]);



                if($status == "captured"){

                    $walletBalance = DB::table('wallet')->where('user_id', $userId)->get();

                    if(count($walletBalance) > 0){

                        $userBalance = ($walletBalance[0]->balance)+$amount;
                        DB::table('wallet')->where(['user_id' => $userId])->update([
                            'balance'  => $userBalance,
                        ]);

                    } else {

                        $walletArray = [
                            'user_id' => $userId,
                            'balance' => $userBalance
                        ];
                        
                        DB::table('wallet_orders')->insert($walletArray);

                    }

                    $historyArray = [
                        'user_id' => $userId,
                        'amount' => $amount,
                        'status' => 'credit',
                        'recharge_id' => $orderId
                    ];
                    
                    DB::table('wallet_histories')->insert($historyArray);

                    $response['status'] = 'success';
                    $response['message'] = 'Recharge Successfully Updated.';
                    $response['data'] = [];

                } elseif($status == "failed"){

                    $response['status'] = 'fail';
                    $response['message'] = 'Recharge Failed.';
                    $response['data'] = [];

                }

            } else {

                $response['status'] = 'fail';
                $response['message'] = 'Order Not Found.';
                $response['data'] = [];

            }

            return response()->json($response, 200);
        }
    }

    public function create_order(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = [];

            return response()->json($response, 200);
        }
        
        if($request['user_id'] != ""){

            $walletOrders = DB::table('wallet_orders')->limit(1)->orderBy('id', 'DESC')->get();

            if(count($walletOrders)>0){
                $orderId = "wallet_000".$walletOrders[0]->id;
            } else {
                $orderId = "wallet_0001";
            }

            $orderArray = [
                'order_id' => $orderId,
                'user_id' => $request['user_id'],
                'status' => 'pending'
            ];
            
            DB::table('wallet_orders')->insert($orderArray);

            $orderData['order_id'] = $orderId;
            $orderData['user_id'] = $request['user_id'];

            $response['status'] = 'success';
            $response['message'] = 'Order Generated';
            $response['data'][] = $orderData;

            return response()->json($response, 200);
        }
        
    }

    public function user_balance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = [];
        }

        //$wishlist = Wishlist::where('user_id', $request->user_id)->where('product_id', $request->product_id)->first();
        $wallet = DB::table('wallet')->where('user_id', $request['user_id'])->get();

        if (count($wallet)>0) {
            $response['status'] = 'success';
            $response['message'] = 'User Wallet';
            $response['data'] = $wallet;
        } else {
            $response['status'] = 'success';
            $response['message'] = 'User do not have any wallet balance';
            $response['data'] = [];
        }
        return response()->json($response, 200);
    }
}
