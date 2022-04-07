<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CategoryLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\Product;

class CategoryController extends Controller
{
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
          	$mainCatArray = []; $catPushArray = [];
            $mainCategories = Category::where(['position' => 0,'status'=>1])->get();
          
          
          	foreach($mainCategories as $mainCat){
                $mainCatPushArray = [];
                $mainCatId = $mainCat['id'];
                $mainCatPushArray['cat_data'] = $mainCat;


				$subCategories = Category::where(['position' => 1, 'status'=>1, 'parent_id'=>$mainCatId])->get();
                if(isset($subCategories) && !empty($subCategories)){
                  
                  
                  foreach($subCategories as $subCat){
                    $subCatPushArray = [];
                    $subCatId = $subCat['id'];
                    $subCatPushArray['sub_cat_data'] = $subCat;
                    
                    
                    
                    $childCategories = Category::where(['position' => 2, 'status'=>1, 'parent_id'=>$subCatId])->get();
                    if(isset($childCategories) && !empty($childCategories)){
                      
                      
                      foreach($childCategories as $childCat){
                        $childCatPushArray = [];
                        $childCatId = $childCat['id'];
                        $childCatPushArray['child_cat_data'] = $childCat;
                        
                        
                        $products = Product::where('child_cat_id', $childCatId)->where('status', 1)->get();
                        $childCatPushArray['products'] = $products;
                        
                        
                      } // Child Category For Loop
                      $subCatPushArray['child_category'][] = $childCatPushArray;
                      
                      
                    } // Child Category If Condition
                    
                    
                    
                  } //Sub Category For Loop
                  $mainCatPushArray['sub_category'][] = $subCatPushArray;
                  

                } //Sub Category If codition


                $mainCatArray[] = $mainCatPushArray;
            } // Main Category Foor Loop
          	
          
          	if(!empty($mainCatArray)){
              $status = "success";
            } else {
                $status = "fail";
            }
          	
          	$response['status'] = $status;
            $response['data'] = $mainCatArray;
            return response()->json($response, 200);
          
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
  
  	public function all_cat_sub_cat()
    {
        try {
          	$mainCatArray = [];
            $mainCategories = Category::where(['position' => 0,'status'=>1])->get();
          
          	foreach($mainCategories as $mainCat){
              $catPushArray = [];
              $mainCatId = $mainCat['id'];
              $catPushArray['cat_data'] = $mainCat;
              
              $subCategories = Category::where(['position' => 1, 'status'=>1, 'parent_id'=>$mainCatId])->get();
              $catPushArray['sub_cate_data'][''] = $subCategories;
              
              $mainCatArray[] = $catPushArray;
            }
          
          	if(!empty($mainCatArray)){
              $status = "success";
            } else {
                $status = "fail";
            }
          	
          	$response['status'] = $status;
            $response['data'] = $mainCatArray;
            return response()->json($response, 200);
          
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

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
