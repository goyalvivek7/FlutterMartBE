<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;
use App\Http\Controllers\Controller;
use App\Model\Product;
use App\Model\Review;
use App\Model\Search;
use App\Model\Banner;
use App\Model\Sale;
use App\Model\Category;
use App\Model\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    public function barcode_product($barcode)
    {
        try {

            $getProducts = DB::table('products')->where('bar_code', $barcode)->get();

            if(count($getProducts) > 0){
                $id = $getProducts[0]->id;

                $product = ProductLogic::get_product($id);

                $response['status'] = 'success';
                if(isset($product) && !empty($product)){
                    $product = Helpers::product_data_formatting($product, false);
                    $response['message'] = 'Product Detail Found.';
                    $response['data'][] = $product;
                } else {
                    $response['message'] = 'Product Detail Not Found.';
                    $response['data'] = [];
                }

            } else {
                $response['status'] = 'fail';
                $response['message'] = 'No Product Detail Found.';
                $response['data'] = [];
            }

            

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response['status'] = 'fail';
            $response['message'] = 'No Product Detail Found.';
            $response['data'] = [];
            return response()->json($response, 200);
            //return response()->json(['errors' => ['code' => 'product-001', 'message' => 'Product not found!'],], 404);
        }
    }
  	
  	public function recent_search(Request $request)
    {
        //$searchTerms = Search::where(['user_id' => $request['user_id']])->distinct ('search_term')->get();
        $searchTerms = Search::select('search_term', 'user_id')->where(['user_id' => $request['user_id']])->distinct()->limit($request['limit'])->get();

        if(count($searchTerms) == 0){
            $apiStatus = "fail";
        } else {
            $apiStatus = "success";
        }
        $apiStatus = "success";
        $search = [
            'total_size' => count($searchTerms),
            'status' => $apiStatus,
            'terms' => $searchTerms
        ];
        return response()->json($search, 200);
    }
  
    public function get_latest_products(Request $request)
    {
        $products = ProductLogic::get_latest_products($request['limit'], $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    public function get_searched_products(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required',
        //     ''
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        // }

        //$products = ProductLogic::search_products($request['name'], $request['limit'], $request['offset']);
        //echo '@@@@<pre />'; print_r($products); die;
        //if (count($products['products']) == 0) {
        if ($request['name'] != '' && $request['limit'] != "" && $request['offset'] != "") {
            $key = explode(' ', $request['name']);
            $paginator = Product::active()->withCount(['wishlist'])->with(['rating'])->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            })->paginate($request['limit'], ['*'], 'page', $request['offset']);

            // if($paginator->total() == 0){
            //     $apiStatus = "fail";
            // } else {
            //     $apiStatus = "success";
            // }
            $apiStatus = "success";
          
            $products = [
                'total_size' => $paginator->total(),
              	'status' => $apiStatus,
                'limit' => $request['limit'],
                'offset' => $request['offset'],
                'products' => $paginator->items()
            ];

            $searches = new Search();
            $searches->search_term = $request['name'];
            if($request['user_id'] && $request['user_id'] != ""){
            $searches->user_id = $request['user_id'];
            }
            $searches->save();
        
            $products['products'] = Helpers::product_data_formatting($products['products'], true);
            return response()->json($products, 200);
        } else {
            $products = [
                'total_size' => 0,
              	'status' => 'fail',
                'message' => 'Please send require fields',
                'products' => []
            ];
            return response()->json($products, 200);
        }
      	
    }

    public function get_product($id)
    {
        try {
            $product = ProductLogic::get_product($id);

            $response['status'] = 'success';
            if(isset($product) && !empty($product)){
                $product = Helpers::product_data_formatting($product, false);
                $response['message'] = 'Product Detail Found.';
                $response['data'][] = $product;
            } else {
                $response['message'] = 'Product Detail Not Found.';
                $response['data'] = [];
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response['status'] = 'fail';
            $response['message'] = 'No Product Detail Found.';
            $response['data'] = [];
            return response()->json($response, 200);
            //return response()->json(['errors' => ['code' => 'product-001', 'message' => 'Product not found!'],], 404);
        }
    }

    public function get_related_products($id)
    {
        if (Product::find($id)) {
            $products = ProductLogic::get_related_products($id);
            $products = Helpers::product_data_formatting($products, true);
            return response()->json($products, 200);
        }
        return response()->json([
            'errors' => ['code' => 'product-001', 'message' => 'Product not found!'],
        ], 404);
    }

    public function get_product_reviews($id)
    {
        $reviews = Review::with(['customer'])->where(['product_id' => $id])->get();

        $storage = [];
        foreach ($reviews as $item) {
            $item['attachment'] = json_decode($item['attachment']);
            array_push($storage, $item);
        }

        return response()->json($storage, 200);
    }

    public function get_product_rating($id)
    {
        try {
            $product = Product::find($id);
            $overallRating = ProductLogic::get_overall_rating($product->reviews);
            return response()->json(floatval($overallRating[0]), 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function submit_product_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'order_id' => 'required',
            'comment' => 'required',
            'rating' => 'required|numeric|max:5',
        ]);

        $product = Product::find($request->product_id);
        if (isset($product) == false) {
            $validator->errors()->add('product_id', 'There is no such product');
        }

        $multi_review = Review::where(['product_id' => $request->product_id, 'user_id' => $request->user()->id])->first();
        if (isset($multi_review)) {
            $review = $multi_review;
        } else {
            $review = new Review;
        }

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $image_array = [];
        if (!empty($request->file('attachment'))) {
            foreach ($request->file('attachment') as $image) {
                if ($image != null) {
                    if (!Storage::disk('public')->exists('review')) {
                        Storage::disk('public')->makeDirectory('review');
                    }
                    array_push($image_array, Storage::disk('public')->put('review', $image));
                }
            }
        }

        $review->user_id = $request->user()->id;
        $review->product_id = $request->product_id;
        $review->order_id = $request->order_id;
        $review->comment = $request->comment;
        $review->rating = $request->rating;
        $review->attachment = json_encode($image_array);
        $review->save();

        return response()->json(['message' => 'successfully review submitted!'], 200);
    }

    public function get_discounted_products()
    {
        try {
            $products = Helpers::product_data_formatting(Product::active()->withCount(['wishlist'])->with(['rating'])->where('discount', '>', 0)->get(), true);
            return response()->json($products, 200);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => 'Set menu not found!'],
            ], 404);
        }
    }

    public function get_daily_need_products()
    {
        try {
            $products = Helpers::product_data_formatting(Product::active()->withCount(['wishlist'])->with(['rating'])->where(['daily_needs' => 1])->get(), true);
            return response()->json($products, 200);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => 'Products not found!'],
            ], 404);
        }
    }
  
  	public function homepage_sales(){
        try {

          	$bannerRecords = Banner::where(['status'=>1])->get();
          	$salesRecords = Sale::where(['status'=>1])->get();
            
            $bannerArray = array(); $saleData = [];  $resData = []; $salearray = array();

            $welcomeReords = DB::table('miscellaneous')->where('setting_type', 'welcome_icons')->where('status', 1)->orderBy('priorty', 'ASC')->get();
            if($welcomeReords && $welcomeReords != "" && $welcomeReords != NULL){
                $salearray['welcome_icons'] = $welcomeReords;
            }

            if($salesRecords && $salesRecords != "" && $salesRecords != NULL){

                foreach($salesRecords as $saleRecord){
                    //echo '<pre />'; print_r($saleRecord);
                    //$salearray = array();
                  	$saleUrl = $saleRecord['sale_url'];
                    $salearray[$saleUrl] = $saleRecord;

                    $saleId = $saleRecord['id'];
                    $saleType = $saleRecord['sale_type'];
                    $catId = $saleRecord['cat_id'];
                    $subCatId = $saleRecord['sub_cat_id'];
                    $childCatId = $saleRecord['child_cat_id'];
                    if($saleType == "categories"){
                        if($childCatId && $childCatId != "" && $childCatId != NULL){
                            $childCatArray = json_decode($childCatId);
                            $categories = Category::active()->whereIn('id', $childCatArray)->get();
                            $salearray[$saleUrl]['categories'] = $categories;
                            //echo 'childCatArray <pre />'; print_r($childCatArray);
                        } elseif($subCatId && $subCatId != "" && $subCatId != NULL){
                            $subCatArray = json_decode($subCatId);
                            $categories = Category::active()->whereIn('id', $subCatArray)->get();
                            $salearray[$saleUrl]['categories'] = $categories;
                            //echo 'subCatArray <pre />'; print_r($subCatArray);
                        } else {
                            $catArray = json_decode($catId);
                            $categories = Category::active()->whereIn('id', $catArray)->get();
                            $salearray[$saleUrl]['categories'] = $categories;
                            //echo 'catArray <pre />'; print_r($catArray);
                        }
                    }
                    if($saleType == "products"){
                        $allowIdArray = json_decode($saleRecord['allow_ids']);
                        //echo 'allowIdArray <pre />'; print_r($allowIdArray);
                        $products = Product::active()->whereIn('id', $allowIdArray)->get();
                        $salearray[$saleUrl]['products'] = $products;
                    }
                    //array_push($saleData, $salearray);
                    //echo $saleId;
                }
            }

            if($salesRecords && $salesRecords != "" && $salesRecords != NULL){
                foreach($bannerRecords as $bannerRecord){
                $bannerUrl = $bannerRecord['banner_url'];
                $salearray[$bannerUrl][] = $bannerRecord;
                }
            }
            //$resData['sale'] = $saleData;
            return response()->json(["status" => "success", "data" => $salearray], 200);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ["status" => "fail", 'message' => "Sales not found!", 'data' => []],
            ], 404);
        }
    }
  	
}
