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
            'payment_type' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = []; 
            return response()->json($response, 200);
        }

        $userId = $request['user_id'];
        $paymentType = $request['payment_type'];

        $cartOrder = DB::table('cart')->where('user_id', $userId)->where('status', 'pending')->get();
        if(!empty($cartOrder) && isset($cartOrder[0])){

            $cartTotal = 0; $cartIdArray = array(); $productArray = array(); $basicPrice = 0;
            $fDiscount = 0;
            foreach($cartOrder as $cart){
                $cartTotal += $cart->total_price;
                $productId = $cart->product_id;
                $quantity = $cart->quantity;
                $productPrice = $cart->product_price;
                $cartIdArray[] = $cart->id;
                $discount = $cart->discount;
                $discountType = $cart->discount_type;

                $productArray[] = $productId;
                $basicPrice += ($productPrice * $quantity);

                if($discount != 0){
                    if($discountType == "amount"){
                        $fDiscount += ($basicPrice-$discount);
                    }
                    if($discountType == "percent"){
                        $fDiscount += ($basicPrice - (($basicPrice*$discount)/100));
                    }
                }
            }

            $cartArray['cart_total'] = $cartTotal;
            $finalTotal = $cartTotal;

            $paymentSettings = DB::table('payment_settings')->where('status', 1)->get();
            if(!empty($paymentSettings) && isset($paymentSettings[0])){
                foreach($paymentSettings as $setting){
                    $chargeTitle = $setting->setting_title;
                    $chargeType = $setting->charge_type;
                    $chargeValue = $setting->charge_value;
                    $cartValue = 0;
                    if($chargeType == "percentage"){
                        $cartValue = (($cartTotal * $chargeValue) / 100);
                    }
                    if($chargeType == "amount"){
                        $cartValue = $chargeValue;
                    }
                    $cartArray[$chargeTitle] = $cartValue;
                    $finalTotal += $cartValue;
                }
            }


            if($paymentType == "full"){
                $finalAmount = $finalTotal;
                $remainingBalance = 0;
            }

            $paymentOption = 0;
            if($paymentType == "partial"){
                $paymentOption = $request['payment_option'];
                $paymentOptions = DB::table('payment_options')->where('id', $paymentOption)->get();
                if(!empty($paymentOptions) && isset($paymentOptions[0])){
                    foreach($paymentOptions as $pOptions){
                        $paymentPricePercentage = $pOptions->payment_price_percentage;
                        $finalAmount = (($finalTotal * $paymentPricePercentage) / 100);
                        $remainingBalance = ($finalTotal - $finalAmount);
                    }
                }

            }
            
            $finalCart = CartFinal::where('user_id', $userId)->where('cart_status', 'pending')->get();
            if(!empty($finalCart) && isset($finalCart[0])){

                CartFinal::where(['user_id' => $userId, 'cart_status' => 'pending'])->update([
                    'cart_list'  => json_encode($cartIdArray),
                    'product_list'  => json_encode($productArray),
                    'order_charges' => json_encode($cartArray),
                    'total_amount'  => round($cartTotal, 2),
                    'basic_amount'  => round($basicPrice, 2),
                    'total_discount'  => round($fDiscount, 2),
                    'sub_total'  => round($finalTotal, 2),
                    'remaining_sub_total' => round($remainingBalance, 2),
                    'final_amount' => round($finalAmount),
                    'payment_type' => $paymentType,
                    'payment_option' => $paymentOption
                ]);

            } else {
                $cartFinal = new CartFinal();
                $cartFinal->user_id = $userId;

                $cartFinal->cart_list  = json_encode($cartIdArray);
                $cartFinal->product_list  = json_encode($productArray);
                $cartFinal->order_charges  = json_encode($cartArray);
                $cartFinal->total_amount  = round($cartTotal, 2);
                $cartFinal->basic_amount  = round($basicPrice, 2);
                $cartFinal->total_discount = round($fDiscount, 2);
                $cartFinal->sub_total  = round($finalTotal, 2);
                $cartFinal->remaining_sub_total  = round($remainingBalance, 2);
                $cartFinal->final_amount  = round($finalAmount);
                $cartFinal->payment_type  = $paymentType;
                $cartFinal->payment_option  = $paymentOption;
                $cartFinal->save();
            }

            $rData['amount_pay'] = $finalAmount;
            $rData['user_id'] = $userId;
            
            $response['status'] = 'success';
            $response['message'] = 'Order Updated';
            $response['data'][] = $rData;
            return response()->json($response, 200);
        } else {
            $response['status'] = 'fail';
            $response['message'] = 'Cart Not Found';
            $response['data'] = []; 
            return response()->json($response, 200);
        }
    }



    public function check_cart(Request $request){
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

            $cartTotal = 0;
            foreach($cartOrder as $cart){
                $cartTotal += $cart->total_price;
            }

            $cartArray['cart_total'] = $cartTotal;
            $finalTotal = $cartTotal;

            $paymentSettings = DB::table('payment_settings')->where('status', 1)->get();
            if(!empty($paymentSettings) && isset($paymentSettings[0])){
                foreach($paymentSettings as $setting){
                    $chargeTitle = $setting->setting_title;
                    $chargeType = $setting->charge_type;
                    $chargeValue = $setting->charge_value;
                    $cartValue = 0;
                    if($chargeType == "percentage"){
                        $cartValue = (($cartTotal * $chargeValue) / 100);
                    }
                    if($chargeType == "amount"){
                        $cartValue = $chargeValue;
                    }
                    $cartArray[$chargeTitle] = $cartValue;
                    $finalTotal += $cartValue;
                }
            }
            $cartArray['final_total'] = $finalTotal;

            $response['status'] = 'success';
            $response['message'] = 'Cart Details';
            $response['data'][] = $cartArray; 
            return response()->json($response, 200);

        } else {
            $response['status'] = 'fail';
            $response['message'] = 'Cart Not Found';
            $response['data'] = []; 
            return response()->json($response, 200);
        }

    }

    public function payment_options(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = []; 
            return response()->json($response, 200);
        }

        $userId = $request['user_id'];
        $cartOrder = DB::table('cart')->where('user_id', $userId)->where('status', 'pending')->get();
        
        $cartTotal = 0; $finalTotal = 0;
        if(!empty($cartOrder) && isset($cartOrder[0])){
            foreach($cartOrder as $cart){
                $cartTotal += $cart->total_price;
            }
            $finalTotal = $cartTotal;
            $paymentSettings = DB::table('payment_settings')->where('status', 1)->get();
            if(!empty($paymentSettings) && isset($paymentSettings[0])){
                foreach($paymentSettings as $setting){
                    $chargeTitle = $setting->setting_title;
                    $chargeType = $setting->charge_type;
                    $chargeValue = $setting->charge_value;
                    $cartValue = 0;
                    if($chargeType == "percentage"){
                        $cartValue = (($cartTotal * $chargeValue) / 100);
                    }
                    if($chargeType == "amount"){
                        $cartValue = $chargeValue;
                    }
                    $finalTotal += $cartValue;
                }
            }
            $paymentOptions = DB::table('payment_options')->where('status', 1)->get();
            if(!empty($paymentOptions) && isset($paymentOptions[0])){
                foreach($paymentOptions as $options){
                    $advancedRate = $options->payment_price_percentage;

                    $options->total_price = (($finalTotal * $advancedRate) / 100);
                }
                $response['status'] = 'success';
                $response['message'] = 'Payment Options With Details';
                $response['data'] = $paymentOptions; 
                return response()->json($response, 200);
            } else {
                $response['status'] = 'fail';
                $response['message'] = 'Payment Options Not Found';
                $response['data'] = []; 
                return response()->json($response, 200);
            }

        } else {
            $response['status'] = 'fail';
            $response['message'] = 'Cart Not Found';
            $response['data'] = []; 
            return response()->json($response, 200);
        }

    }

    public function delete_cart(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'product_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = []; 
            return response()->json($response, 200);
        }
        $userId = $request['user_id'];
        $productId = $request['product_id'];
        DB::table('cart')->where('user_id', $userId)->where('product_id', $productId)->where('status', 'pending')->delete();

        $response['status'] = 'success';
        $response['message'] = 'Cart Updated';
        $response['data'] = []; 
        return response()->json($response, 200);

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
                            }
                        }

                        if($discount != 0){
                            if($discountType == "amount"){
                                //$productPrice = ($productPrice-$discount);
                                $totalCartPrice = ($totalCartPrice-$discount);
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


                        // Cart::where(['user_id' => $request['user_id'], 'product_id' => $productId])->update([
                        //     'product_price'  => $productOrgPrice,
                        //     'product_image'  => $productImage,
                        //     'quantity'  => $quantity,
                        //     'variations'  => $variationsType,
                        //     'discount'  => $discount,
                        //     'discount_type'  => $discountType,
                        //     'total_price'  => $productPrice,
                        //     'product_code'  => $productCode,
                        //     'variations'  => $productCode,
                        // ]);
                        //echo $variationsType; die;
                        Cart::where(['user_id' => $request['user_id'], 'product_id' => $productId])->update([
                            'product_price'  => $productPrice,
                            'product_image'  => $productImage,
                            'quantity'  => $quantity,
                            'variations'  => $variationsType,
                            'discount'  => $discount,
                            'discount_type'  => $discountType,
                            'total_price'  => $totalCartPrice,
                            'product_code'  => $productCode
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
                    }
                }

                if($discount != 0){
                    if($discountType == "amount"){
                        //$productPrice = ($productPrice-$discount);
                        $totalCartPrice = ($totalCartPrice-$discount);
                    }
                    if($discountType == "percent"){
                        //$productPrice = ($productPrice - (($productPrice*$discount)/100));
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
                $cart->product_name = $productName;
                //$cart->product_price = $productOrgPrice;
                $cart->product_price = $productPrice;
                $cart->product_image = $productImage;
                $cart->quantity = $quantity;
                $cart->variations = $variationsType;
                $cart->discount = $discount;
                $cart->discount_type = $discountType;
                //$cart->total_price = $productPrice;
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

                //$totalPrice += ($cart->quantity * $cart->total_price);
                //$basicPrice += ($cart->quantity * $cart->product_price);

                $totalPrice += $cart->total_price;
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
