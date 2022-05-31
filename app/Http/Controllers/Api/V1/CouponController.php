<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Coupon;
use App\Model\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function list()
    {
        try {
            $coupon = Coupon::active()->get();
            // return response()->json($coupon, 200);
            $response['status'] = 'success';
      	    $response['message'] = 'Coupon List';
            $response['data'] = $coupon;
        } catch (\Exception $e) {
            //return response()->json(['errors' => $e], 403);
            $response['status'] = 'fail';
      	    $response['message'] = 'Error In Coupon List';
            $response['data'] = [];
            $response['errors'] = $e;
        }
        return response()->json($response, 200);
    }

    public function apply(Request $request){

        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->errors()->count()>0) {
            //return response()->json(['errors' => Helpers::error_processor($validator)], 403);
            $response['status'] = 'fail';
      	    $response['message'] = 'Please Send All Required Fields';
            $response['data'] = [];
        }

        try {
            $coupon = Coupon::active()->where(['code' => $request['code']])->first();
            if (isset($coupon)) {
                if ($coupon['limit'] == null) {
                    return response()->json($coupon, 200);
                } else {
                    $total = Order::where(['user_id' => $request['user_id'], 'coupon_code' => $request['code']])->count();
                    if ($total < $coupon['limit']) {
                        //return response()->json($coupon, 200);
                        $response['status'] = 'success';
                        $response['message'] = 'Coupon Code Applied';
                        $response['data'] = $coupon;
                    }else{
                        // return response()->json([
                        //     'errors' => [
                        //         ['code' => 'coupon', 'message' => 'Coupon code usage limit is over for you!']
                        //     ]
                        // ], 401);
                        $response['status'] = 'fail';
                        $response['message'] = 'Coupon code usage limit is over for you!';
                        $response['data'] = [];
                    }
                }

            } else {
                // return response()->json([
                //     'errors' => [
                //         ['code' => 'coupon', 'message' => 'not found!']
                //     ]
                // ], 401);
                $response['status'] = 'fail';
                $response['message'] = 'Coupon code not found';
                $response['data'] = [];
            }
        } catch (\Exception $e) {
            //return response()->json(['errors' => $e], 403);
            $response['status'] = 'fail';
            $response['message'] = 'Getting error in applying coupon code';
            $response['error'][] = $e;
            $response['data'] = [];
        }

        return response()->json($response, 200);

    }

    // public function apply(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'code' => 'required',
    //     ]);

    //     if ($validator->errors()->count()>0) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }

    //     try {
    //         $coupon = Coupon::active()->where(['code' => $request['code']])->first();
    //         if (isset($coupon)) {
    //             if ($coupon['limit'] == null) {
    //                 return response()->json($coupon, 200);
    //             } else {
    //                 $total = Order::where(['user_id' => $request->user()->id, 'coupon_code' => $request['code']])->count();
    //                 if ($total < $coupon['limit']) {
    //                     return response()->json($coupon, 200);
    //                 }else{
    //                     return response()->json([
    //                         'errors' => [
    //                             ['code' => 'coupon', 'message' => 'Coupon code usage limit is over for you!']
    //                         ]
    //                     ], 401);
    //                 }
    //             }

    //         } else {
    //             return response()->json([
    //                 'errors' => [
    //                     ['code' => 'coupon', 'message' => 'not found!']
    //                 ]
    //             ], 401);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json(['errors' => $e], 403);
    //     }
    // }
}
