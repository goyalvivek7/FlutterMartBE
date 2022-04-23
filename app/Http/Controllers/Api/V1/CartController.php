<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
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
            DB::table('cart')->where('user_id', $userId)->where('product_id', $productId)->delete();
        }

        $cartOrder = DB::table('cart')->where('user_id', $userId)->get();

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

        $cartOrder = DB::table('cart')->where('user_id', $userId)->get();

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


    public function list(Request $request)
    {
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

        $cartOrder = DB::table('cart')->where('user_id', $userId)->get();

        if(!empty($cartOrder) && isset($cartOrder[0])){

            $basicCart['total_items'] = count($cartOrder);
            $totalPrice = 0;
            foreach($cartOrder as $cart){
                //echo '<pre />'; print_r($cart);
                $totalPrice += ($cart->quantity * $cart->total_price);
            }
            $basicCart['total_amount'] = $totalPrice;


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
