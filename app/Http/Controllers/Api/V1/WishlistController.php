<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{

    public function check(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $wishlist = Wishlist::where('user_id', $request->user_id)->where('product_id', $request->product_id)->first();
        if (empty($wishlist)) {
            $response['status'] = 'fail';
            $response['message'] = 'wishlist not added!';
            $response['data'] = [];
            return response()->json($response, 200);
        } else {
            $response['status'] = 'success';
            $response['message'] = 'wishlist added!';
            $response['data'] = [];
            return response()->json($response, 200);
        }
    }


    public function add_to_wishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            return response()->json($response, 200);
            //return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        //$wishlist = Wishlist::where('user_id', $request->user()->id)->where('product_id', $request->product_id)->first();
        $wishlist = Wishlist::where('user_id', $request->user_id)->where('product_id', $request->product_id)->first();

        if (empty($wishlist)) {
            $wishlist = new Wishlist;
            //$wishlist->user_id = $request->user()->id;
            $wishlist->user_id = $request->user_id;
            $wishlist->product_id = $request->product_id;
            $wishlist->save();
            $response['status'] = 'success';
            $response['message'] = 'successfully added!';
            return response()->json($response, 200);
            //return response()->json(['message' => 'successfully added!'], 200);
        }

        $response['status'] = 'fail';
        $response['message'] = 'Already in your wishlist.';
        return response()->json($response, 200);
        //return response()->json(['message' => 'Already in your wishlist'], 409);
    }

    public function remove_from_wishlist(Request $request)
    {
      	
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            return response()->json($response, 200);
            //return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $wishlist = Wishlist::where('user_id', $request->user_id)->where('product_id', $request->product_id)->first();
        //$wishlist = Wishlist::where('user_id', $request->user()->id)->where('product_id', $request->product_id)->first();

        if (!empty($wishlist)) {
            //Wishlist::where(['user_id' => $request->user()->id, 'product_id' => $request->product_id])->delete();
            Wishlist::where(['user_id' => $request->user_id, 'product_id' => $request->product_id])->delete();
            $response['status'] = 'success';
            $response['message'] = 'successfully removed!';
            return response()->json($response, 200);
            //return response()->json(['message' => 'successfully removed!'], 200);

        }
        $response['status'] = 'fail';
        $response['message'] = 'No such data found!';
        return response()->json($response, 200);
        //return response()->json(['message' => 'No such data found!'], 404);
    }

    public function wish_list(Request $request)
    {
      	$validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            return response()->json($response, 200);
            //return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        //$wishList = Wishlist::where('user_id', $request->user_id)->get();
        $wishList = Wishlist::select('wishlists.*', 'products.*', 'wishlists.id AS wishlist_id', 'categories.name AS child_cat_name')->leftJoin('products', 'products.id', '=', 'wishlists.product_id')->leftJoin('categories', 'categories.id', '=', 'products.child_cat_id')->where('wishlists.user_id', $request->user_id)->orderBy('wishlists.id', 'DESC')->get();
        $response['status'] = 'success';
        $response['message'] = 'Wishlist successfully fetched!';
        $response['data'] = $wishList;
        return response()->json($response, 200);
        //return response()->json(Wishlist::where('user_id', $request->user()->id)->get(), 200);
    }
}
