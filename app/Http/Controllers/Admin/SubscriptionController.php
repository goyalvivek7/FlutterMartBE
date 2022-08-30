<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderHistory;
use App\Model\BusinessSetting;
use App\Model\Product;
use App\Model\SubscriptionOrders;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;

class SubscriptionController extends Controller{

    public function add_delivery_man($order_id, $sub_date, $delivery_man_id)
    {
        $orderId = $order_id;
        $subsDate = date('Y-m-d', $sub_date);
        $deliveryManId = $delivery_man_id;
        $todayDate = date("Y-m-d");
        if ($deliveryManId == 0) {
            Toastr::warning('Please Assign Deliveryman');
        }
        if($todayDate == $subsDate){
            $order = SubscriptionOrders::find($orderId);
            $orderHistory = $order['order_history'];
            if($orderHistory != ""){
                $historyArray = json_decode($orderHistory);
                foreach($historyArray as $history){
                    if($history->date == $subsDate){
                        $history->delivery_man = $deliveryManId;
                    }
                }
                $subsOrderUpdate = DB::table('subscription_orders')->where('id', $orderId)->update([
                    'order_history' => json_encode($historyArray)
                ]);

                Toastr::success('Deliverman assigned to subscription.');
            } else {
                Toastr::warning('No order Details Found.');
            }
        } else {
            Toastr::warning('Delivery man can assign only for order date');
        }
        return response()->json(['status' => true], 200);
    }

    public function payment_status(Request $request){
        $orderStatus = $request->order_status;
        $subsDate = $request->subs_date;
        $todayDate = date("Y-m-d");
        if($todayDate == $subsDate){
            $order = SubscriptionOrders::find($request->id);
            $userId = $order['user_id'];
            $wallet = DB::table('wallet')->where('user_id', $userId)->first();
            $userBalance = $wallet->balance;
            
            if($userBalance>0){
                $orderHistory = $order['order_history'];
                if($orderHistory != ""){
                    $historyArray = json_decode($orderHistory);
                    foreach($historyArray as $history){
                        if($history->date == $subsDate){
                            if($history->payment_status == "pending"){
                                $subsPrice = ($history->price * $history->quantity);
                                if($subsPrice<$userBalance){
                                    
                                    $walletHistoryArray = [
                                        'user_id' => $userId,
                                        'amount' => $subsPrice,
                                        'status' => 'debit',
                                        'order_id' => $order['order_id'],
                                        'subscription_date' => $subsDate
                                    ];
                                    DB::table('wallet_histories')->insert($walletHistoryArray);
                                    
                                    $newAmount = $userBalance - $subsPrice;
                                    $walletUpdate = DB::table('wallet')->where('user_id', $userId)->update([
                                        'balance' => $newAmount
                                    ]);

                                    $history->payment_status = "completed";

                                } else {
                                    Toastr::warning("User don't have sufficient balance to deduct money.");
                                    return back();
                                }
                            } else {
                                Toastr::warning("Payment already deducted");
                                return back();
                            }
                        }
                        //echo '<pre />'; print_r($history);
                    }

                    $subsOrderUpdate = DB::table('subscription_orders')->where('id', $request->id)->update([
                        'order_history' => json_encode($historyArray)
                    ]);

                    Toastr::success('Subscription amount deducted from wallet.');
                    return back();
                }
            } else {
                Toastr::warning("Customer Don't have sufficient balance.");
                return back();
            }
            //echo $userBalance.'<pre />'; print_r($wallet);
        } else {
            Toastr::warning('Balance can deduct only for order date');
            return back();
        }
        
        if ($request->order_status == 'out_for_delivery' && $order['delivery_man_id'] == null && $order['order_type'] != 'self_pickup') {
            
        }

        $order->order_status = $request->order_status;
        $order->save();

        Toastr::success('Order status updated!');
        return back();
    }

    public function status(Request $request){
        $order = SubscriptionOrders::find($request->id);
        if ($request->order_status == 'out_for_delivery' && $order['delivery_man_id'] == null && $order['order_type'] != 'self_pickup') {
            Toastr::warning('Please assign delivery man first!');
            return back();
        }

        $order->order_status = $request->order_status;
        $order->save();

        Toastr::success('Order status updated!');
        return back();
    }

    public function details($id)
    {
        
        $order = SubscriptionOrders::with(['customer', 'delivery_address'])->where(['id' => $id])->first();
        if (isset($order)) {
            $orderId = $order['order_id'];
            $productId = $order['product_id'];
            $userId = $order['user_id'];

            $product = Product::where(['id' => $productId])->first();
            $walletHistories = DB::table('wallet_histories')->where('order_id', $orderId)->whereNotNull('subscription_date')->limit(1)->orderBy('id', 'ASC')->get();
            $wallet = DB::table('wallet')->where('user_id', $userId)->first();
            return view('admin-views.subscription.order-view', compact('order', 'product', 'walletHistories', 'wallet'));
        } else {
            Toastr::info('No more orders!');
            return back();
        }
    }
    
    public function list(Request $request, $status)
    {
        //dd($request['timeSlot']);
        $query_param = [];
        $search = $request['search'];
        $date = $request['date'];
        
        
        if ($status != 'all') {
            $query = SubscriptionOrders::with(['customer'])->where(['order_status' => $status]);
        } else {
            $query = SubscriptionOrders::with(['customer']);
        }

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }

        $orders = $query->latest()->paginate(Helpers::getPagination())->appends($query_param);
        //echo '<pre />'; print_r($orders); die;
        
        return view('admin-views.subscription.list', compact('orders', 'status', 'search', 'date',));
    }

}
