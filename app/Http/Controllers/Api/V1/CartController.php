<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Cart;
use App\Model\CartFinal;
use App\Model\Coupon;
use App\Model\Order;
use App\User;
use App\Model\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api;

class CartController extends Controller
{
  
    public function id_list(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = []; 
            return response()->json($response, 200);
        }

        $userId = $request['user_id'];
        //echo $userId;
        $cartIdArray = DB::table('cart')->where('user_id', $userId)->where('status', 'pending')->select('product_id')->get();
        //echo '<pre />'; print_r($cartIdArray);
        if(!empty($cartIdArray) && isset($cartIdArray[0])){
            $idArray = array();
            foreach($cartIdArray as $cartArray){
                $idArray[] = $cartArray->product_id;
            }

            $response['status'] = 'success';
            $response['message'] = 'Cart List';
            $response['data'] = $idArray;
            return response()->json($response, 200);
        } else {
            $response['status'] = 'fail';
            $response['message'] = 'Cart Not Found';
            $response['items'] = [];
            return response()->json($response, 200);
        }
        
    }
  
  	public function create_membership_order(Request $request){

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'amount' => 'required',
            'package_id' => 'required',
            'valid_days' => 'required'
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
            $packageId = $request['package_id'];
            $validDays = $request['valid_days'];

            $lastWallet = DB::table('memberships')->whereNotNull('receipt_id')->limit(1)->orderBy('id', 'DESC')->get();
            if(count($lastWallet) > 0){
                $receiptId = (($lastWallet[0]->receipt_id) + 1);
            } else {
                $receiptId = 1;
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
                        'order_type'=> "membership"
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
                $validDays = $request['valid_days'];
                
                
                $walletOrders = [
                    'user_id' => $orderUserId,
                    'order_id' => $orderId,
                    'package_id' => $packageId,
                    'amount' => $orderAmount,
                    'receipt_id' => $orderReceipt,
                    'order_status' => $orderData['status'],
                    'valid_days' => $validDays
                ];
                
                DB::table('memberships')->insert($walletOrders);

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
  
  

    public function final_cart(Request $request){
        if(isset($request['order_type']) && $request['order_type'] != 4){
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'is_wallet' => 'required',
                'delivery_address_id' => 'required',
                'time_slot_id' => 'required',
                'same_day_delievery' => 'required',
                'order_type' => 'required'
            ]);

            if ($validator->fails()) {
                $response['status'] = 'fail';
                $response['message'] = 'Please send all required fields.';
                $response['data'] = []; 
                return response()->json($response, 200);
            }
        } elseif($request['order_type'] == 4){
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'is_wallet' => 'required'
            ]);

            if ($validator->fails()) {
                $response['status'] = 'fail';
                $response['message'] = 'Please send all required fields.';
                $response['data'] = []; 
                return response()->json($response, 200);
            }
        } else {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = []; 
            return response()->json($response, 200);
        }

        $userId = $request['user_id'];
        $couponDiscount = 0;

        $cartOrder = DB::table('cart')->where('user_id', $userId)->where('status', 'pending')->get();

        if(!empty($cartOrder) && isset($cartOrder[0])){

            $basicCart['total_items'] = count($cartOrder);
            $totalPrice = 0.00; $basicPrice = 0.00; $taxAmount = 0.00; $catName = ""; $remainingBalance = 0.00;
            $productBaseDiscount = 0.00; $productOfferDiscount = 0.00;  $productPrice = 0.00;
            $cartArray = array(); $productArray = array();
            foreach($cartOrder as $cart){
                $productId = $cart->product_id;
                $cartArray[] = $cart->id;
                $productArray[] = $productId;

                $productData = DB::table('products')->where('id', $productId)->get();
                $deliveryManagement = DB::table('business_settings')->where('key', 'delivery_management')->get();
                $deliveryCharge = DB::table('business_settings')->where('key', 'delivery_charge')->get();

                //$totalPrice += ($cart->quantity * $cart->total_price);
                $totalPrice += $cart->total_price;
                //$basicPrice += ($cart->quantity * $cart->product_price);
                $basicPrice += ($cart->quantity * $cart->product_org_price);
                $productPrice += ($cart->quantity * $cart->product_price);


                if(isset($productData) && !empty($productData[0])){
                    $tax = $productData[0]->tax;
                    $taxType = $productData[0]->tax_type;
                    $categoryArray = json_decode($productData[0]->category_ids);
                    foreach($categoryArray as $category){
                        $catPosition = $category->position;
                        $catId = $category->id;

                        if($catPosition == 1){
                            $catArray = DB::table('categories')->where('id', $catId)->get();
                            //echo '<pre />'; print_r($catArray[0]->name);
                            if(isset($catArray)){
                                $catName = $catArray[0]->name;
                            }
                        }

                    }
                    //echo '<pre />'; print_r($categoryArray);

                    //echo $productData[0]->name.'----'.$taxType.'---'.$tax.'<br />';
                    if($taxType == "percent"){
                        //$taxAmount += ($cart->quantity * (($tax * $cart->total_price)/100));
                        $taxAmount += ($cart->quantity * (($tax * $cart->product_price)/100));
                        //echo "percent--".$taxAmount.'<br />';
                    }
                    if($taxType == "amount"){
                        $taxAmount += ($cart->quantity * $tax);
                        //echo "amount--".$taxAmount.'<br />';
                    }
                }

                //echo '<pre />'; print_r(json_decode($deliveryManagement->value));
                $deliveryArray = json_decode($deliveryManagement[0]->value);
                $deliveryStatus = $deliveryArray->status;

                if($deliveryStatus == 0){
                    $delCharge = $deliveryCharge[0]->value;
                } else {
                    $delCharge = $deliveryArray->min_shipping_charge;
                }

                $cart->category_name = $catName;

                //echo '<pre />'; print_r($deliveryArray);
                //echo '<pre />'; print_r($deliveryCharge);
            }
            
            if($request['order_type'] != 1){ 
                $delCharge = 0; 
            }

            $userData = User::where('id', $userId)->first();
            if($userData->prime_member==1){
                $memberValidity = $userData->member_validity;
                $todayStr = strtotime(date('Y-m-d h:i:s'));
                if($todayStr<strtotime($memberValidity)){
                    $delCharge = 0;
                }
            }

            if(isset($request['coupon_code']) && $request['coupon_code'] != ""){
                $couponCode = $request['coupon_code'];

                $coupon = Coupon::active()->where(['code' => $couponCode])->first();
                
                if (isset($coupon)) {
                    if ($coupon['limit'] != null) {
                        $total = Order::where(['user_id' => $request['user_id'], 'coupon_code' => $couponCode])->count();
                        if ($total < $coupon['limit']) {
                            
                            $cMinPurchase = $coupon['min_purchase'];
                            $cMaxDiscount = (float) $coupon['max_discount'];
                            $cDiscount = $coupon['discount'];
                            $cDiscountType = $coupon['discount_type'];
                            //echo "In Final Cart"."!!!!";
                            if($cDiscountType == "amount"){
                                //echo $totalPrice."@@@@@";
                                if($totalPrice >= $cMinPurchase){
                                    $totalPrice = $totalPrice - $cDiscount;
                                    if($totalPrice<0){
                                        $totalPrice = 0;
                                    }
                                    $couponDiscount = $cDiscount;
                                }
                            }

                            if($cDiscountType == "percent"){
                              
                                if($totalPrice >= $cMinPurchase){
                                    $couponDiscount = (($cDiscount * $totalPrice)/100);
                                    if($couponDiscount <= $cMaxDiscount){
                                        $totalPrice = $totalPrice - $couponDiscount;
                                    } else {
                                        $totalPrice = $totalPrice - $coupon['max_discount'];
                                      	$couponDiscount = $coupon['max_discount'];
                                    }
                                  	
                                }
                            }
                            
                        }
                    }

                }
            } else {
                $couponCode = "";
            }

            $basicCart['total_amount'] = $totalPrice;
            $basicCart['basic_amount'] = $basicPrice;
            $productBaseDiscount = $basicPrice - $productPrice;
            $basicCart['product_base_discount'] = $productBaseDiscount;
            $productOfferDiscount = $productPrice - $totalPrice;
            $basicCart['product_offer_discount'] = $productOfferDiscount;



            $fDiscount = ($basicPrice - $totalPrice);

            $basicCart['total_discount'] = ($basicPrice - $totalPrice);
            $basicCart['tax_amount'] = $taxAmount;
            $basicCart['delivery_charge'] = $delCharge;
            $basicCart['coupon_discount'] = $couponDiscount;

            $sTotal = round(($totalPrice + $taxAmount + $delCharge), 2);

            $basicCart['sub_total'] =  round(($totalPrice + $taxAmount + $delCharge), 2);
            $basicCart['wallet_balance'] =  0;
          	$basicCart['wallet_remaining'] = 0;

            $remainingBalance = $basicCart['sub_total'];

            if($request['is_wallet'] == 1){
                $wallet = DB::table('wallet')->where('user_id', $request['user_id'])->get();
                if(isset($wallet) && isset($wallet[0]) && $wallet[0]->balance){
                    $walletBalance = $wallet[0]->balance;
                    
                  	if($basicCart['sub_total']<=$walletBalance){
                      	$remainingBalance = 0;
                      	$basicCart['wallet_balance'] = $basicCart['sub_total'];
                      	$basicCart['wallet_remaining'] = $walletBalance - $basicCart['sub_total'];
                    } else {
                      	$remainingBalance = $basicCart['sub_total'] - $walletBalance;
                      	$basicCart['wallet_balance'] = $walletBalance;
                      	$basicCart['wallet_remaining'] = 0;
                    }
                }
            }

            $walletBalance = $basicCart['wallet_balance'];
            $walletRemaining = $basicCart['wallet_remaining'];


            $basicCart['remaining_sub_total'] =  round($remainingBalance, 2);

            $finalCart = CartFinal::where('user_id', $userId)->where('cart_status', 'pending')->get();
            if(!empty($finalCart) && isset($finalCart[0])){

                CartFinal::where(['user_id' => $userId, 'cart_status' => 'pending'])->update([
                    'cart_list'  => json_encode($cartArray),
                    'product_list'  => json_encode($productArray),
                    'total_amount'  => $totalPrice,
                    'basic_amount'  => $basicPrice,
                    'product_base_discount' => $productBaseDiscount,
                    'product_offer_discount' => $productOfferDiscount,
                    'total_discount'  => $fDiscount,
                    'coupon_code' => $couponCode,
                    'tax_amount'  => $taxAmount,
                    'delivery_charge'  => $delCharge,
                    'coupon_discount'  => $couponDiscount,
                    'sub_total'  => $sTotal,
                    'wallet_balance'  => $walletBalance,
                    'wallet_remaining'  => $walletRemaining,
                    'remaining_sub_total' => round($remainingBalance, 2),
                    'final_amount' => round($remainingBalance),
                    'delivery_address_id' => $request['delivery_address_id'],
                    'time_slot_id' => $request['time_slot_id'],
                    'same_day_delievery' => $request['same_day_delievery'],
                    'order_type' => $request['order_type']
                ]);

            } else {

                $cartFinal = new CartFinal();
                $cartFinal->user_id = $userId;
                $cartFinal->cart_list  = json_encode($cartArray);
                $cartFinal->product_list  = json_encode($productArray);
                $cartFinal->total_amount  = $totalPrice;
                $cartFinal->basic_amount  = $basicPrice;
                $cartFinal->product_base_discount = $productBaseDiscount;
                $cartFinal->product_offer_discount = $productOfferDiscount;
                $cartFinal->total_discount  = $fDiscount;
                $cartFinal->coupon_code = $couponCode;
                $cartFinal->tax_amount  = $taxAmount;
                $cartFinal->delivery_charge  = $delCharge;
                $cartFinal->coupon_discount  = $couponDiscount;
                $cartFinal->sub_total  = $sTotal;
                $cartFinal->wallet_balance  = $walletBalance;
                $cartFinal->wallet_remaining  = $walletRemaining;
                $cartFinal->remaining_sub_total = round($remainingBalance, 2);
                $cartFinal->cart_status = 'pending';
                $cartFinal->final_amount = round($remainingBalance);
                $cartFinal->delivery_address_id = $request['delivery_address_id'];
                $cartFinal->time_slot_id = $request['time_slot_id'];
                $cartFinal->same_day_delievery = (string)$request['same_day_delievery'];
                $cartFinal->order_type = $request['order_type'];
                $cartFinal->save();

            }


            //$tax = $cart->total_price
            $response['status'] = 'success';
            $response['message'] = 'Cart List';
            $response['details'][] = $basicCart;
            $response['items'] = $cartOrder;
            return response()->json($response, 200);

        } else {

            $response['status'] = 'fail';
            $response['message'] = 'Cart Not Found';
            $response['items'] = [];
            return response()->json($response, 200);

        }
        
    }


    public function add_to_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = []; 
            return response()->json($response, 200);
        }

        $userId = $request['user_id'];
        $productId = $request['product_id'];
        $quantity = $request['quantity'];
        
      	if(isset($request['variations-type']) && $request['variations-type'] != ""){
            $variationsType = $request['variations-type'];
        } else {
            $variationsType = "";
        }
        
        if($quantity <= 0){
          	//echo $quantity."---".$userId."---".$productId;
          	//echo DB::table('cart')->where('user_id', $userId)->where('product_id', $productId)->where('status', 'pending')->delete();
          	DB::table('cart')->where('user_id', $userId)->where('product_id', $productId)->where('status', 'pending')->delete();
          	$response['status'] = 'success';
            $response['message'] = 'Cart Updated';
            $response['data'] = []; 
            return response()->json($response, 200);
        }
        
        //$cartOrder = DB::table('cart')->where('user_id', $userId)->where('product_id', $productId)->where('status', 'pending')->get();
        $cartOrder = DB::table('cart')->where('user_id', $userId)->where('product_id', $productId)->where('variations', $variationsType)->where('status', 'pending')->get();

        if(!empty($cartOrder) && isset($cartOrder[0])){
            foreach($cartOrder as $cart){

                $cProductId = $cart->product_id;
                $cartId = $cart->id;
                if($cProductId == $productId){

                    $productData = DB::table('products')->where('id', $productId)->get();


                    if(isset($productData) && !empty($productData[0])){

                        if($variationsType != ""){
                            if(isset($productData[0]->variations) && $productData[0]->variations != "" && $productData[0]->variations != '[]'){
                                $variationArray = json_decode($productData[0]->variations);
                                foreach($variationArray as $variation){
                                    if($variation->type == $variationsType){
                                        $productOriginalPrice = $variation->org_price;
                                    }
                                }
                            } else {
                                $productOriginalPrice = $productData[0]->org_price;
                            }
                        } else {
                            $productOriginalPrice = $productData[0]->org_price;
                        }
                        
                        $productPrice = $productData[0]->price;
                        $productOrgPrice = $productData[0]->price;
                        $discount = $productData[0]->discount;
                        $discountType = $productData[0]->discount_type;
                        $totalStock = $productData[0]->total_stock;
                        $variations = $productData[0]->variations;
                        $productName = $productData[0]->name;
                        $productImage = "";
                        $productImages = $productData[0]->image;
                        $productCode = $productData[0]->sku;
                        $totalCartPrice = 0;

                        if($variationsType != ""){
                            if($variations != '[]'){
                                $variationArray = json_decode($variations);
                                foreach($variationArray as $variation){
                                    if($variation->type == $variationsType){
                                        $productPrice = $variation->price;
                                    }
                                }
                                $totalCartPrice = $productPrice * $quantity;
                            }
                        } else {
                            if($quantity > $totalStock){
                                $response['status'] = 'fail';
                                $response['message'] = 'This product quantity is out of stock.';
                                $response['data'] = [];
                                return response()->json($response, 200);
                            } else {
                                $totalCartPrice = $productPrice * $quantity;
                            }
                        }

                        if($discount != 0){
                            if($discountType == "amount"){
                                //$productPrice = ($productPrice-$discount);
                                $totalCartPrice = ($totalCartPrice-($discount * $quantity));
                            }
                            if($discountType == "percent"){
                                //$productPrice = (($productPrice*$discount)/100);
                                $totalCartPrice = ($totalCartPrice - (($totalCartPrice*$discount)/100));
                            }
                        }

                        if($productImages != "[]" && $productImages != ""){
                            $imageArray = json_decode($productImages);
                            $productImage = $imageArray[0];
                        }


                        Cart::where(['user_id' => $request['user_id'], 'product_id' => $productId, 'variations' => $variationsType])->update([
                            'product_org_price' => $productOriginalPrice,
                            'product_price'  => $productPrice,
                            'product_image'  => $productImage,
                            'quantity'  => $quantity,
                            'variations'  => $variationsType,
                            'discount'  => $discount,
                            'discount_type'  => $discountType,
                            'total_price'  => $totalCartPrice,
                            'product_code'  => $productCode,
                        ]);

                    }

                }
            }

        } else {
            $productData = DB::table('products')->where('id', $productId)->get();

            if(isset($productData) && !empty($productData[0])){

                if($variationsType != ""){
                    if(isset($productData[0]->variations) && $productData[0]->variations != "" && $productData[0]->variations != '[]'){
                        $variationArray = json_decode($productData[0]->variations);
                        foreach($variationArray as $variation){
                            if($variation->type == $variationsType){
                                $productOriginalPrice = $variation->org_price;
                            }
                        }
                    } else {
                        $productOriginalPrice = $productData[0]->org_price;
                    }
                } else {
                    $productOriginalPrice = $productData[0]->org_price;
                }
                
                $productPrice = $productData[0]->price;
                $productOrgPrice = $productData[0]->price;
                $discount = $productData[0]->discount;
                $discountType = $productData[0]->discount_type;
                $totalStock = $productData[0]->total_stock;
                $variations = $productData[0]->variations;
                $productName = $productData[0]->name;
                $productImage = "";
                $productImages = $productData[0]->image;
                $productCode = $productData[0]->sku;
                $totalCartPrice = 0;

                if($variationsType != ""){
                    if($variations != '[]'){
                        $variationArray = json_decode($variations);
                        foreach($variationArray as $variation){
                            if($variation->type == $variationsType){
                                $productPrice = $variation->price;
                            }
                        }
                        $totalCartPrice = $productPrice * $quantity;
                    }
                } else {
                    if($quantity > $totalStock){
                        $response['status'] = 'fail';
                        $response['message'] = 'This product quantity is out of stock.';
                        $response['data'] = [];
                        return response()->json($response, 200);
                    } else {
                        $totalCartPrice = $productPrice * $quantity;
                    }
                }
				
              	//echo $totalCartPrice.'---';
                if($discount != 0){
                    if($discountType == "amount"){
                        //$productPrice = ($productPrice-$discount);
                        $totalCartPrice = ($totalCartPrice-($discount * $quantity));
                    }
                    if($discountType == "percent"){
                        //$productPrice = (($productPrice*$discount)/100);
                        $totalCartPrice = ($totalCartPrice - (($totalCartPrice*$discount)/100));
                    }
                }

                if($productImages != "[]" && $productImages != ""){
                    $imageArray = json_decode($productImages);
                    $productImage = $imageArray[0];
                }

                $cart = new Cart();
                $cart->user_id = $request['user_id'];
                $cart->product_id = $request['product_id'];
                $cart->product_org_price = $productOriginalPrice;
                $cart->product_name = $productName;
                $cart->product_price = $productPrice;
                $cart->product_image = $productImage;
                $cart->quantity = $quantity;
                $cart->variations = $variationsType;
                $cart->discount = $discount;
                $cart->discount_type = $discountType;
                $cart->total_price = $totalCartPrice;
                $cart->product_code = $productCode;
                $cart->save();
                

            } else {
                $response['status'] = 'fail';
                $response['message'] = 'This Product Not Found.';
                $response['data'] = [];
                return response()->json($response, 200);
            }
        }

        $cartOrder = DB::table('cart')->where('user_id', $userId)->where('status', 'pending')->get();

        $basicCart['total_items'] = count($cartOrder);
        $totalPrice = 0;
        foreach($cartOrder as $cart){
            //$totalPrice += ($cart->quantity * $cart->total_price);
            $totalPrice += $cart->total_price;
        }
        $basicCart['total_amount'] = $totalPrice;


        if(isset($request['from_wishlist'])  && $request['from_wishlist'] == 1){
            Wishlist::where(['user_id' => $request['user_id'], 'product_id' => $productId])->delete();
        }


        $response['status'] = 'success';
        $response['message'] = 'Cart Updated';
        $response['details'][] = $basicCart;
        $response['items'] = $cartOrder;
        return response()->json($response, 200);
        
    }


    // public function list(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         $response['status'] = 'fail';
    //         $response['message'] = 'Please send all required fields.';
    //         $response['data'] = []; 
    //         return response()->json($response, 200);
    //     }

    //     $userId = $request['user_id'];

    //     $cartOrder = DB::table('cart')->where('user_id', $userId)->get();

    //     if(!empty($cartOrder) && isset($cartOrder[0])){

    //         $basicCart['total_items'] = count($cartOrder);
    //         $totalPrice = 0;
    //         foreach($cartOrder as $cart){
    //             $totalPrice += ($cart->quantity * $cart->total_price);
    //         }
    //         $basicCart['total_amount'] = $totalPrice;


    //         $response['status'] = 'success';
    //         $response['message'] = 'Cart List';
    //         $response['details'][] = $basicCart;
    //         $response['items'] = $cartOrder;
    //         return response()->json($response, 200);

    //     } else {

    //         $response['status'] = 'fail';
    //         $response['message'] = 'Cart Not Found';
    //         $response['items'] = [];
    //         return response()->json($response, 200);

    //     }
        
    // }


    public function list(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = []; 
            return response()->json($response, 200);
        }

        $userId = $request['user_id'];

        $cartOrder = DB::table('cart')->where('user_id', $userId)->where('status', 'pending')->get();

        if(!empty($cartOrder) && isset($cartOrder[0])){

            $basicCart['total_items'] = count($cartOrder);
            $totalPrice = 0; $basicPrice = 0; $taxAmount = 0; $catName = "";
            foreach($cartOrder as $cart){
                $productId = $cart->product_id;

                $productData = DB::table('products')->where('id', $productId)->get();
                $deliveryManagement = DB::table('business_settings')->where('key', 'delivery_management')->get();
                $deliveryCharge = DB::table('business_settings')->where('key', 'delivery_charge')->get();
				
              	$totalPrice += $cart->total_price;
                //$totalPrice += ($cart->quantity * $cart->total_price);
                //$basicPrice += ($cart->quantity * $cart->product_price);
                $basicPrice += ($cart->quantity * $cart->product_org_price);

                if(isset($productData) && !empty($productData[0])){
                    $tax = $productData[0]->tax;
                    $taxType = $productData[0]->tax_type;
                    $categoryArray = json_decode($productData[0]->category_ids);
                    foreach($categoryArray as $category){
                        $catPosition = $category->position;
                        $catId = $category->id;

                        if($catPosition == 1){
                            $catArray = DB::table('categories')->where('id', $catId)->get();
                            //echo '<pre />'; print_r($catArray[0]->name);
                            if(isset($catArray)){
                                $catName = $catArray[0]->name;
                            }
                        }

                    }
                    //echo '<pre />'; print_r($categoryArray);


                    if($taxType == "percent"){
                        $taxAmount += ($cart->quantity * (($tax * $cart->total_price)/100));
                    }
                    if($taxType == "amount"){
                        $taxAmount += ($cart->quantity * $tax);
                    }
                }

                //echo '<pre />'; print_r(json_decode($deliveryManagement->value));
                $deliveryArray = json_decode($deliveryManagement[0]->value);
                $deliveryStatus = $deliveryArray->status;

                if($deliveryStatus == 0){
                    $delCharge = $deliveryCharge[0]->value;
                } else {
                    $delCharge = $deliveryArray->min_shipping_charge;
                }

                $cart->category_name = $catName;

                //echo '<pre />'; print_r($deliveryArray);
                //echo '<pre />'; print_r($deliveryCharge);
            }
            $basicCart['total_amount'] = $totalPrice;
            $basicCart['basic_amount'] = $basicPrice;
            $basicCart['total_discount'] = ($basicPrice - $totalPrice);
            $basicCart['tax_amount'] = $taxAmount;
            $basicCart['delivery_charge'] = $delCharge;
            //$basicCart['sub_total'] =  round(($totalPrice + $taxAmount + $delCharge), 2);
          	$basicCart['sub_total'] =  round(($totalPrice), 2);

            //$tax = $cart->total_price



            $response['status'] = 'success';
            $response['message'] = 'Cart List';
            $response['details'][] = $basicCart;
            $response['items'] = $cartOrder;
            return response()->json($response, 200);

        } else {

            $response['status'] = 'fail';
            $response['message'] = 'Cart Not Found';
            $response['items'] = [];
            return response()->json($response, 200);

        }
        
    }
  
  
  	public function membership_package(){

        $membershipPackages = DB::table('membership_package')->where('status', 1)->get();
        $membershipFeatures = DB::table('membership_features')->where('status', 1)->orderBy('priorty', 'ASC')->get();

        if(!empty($membershipPackages) && isset($membershipPackages[0])){

            $response['status'] = 'success';
            $response['message'] = 'Membership Package List';
            $response['data'] = $membershipPackages;
            $response['features'] = $membershipFeatures;
            return response()->json($response, 200);

        } else {

            $response['status'] = 'fail';
            $response['message'] = 'Membership Package Not Found';
            $response['data'] = [];
            return response()->json($response, 200);

        }
        
    }


}
