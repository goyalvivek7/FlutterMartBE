<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Cart;
use App\Model\CartFinal;
use App\Model\Coupon;
use App\Model\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{

    public function final_cart(Request $request){
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

        $userId = $request['user_id'];
        $couponDiscount = 0;

        $cartOrder = DB::table('cart')->where('user_id', $userId)->get();

        if(!empty($cartOrder) && isset($cartOrder[0])){

            $basicCart['total_items'] = count($cartOrder);
            $totalPrice = 0.00; $basicPrice = 0.00; $taxAmount = 0.00; $catName = ""; $remainingBalance = 0.00;
            $cartArray = array(); $productArray = array();
            foreach($cartOrder as $cart){
                $productId = $cart->product_id;
                $cartArray[] = $cart->id;
                $productArray[] = $productId;

                $productData = DB::table('products')->where('id', $productId)->get();
                $deliveryManagement = DB::table('business_settings')->where('key', 'delivery_management')->get();
                $deliveryCharge = DB::table('business_settings')->where('key', 'delivery_charge')->get();

                $totalPrice += ($cart->quantity * $cart->total_price);
                $basicPrice += ($cart->quantity * $cart->product_price);

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
                    'total_discount'  => $fDiscount,
                    'coupon_code' => $couponCode,
                    'tax_amount'  => $taxAmount,
                    'delivery_charge'  => $delCharge,
                    'coupon_discount'  => $couponDiscount,
                    'sub_total'  => $sTotal,
                    'wallet_balance'  => $walletBalance,
                    'wallet_remaining'  => $walletRemaining,
                    'remaining_sub_total' => round($remainingBalance, 2),
                    'final_amount' => round($remainingBalance)
                ]);

            } else {

                $cartFinal = new CartFinal();
                $cartFinal->user_id = $userId;
                $cartFinal->cart_list  = json_encode($cartArray);
                $cartFinal->product_list  = json_encode($productArray);
                $cartFinal->total_amount  = $totalPrice;
                $cartFinal->basic_amount  = $basicPrice;
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
            'variations-type' => 'required',
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
        $variationsType = $request['variations-type'];
        
        if($quantity <= 0){
            DB::table('cart')->where('user_id', $userId)->where('product_id', $productId)->where('status', 'pending')->delete();
        }

        $cartOrder = DB::table('cart')->where('user_id', $userId)->where('product_id', $productId)->where('status', 'pending')->get();

        if(!empty($cartOrder) && isset($cartOrder[0])){
            foreach($cartOrder as $cart){

                $cProductId = $cart->product_id;
                $cartId = $cart->id;
                if($cProductId == $productId){

                    $productData = DB::table('products')->where('id', $productId)->get();

                    if(isset($productData) && !empty($productData[0])){

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

                        if($variationsType != ""){
                            if($variations != '[]'){
                                $variationArray = json_decode($variations);
                                foreach($variationArray as $variation){
                                    if($variation->type == $variationsType){
                                        $productPrice = $variation->price;
                                    }
                                }
                            }
                        } else {
                            if($quantity > $totalStock){
                                $response['status'] = 'fail';
                                $response['message'] = 'This product quantity is out of stock.';
                                $response['data'] = [];
                                return response()->json($response, 200);
                            }
                        }

                        if($discount != 0){
                            if($discountType == "amount"){
                                $productPrice = ($productPrice-$discount);
                            }
                            if($discountType == "percent"){
                                $productPrice = (($productPrice*$discount)/100);
                            }
                        }

                        if($productImages != "[]" && $productImages != ""){
                            $imageArray = json_decode($productImages);
                            $productImage = $imageArray[0];
                        }


                        Cart::where(['user_id' => $request['user_id'], 'product_id' => $productId])->update([
                            'product_price'  => $productOrgPrice,
                            'product_image'  => $productImage,
                            'quantity'  => $quantity,
                            'variations'  => $variationsType,
                            'discount'  => $discount,
                            'discount_type'  => $discountType,
                            'total_price'  => $productPrice,
                            'product_code'  => $productCode,
                            'variations'  => $productCode,
                        ]);

                    }

                }
            }

        } else {
            $productData = DB::table('products')->where('id', $productId)->get();

            if(isset($productData) && !empty($productData[0])){

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

                if($variationsType != ""){
                    if($variations != '[]'){
                        $variationArray = json_decode($variations);
                        foreach($variationArray as $variation){
                            if($variation->type == $variationsType){
                                $productPrice = $variation->price;
                            }
                        }
                    }
                } else {
                    if($quantity > $totalStock){
                        $response['status'] = 'fail';
                        $response['message'] = 'This product quantity is out of stock.';
                        $response['data'] = [];
                        return response()->json($response, 200);
                    }
                }

                if($discount != 0){
                    if($discountType == "amount"){
                        $productPrice = ($productPrice-$discount);
                    }
                    if($discountType == "percent"){
                        $productPrice = ($productPrice - (($productPrice*$discount)/100));
                    }
                }

                if($productImages != "[]" && $productImages != ""){
                    $imageArray = json_decode($productImages);
                    $productImage = $imageArray[0];
                }

                $cart = new Cart();
                $cart->user_id = $request['user_id'];
                $cart->product_id = $request['product_id'];
                $cart->product_name = $productName;
                $cart->product_price = $productOrgPrice;
                $cart->product_image = $productImage;
                $cart->quantity = $quantity;
                $cart->variations = $variationsType;
                $cart->discount = $discount;
                $cart->discount_type = $discountType;
                $cart->total_price = $productPrice;
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
            //echo '<pre />'; print_r($cart);
            $totalPrice += ($cart->quantity * $cart->total_price);
        }
        $basicCart['total_amount'] = $totalPrice;


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

                $totalPrice += ($cart->quantity * $cart->total_price);
                $basicPrice += ($cart->quantity * $cart->product_price);

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
            $basicCart['sub_total'] =  round(($totalPrice + $taxAmount + $delCharge), 2);

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


}
