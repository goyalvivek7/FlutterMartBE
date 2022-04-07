<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CategoryLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{

  public function get_product_with_child_id(Request $request){
    if(isset($request['child_cat_id']) && $request['child_cat_id'] != NULL && $request['child_cat_id'] != ""){
      $childCatId = $request['child_cat_id'];
      try {

        $products = Product::where('child_cat_id', $childCatId)->where('status', 1)->get();
        
        if(!empty($products[0])){
          $status = "success";
        } else {
          $status = "fail";
        }
        
        $response['status'] = $status;
        $response['data'] = $products;
        return response()->json($response, 200);

      } catch (\Exception $e) {

        $response['status'] = 'fail';
        $response['data'] = [];
        $response['error'] = $e;
        return response()->json($response, 200);
        //return response()->json([], 200);

      }

    } else{

      $response['status'] = 'fail';
      $response['data'] = [];
      return response()->json($response, 200);

    }
  }

  public function get_sub_child_with_main_id(Request $request){

    if(isset($request['main_cat_id']) && $request['main_cat_id'] != NULL && $request['main_cat_id'] != ""){
      $mainCatId = $request['main_cat_id'];

      try {
        
        $outputArray = [];
        $subCategories = Category::where(['position' => 1, 'status'=>1, 'parent_id'=>$mainCatId])->get();
        if(isset($subCategories) && !empty($subCategories)){
          
          foreach($subCategories as $subCat){
            $subCatId = $subCat['id'];
            
            $childCategories = Category::where(['position' => 2, 'status'=>1, 'parent_id'=>$subCatId])->get();
            if(isset($childCategories) && !empty($childCategories)){
              $subCat['child_category'] = $childCategories;
            }
          }
          
          $outputArray = $subCategories;
        }
        
        if(!empty($outputArray[0])){
          $status = "success";
        } else {
          $status = "fail";
        }
  
        $response['status'] = $status;
        $response['data'] = $outputArray;
        return response()->json($response, 200);

      } catch (\Exception $e) {
        $response['status'] = 'fail';
        $response['data'] = [];
        $response['error'] = $e;
        return response()->json($response, 200);
        //return response()->json([], 200);
      }

    } else{

      $response['status'] = 'fail';
      $response['data'] = [];
      return response()->json($response, 200);

    }
    
  }

    public function get_categories()
    {
        try {
            $categories = Category::where(['position'=>0,'status'=>1])->latest()->get();
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_childes($id)
    {
        try {
            $categories = Category::where(['parent_id' => $id,'status'=>1])->get();
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
  
  	public function products_with_categories(){

      try {
          
        $outputArray = [];
        $mainCategories = Category::where(['position' => 0,'status'=>1])->get();

        foreach($mainCategories as $mainCategory){
          $mainCatId = $mainCategory['id'];
          
          $subCategories = Category::where(['position' => 1, 'status'=>1, 'parent_id'=>$mainCatId])->get();
          if(isset($subCategories) && !empty($subCategories)){

            foreach($subCategories as $subCat){
              $subCatId = $subCat['id'];

              $childCategories = Category::where(['position' => 2, 'status'=>1, 'parent_id'=>$subCatId])->get();
              if(isset($childCategories) && !empty($childCategories)){

                foreach($childCategories as $childCat){
                  $childCatId = $childCat['id'];
                  $childCatPushArray['child_cat_data'] = $childCat;
                  
                  
                  $products = Product::where('child_cat_id', $childCatId)->where('status', 1)->get();
                  
                  $childCat['products'] = $products;
                }

                $subCat['child_category'] = $childCategories;
              }

            }

            $mainCategory['sub_category'] = $subCategories;
          }

          $outputArray[] = $mainCategory;
        }

        if(!empty($outputArray[0])){
          $status = "success";
        } else {
          $status = "fail";
        }

        $response['status'] = $status;
        $response['data'] = $outputArray;
        return response()->json($response, 200);
        
      } catch (\Exception $e) {
        $response['status'] = 'fail';
        $response['data'] = [];
        $response['error'] = $e;
        return response()->json($response, 200);
      }
    }

    public function all_cat_sub_cat()
    {
      try {
          
        $outputArray = [];
        $mainCategories = Category::where(['position' => 0,'status'=>1])->get();

        foreach($mainCategories as $mainCategory){
          $mainCatId = $mainCategory['id'];
          
          $subCategories = Category::where(['position' => 1, 'status'=>1, 'parent_id'=>$mainCatId])->get();
          if(isset($subCategories) && !empty($subCategories)){

            foreach($subCategories as $subCat){
              $subCatId = $subCat['id'];

              $childCategories = Category::where(['position' => 2, 'status'=>1, 'parent_id'=>$subCatId])->get();
              if(isset($childCategories) && !empty($childCategories)){
                $subCat['child_category'] = $childCategories;
              }

            }

            $mainCategory['sub_category'] = $subCategories;
          }

          $outputArray[] = $mainCategory;
        }

        if(!empty($outputArray[0])){
          $status = "success";
        } else {
          $status = "fail";
        }

        $response['status'] = $status;
        $response['data'] = $outputArray;
        return response()->json($response, 200);
        
      } catch (\Exception $e) {
        $response['status'] = 'fail';
        $response['data'] = [];
        $response['error'] = $e;
        return response()->json($response, 200);
      }
    }
  
  	// public function all_cat_sub_cat()
    // {
    //     try {
    //       	$mainCatArray = [];
    //         $mainCategories = Category::where(['position' => 0,'status'=>1])->get();
          
    //       	foreach($mainCategories as $mainCat){
    //           $catPushArray = [];
    //           $mainCatId = $mainCat['id'];
    //           $catPushArray['cat_data'] = $mainCat;
              
    //           $subCategories = Category::where(['position' => 1, 'status'=>1, 'parent_id'=>$mainCatId])->get();
    //           $catPushArray['sub_cate_data'][''] = $subCategories;
              
    //           $mainCatArray[] = $catPushArray;
    //         }
          
    //       	if(!empty($mainCatArray)){
    //           $status = "success";
    //         } else {
    //             $status = "fail";
    //         }
          	
    //       	$response['status'] = $status;
    //         $response['data'] = $mainCatArray;
    //         return response()->json($response, 200);
          
    //     } catch (\Exception $e) {
    //         return response()->json([], 200);
    //     }
    // }

    public function get_products($id)
    {
        //return response()->json(Helpers::product_data_formatting(CategoryLogic::products($id), true), 200);
      	$fetchedProduct = Helpers::product_data_formatting(CategoryLogic::products($id), true);
        $response = array();
        
        if(count($fetchedProduct) > 0){
            $status = "success";
        } else {
            $status = "fail";
        }
      	$response['total_size'] = count($fetchedProduct);
        $response['status'] = $status;
        $response['products'] = $fetchedProduct;
        return response()->json($response, 200);
    }

    public function get_all_products($id)
    {
        try {
            //return response()->json(Helpers::product_data_formatting(CategoryLogic::all_products($id), true), 200);
          	$fetchedProduct = Helpers::product_data_formatting(CategoryLogic::all_products($id), true);
            $response = array();
            
            if(count($fetchedProduct) > 0){
                $status = "success";
            } else {
                $status = "fail";
            }
            $response['total_size'] = count($fetchedProduct);
            $response['status'] = $status;
            $response['products'] = $fetchedProduct;
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
