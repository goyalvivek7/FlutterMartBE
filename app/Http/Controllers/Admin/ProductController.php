<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\Product;
use App\Model\Brand;
use App\Model\Review;
use App\Model\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Rap2hpoutre\FastExcel\FastExcel;
use GuzzleHttp\Client;

class ProductController extends Controller
{

    public function get_product_listing(Request $request)
    {
        //$catId = explode(',', $request['cat-id']);
        $catId = $request['cat-id'];
        $catPosition = $request['cat-position'];
        if($catPosition == 0){
            $allProducts = DB::table('products')->whereIn('cat_id', $catId)->where('status', 1)->get();
        }
        if($catPosition == 1){
            $allProducts = DB::table('products')->whereIn('sub_cat_id', $catId)->where('status', 1)->get();
        }
        if($catPosition == 2){
            $allProducts = DB::table('products')->whereIn('child_cat_id', $catId)->where('status', 1)->get();
        }
        //echo $allProducts->toSql();
        return $allProducts;
    }

    public function get_categories_multi(Request $request)
    {
        $catArray = $request['parent_id'];
        $cat = Category::whereIn('parent_id', $catArray)->get();
        return $cat;
    }

    public function search_update(Request $request, $id)
    {
        //echo $id."@@@@@@".$request['selected_product']."######";
        if(isset($request['selected_product']) && !empty($request['selected_product']) && $request['selected_product'] != ""){
            //echo "@@@@"; die;
            $search_ids = json_encode($request['selected_product']);
        } else {
            $search_ids = null;
        }
        
        $category = DB::table('custome_searches')->where('id', $request->id)->update(['search_ids'=>$search_ids]);
        Toastr::success('Search updated!');
        return back();
    }

    public function popular_product(Request $request)
    {
        $popularSearch = DB::table('custome_searches')->where('title', 'popular_search')->where('status', 1)->get();
        //$products = $query->paginate(Helpers::getPagination())->appends($query_param);
        $allProducts = DB::table('products')->where('status', 1)->get();
        $popular = $popularSearch[0];
        $searchType = $popular->search_type;
        $products = array();
        
        if($popular->search_ids != null && $popular->search_ids != ""){
            $searchIds = json_decode($popular->search_ids);
            if($searchType == "products"){
                $products = Product::whereIn('id', $searchIds)->where('status', 1)->get();
            }
        } else {
            $searchIds = "";
        }
        return view('admin-views.product.popular-product', compact('popularSearch', 'products', 'allProducts', 'searchIds'));
    }

    public function variant_combination(Request $request)
    {
        $options = [];
        $price = $request->price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('', $request[$name]);
                array_push($options, explode(',', $my_str));
            }
        }

        $result = [[]];
        foreach ($options as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }
        $combinations = $result;
        return response()->json([
            'view' => view('admin-views.product.partials._variant-combinations', compact('combinations', 'price', 'product_name'))->render(),
        ]);
    }

    public function get_categories(Request $request)
    {
        $cat = Category::where(['parent_id' => $request->parent_id])->get();
        $res = '<option value="' . 0 . '" disabled selected>---Select---</option>';
        foreach ($cat as $row) {
            if ($row->id == $request->sub_category) {
                $res .= '<option value="' . $row->id . '" selected >' . $row->name . '</option>';
            } else {
                $res .= '<option value="' . $row->id . '">' . $row->name . '</option>';
            }
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function index()
    {
        $categories = Category::where(['position' => 0])->get();
        return view('admin-views.product.index', compact('categories'));
    }

    public function list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = Product::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('name', 'like', "%{$value}%");
                }
            })->latest();
            $query_param = ['search' => $request['search']];
        }else{
            $query = Product::latest();
        }
        $products = $query->paginate(Helpers::getPagination())->appends($query_param);
        return view('admin-views.product.list', compact('products','search'));
    }

    public function search(Request $request)
    {
        $key = explode(' ', $request['search']);
        $products = Product::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })->get();
        return response()->json([
            'view' => view('admin-views.product.partials._table', compact('products'))->render(),
        ]);
    }

    public function view($id)
    {
        $product = Product::where(['id' => $id])->first();
        $reviews = Review::where(['product_id' => $id])->latest()->paginate(20);
        return view('admin-views.product.view', compact('product', 'reviews'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:products',
            'category_id' => 'required',
            'images' => 'required',
            'total_stock' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:1',
        ], [
            'name.required' => 'Product name is required!',
            'category_id.required' => 'category  is required!',
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', 'Discount can not be more or equal to the price!');
        }

        $img_names = [];
        if (!empty($request->file('images'))) {
            foreach ($request->images as $img) {
                $image_data = Helpers::upload('product/', 'png', $img);
                array_push($img_names, $image_data);
            }
            $image_data = json_encode($img_names);
        } else {
            $image_data = json_encode([]);
        }

        $p = new Product;
        $p->name = $request->name[array_search('en', $request->lang)];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
            $p->cat_id = $request->category_id;
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
            $p->sub_cat_id = $request->sub_category_id;
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
            $p->child_cat_id = $request->sub_sub_category_id;
        }

        $p->category_ids = json_encode($category);
        $p->description = $request->description[array_search('en', $request->lang)];

        $choice_options = [];
        if ($request->has('choice')) {
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_' . $no;
                if ($request[$str][0] == null) {
                    $validator->getMessageBag()->add('name', 'Attribute choice option values can not be null!');
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $item['name'] = 'choice_' . $no;
                $item['title'] = $request->choice[$key];
                $item['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', $request[$str])));
                array_push($choice_options, $item);
            }
        }

        $p->choice_options = json_encode($choice_options);
        $variations = [];
        $options = [];
        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('|', $request[$name]);
                array_push($options, explode(',', $my_str));
            }
        }
        //Generates the combinations of customer choice options
        $combinations = Helpers::combinations($options);

        $stock_count = 0;
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $k => $item) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        $str .= str_replace(' ', '', $item);
                    }
                }
                $item = [];
                $item['type'] = $str;
                $item['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
                $item['stock'] = abs($request['stock_' . str_replace('.', '_', $str)]);
                $item['barcode'] = $request['barcode_' . str_replace('.', '_', $str)];
                array_push($variations, $item);
                $stock_count += $item['stock'];
            }
        } else {
            $stock_count = (integer)$request['total_stock'];
        }

        if ((integer)$request['total_stock'] != $stock_count) {
            $validator->getMessageBag()->add('total_stock', 'Stock calculation mismatch!');
        }

        if ($validator->getMessageBag()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        if(isset($request->barcode) && $request->barcode != ""){
            $barcode = $request->barcode;
        } else {
            $barcode = NULL;
        }

        if(isset($request->sku) && $request->sku != ""){
            $sku = $request->sku;
        } else {
            $sku = NULL;
        }

        //combinations end
        $p->variations = json_encode($variations);
        $p->price = $request->price;
        $p->org_price = $request->org_price;
        $p->bar_code = $barcode;
        $p->sku = $sku;
        $p->unit = $request->unit;
        $p->image = $image_data;
        $p->capacity = $request->capacity;
        // $p->set_menu = $request->item_type;

        $p->tax = $request->tax_type == 'amount' ? $request->tax : $request->tax;
        $p->tax_type = $request->tax_type;

        $p->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $p->discount_type = $request->discount_type;
        $p->total_stock = $request->total_stock;

        $p->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $p->save();

        $data = [];
        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                array_push($data, Array(
                    'translationable_type'  => 'App\Model\Product',
                    'translationable_id'    => $p->id,
                    'locale'                => $key,
                    'key'                   => 'name',
                    'value'                 => $request->name[$index],
                ));
            }
            if($request->description[$index] && $key != 'en')
            {
                array_push($data, Array(
                    'translationable_type'  => 'App\Model\Product',
                    'translationable_id'    => $p->id,
                    'locale'                => $key,
                    'key'                   => 'description',
                    'value'                 => $request->description[$index],
                ));
            }
        }


        Translation::insert($data);

        return response()->json([], 200);
    }

    public function edit($id)
    {
        $product = Product::withoutGlobalScopes()->with('translations')->find($id);
        $product_category = json_decode($product->category_ids);
        //echo '<pre />'; print_r(json_decode($product->category_ids)); die;
        $categories = Category::where(['parent_id' => 0])->get();
        return view('admin-views.product.edit', compact('product', 'product_category', 'categories'));
    }

    public function status(Request $request)
    {
        $product = Product::find($request->id);
        $product->status = $request->status;
        $product->save();
        Toastr::success('Product status updated!');
        return back();
    }

    public function daily_needs(Request $request)
    {
        $product = Product::find($request->id);
        $product->daily_needs = $request->status;
        $product->save();
        return response()->json([], 200);
    }

    public function sync_products(Request $request){

        // $companyResponse = file_get_contents('http://103.234.185.42:50/API/ItemAttribute1?TokenId=TESMART');
        // $companyResponseArray = json_decode($companyResponse,TRUE);
        // if(is_array($companyResponseArray) && !empty($companyResponseArray)){

        //     Brand::where('id', '!=', null)->update(['status' => 0]);

        //     foreach($companyResponseArray as $companyArray){
        //         foreach($companyArray as $company){
                    
        //             $companyId = $company['company_id'];
        //             $brandApiStatus = $company['status'];
                    
        //             if($brandApiStatus == "N"){
        //                 $brandStatus = 0;
        //             } elseif($brandApiStatus == "Y"){
        //                 $brandStatus = 1;
        //             }

        //             $checkBrand = DB::table('brands')->where('company_id', $companyId)->limit(1)->get();
        //             if($checkBrand && !empty($checkBrand) && isset($checkBrand[0]) && !empty($checkBrand[0])){

        //                 Brand::where(['company_id' => $companyId])->update([
        //                     'company_name' => $company['company_name'],
        //                     'company_subtitle' => $company['company_subtitle'],
        //                     'status' => $brandStatus,
        //                     'created_date' => $company['date_time'],
        //                 ]);

        //             } else {    

        //                 $brandData = [
        //                     'company_id' => $company['company_id'],
        //                     'company_name' => $company['company_name'],
        //                     'company_subtitle' => $company['company_subtitle'],
        //                     'status' => $brandStatus,
        //                     'created_date' => $company['date_time']
        //                 ];
        //                 DB::table('brands')->insert($brandData);

        //             }

        //         }
        //     }
        // }



        // Main Category API
        // $categoryResponse = file_get_contents('http://103.234.185.42:50/API/ItemAttribute2?TokenId=TESMART');
        // $categoryResponseArray = json_decode($categoryResponse,TRUE);
        // if(is_array($categoryResponseArray) && !empty($categoryResponseArray)){

        //     Category::where('id', '!=', null)->update(['status' => 0]);

        //     foreach($categoryResponseArray as $categoryArray){
        //         foreach($categoryArray as $catArray){

        //             $catCode = $catArray['category_id'];
        //             $catName = $catArray['category_name'];
        //             $catSubtitle = $catArray['category_subtitle'];
        //             $catApiStatus = $catArray['status'];
        //             $parentCompanyId = $catArray['parent_company_id'];
        //             $dateTime = $catArray['date_time'];

        //             if($catApiStatus == "N"){
        //                 $catStatus = 0;
        //             } elseif($catApiStatus == "Y"){
        //                 $catStatus = 1;
        //             }

        //             $checkCategory = DB::table('categories')->where('code', $catCode)->limit(1)->get();

        //             if($parentCompanyId == ""){
        //                 $parentCompanyId = 0;
        //             }

        //             if($checkCategory && !empty($checkCategory) && isset($checkCategory[0]) && !empty($checkCategory[0])){

        //                 Category::where(['code' => $catCode])->update([
        //                     'name' => $catName,
        //                     'sub_title' => $catSubtitle,
        //                     'status' => $catStatus,
        //                     'parent_id' => $parentCompanyId,
        //                 ]);

        //             } else {    

        //                 $catData = [
        //                     'code' => $catCode,
        //                     'name' => $catName,
        //                     'sub_title' => $catSubtitle,
        //                     'status' => $catStatus,
        //                     'parent_id' => $parentCompanyId,
        //                     'position' => 0,
        //                 ];
        //                 DB::table('categories')->insert($catData);

        //             }
        //         }
        //     }

        // }



        // Sub Category API
        // $categoryResponse = file_get_contents('http://103.234.185.42:50/API/ItemAttribute3?TokenId=TESMART');
        // $categoryResponseArray = json_decode($categoryResponse,TRUE);
        // if(is_array($categoryResponseArray) && !empty($categoryResponseArray)){

        //     Category::where('id', '!=', null)->update(['status' => 0]);

        //     foreach($categoryResponseArray as $categoryArray){
        //         foreach($categoryArray as $catArray){

        //             $catCode = $catArray['category_id'];
        //             $catName = $catArray['category_name'];
        //             $catSubtitle = $catArray['category_subtitle'];
        //             $catApiStatus = $catArray['status'];
        //             $parentCompanyId = $catArray['parent_company_id'];
        //             $dateTime = $catArray['date_time'];

        //             if($catApiStatus == "N"){
        //                 $catStatus = 0;
        //             } elseif($catApiStatus == "Y"){
        //                 $catStatus = 1;
        //             }

        //             $checkCategory = DB::table('categories')->where('code', $catCode)->limit(1)->get();

        //             if($parentCompanyId == ""){
        //                 $parentCompanyId = 0;
        //             }

        //             if($checkCategory && !empty($checkCategory) && isset($checkCategory[0]) && !empty($checkCategory[0])){

        //                 Category::where(['code' => $catCode])->update([
        //                     'name' => $catName,
        //                     'sub_title' => $catSubtitle,
        //                     'status' => $catStatus,
        //                     'parent_id' => $parentCompanyId,
        //                 ]);

        //             } else {    

        //                 $catData = [
        //                     'code' => $catCode,
        //                     'name' => $catName,
        //                     'sub_title' => $catSubtitle,
        //                     'status' => $catStatus,
        //                     'parent_id' => $parentCompanyId,
        //                     'position' => 1,
        //                 ];
        //                 DB::table('categories')->insert($catData);

        //             }
        //         }
        //     }
        // }
        

        // Child Category API
        // $categoryResponse = file_get_contents('http://103.234.185.42:50/API/ItemAttribute4?TokenId=TESMART');
        // $categoryResponseArray = json_decode($categoryResponse,TRUE);
        // if(is_array($categoryResponseArray) && !empty($categoryResponseArray)){

        //     Category::where('id', '!=', null)->update(['status' => 0]);

        //     foreach($categoryResponseArray as $categoryArray){
        //         foreach($categoryArray as $catArray){

        //             $catCode = $catArray['category_id'];
        //             $catName = $catArray['category_name'];
        //             $catSubtitle = $catArray['category_subtitle'];
        //             $catApiStatus = $catArray['status'];
        //             $parentCompanyId = $catArray['parent_company_id'];
        //             $dateTime = $catArray['date_time'];

        //             if($catApiStatus == "N"){
        //                 $catStatus = 0;
        //             } elseif($catApiStatus == "Y"){
        //                 $catStatus = 1;
        //             }

        //             $checkCategory = DB::table('categories')->where('code', $catCode)->limit(1)->get();

        //             if($parentCompanyId == ""){
        //                 $parentCompanyId = 0;
        //             }

        //             if($checkCategory && !empty($checkCategory) && isset($checkCategory[0]) && !empty($checkCategory[0])){

        //                 Category::where(['code' => $catCode])->update([
        //                     'name' => $catName,
        //                     'sub_title' => $catSubtitle,
        //                     'status' => $catStatus,
        //                     'parent_id' => $parentCompanyId,
        //                 ]);

        //             } else {

        //                 $catData = [
        //                     'code' => $catCode,
        //                     'name' => $catName,
        //                     'sub_title' => $catSubtitle,
        //                     'status' => $catStatus,
        //                     'parent_id' => $parentCompanyId,
        //                     'position' => 2,
        //                 ];
        //                 DB::table('categories')->insert($catData);

        //             }
        //         }
        //     }

        // }



        $bnpResponse = file_get_contents('http://103.234.185.42:50/API/ItemAttribute5?TokenId=TESMART');
        $productGroupResponseArray = json_decode($bnpResponse,TRUE);
        echo '<pre />'; print_r($productGroupResponseArray);
        // if(is_array($productResponseArray) && !empty($productResponseArray)){
        //     if(is_array($productResponseArray[0]) && !empty($productResponseArray[0])){
        //         $productArray = $productResponseArray[0];
        //         foreach($productArray as $product){
        //             $productSku = $product['product_sku'];
        //             $productCategory = $product['category'];
        //             $productSubCategory = $product['sub_category'];
        //             $productChildCategory = $product['child_category'];

        //             $checkCategory = DB::table('categories')->where('name', $productCategory)->limit(1)->get();
        //             if(isset($checkCategory) && !empty($checkCategory) && isset($checkCategory[0]) && !empty($checkCategory[0])){
        //                 $catId = $checkCategory[0]->id;
        //             } else {
        //                 $category = new Category();
        //                 $categoryCount = $category->count();
                        
        //                 $category->name = $productCategory;
        //                 $category->parent_id = 0;
        //                 $category->position = 0;
        //                 $category->status = 1;
        //                 $category->save();
        //                 $catId = $category->id;
        //             }

        //             $checkSubCategory = DB::table('categories')->where('name', $productSubCategory)->limit(1)->get();
        //             if(isset($checkSubCategory) && !empty($checkSubCategory) && isset($checkSubCategory[0]) && !empty($checkSubCategory[0])){
        //                 $subCatId = $checkSubCategory[0]->id;
        //             } else {
        //                 $category = new Category();
        //                 $categoryCount = $category->count();
                        
        //                 $category->name = $productSubCategory;
        //                 $category->parent_id = $catId;
        //                 $category->position = 1;
        //                 $category->status = 1;
        //                 $category->save();
        //                 $subCatId = $category->id;
        //             }

        //             $checkChildCategory = DB::table('categories')->where('name', $productChildCategory)->limit(1)->get();
        //             if(isset($checkChildCategory) && !empty($checkChildCategory) && isset($checkChildCategory[0]) && !empty($checkChildCategory[0])){
        //                 $childCatId = $checkChildCategory[0]->id;
        //             } else {
        //                 $category = new Category();
        //                 $categoryCount = $category->count();
                        
        //                 $category->name = $productChildCategory;
        //                 $category->parent_id = $subCatId;
        //                 $category->position = 2;
        //                 $category->status = 1;
        //                 $category->save();
        //                 $childCatId = $category->id;
        //             }

        //             $checkProduct = DB::table('products')->where('sku', $productSku)->limit(1)->get();
                    
        //             if($checkProduct && !empty($checkProduct) && isset($checkProduct[0]) && !empty($checkProduct[0])){

        //                 $productSku = $product['product_sku'];
                        
        //                 Product::where(['sku' => $productSku])->update([
        //                     'name' => $product['product_name'],
        //                     'sub_title' => $product['product_subtitle'],
        //                     'description' => $product['description'],
        //                     'bar_code' => $product['bar/qr_code'],
        //                     'unit' => $product['unit'],
        //                     'cat_id' => $catId,
        //                     'sub_cat_id' => $subCatId,
        //                     'child_cat_id' => $childCatId,
        //                     'weight' => $product['weight'],
        //                     'org_price' => $product['mrp'],
        //                     'price' => $product['offer_price'],
        //                     'total_stock' => $product['stock'],
        //                 ]);

        //             } else {

        //                 $p = new Product;
        //                 $p->sku = $product['product_sku'];
        //                 $p->name = $product['product_name'];
        //                 $p->sub_title = $product['product_subtitle'];
        //                 $p->description = $product['description'];
        //                 $p->bar_code = $product['bar/qr_code'];
        //                 $p->unit = $product['unit'];
        //                 $p->cat_id = $catId;
        //                 $p->sub_cat_id = $subCatId;
        //                 $p->child_cat_id = $childCatId;
        //                 $p->weight = $product['weight'];
        //                 $p->org_price = $product['mrp'];
        //                 $p->price = $product['offer_price'];
        //                 $p->total_stock = $product['stock'];

        //                 $category = [];
        //                 if ($catId != null) {
        //                     array_push($category, ['id' => $catId, 'position' => 1,]);
        //                 }
        //                 if ($subCatId != null) {
        //                     array_push($category, ['id' => $subCatId, 'position' => 2,]);
        //                 }
        //                 if ($childCatId != null) {
        //                     array_push($category, ['id' => $childCatId, 'position' => 3,]);
        //                 }

        //                 $p->category_ids = json_encode($category);
        //                 $p->image =json_encode([]);


        //                 $choice_options = [];
        //                 $p->choice_options = json_encode($choice_options);
        //                 $variations = [];
        //                 $options = [];
        //                 $combinations = Helpers::combinations($options);
        //                 $p->variations = json_encode($variations);
        //                 $p->save();


        //             }
        //         }
        //     }
        // }





        die;

        $bnpResponse = file_get_contents('http://103.234.185.42:50/API/Item?TokenId=TESMART');
        $productResponseArray = json_decode($bnpResponse,TRUE);
        if(is_array($productResponseArray) && !empty($productResponseArray)){
            if(is_array($productResponseArray[0]) && !empty($productResponseArray[0])){
                $productArray = $productResponseArray[0];
                foreach($productArray as $product){
                    $productSku = $product['product_sku'];
                    $productCategory = $product['category'];
                    $productSubCategory = $product['sub_category'];
                    $productChildCategory = $product['child_category'];

                    $checkCategory = DB::table('categories')->where('name', $productCategory)->limit(1)->get();
                    if(isset($checkCategory) && !empty($checkCategory) && isset($checkCategory[0]) && !empty($checkCategory[0])){
                        $catId = $checkCategory[0]->id;
                    } else {
                        $category = new Category();
                        $categoryCount = $category->count();
                        
                        $category->name = $productCategory;
                        $category->parent_id = 0;
                        $category->position = 0;
                        $category->status = 1;
                        $category->save();
                        $catId = $category->id;
                    }

                    $checkSubCategory = DB::table('categories')->where('name', $productSubCategory)->limit(1)->get();
                    if(isset($checkSubCategory) && !empty($checkSubCategory) && isset($checkSubCategory[0]) && !empty($checkSubCategory[0])){
                        $subCatId = $checkSubCategory[0]->id;
                    } else {
                        $category = new Category();
                        $categoryCount = $category->count();
                        
                        $category->name = $productSubCategory;
                        $category->parent_id = $catId;
                        $category->position = 1;
                        $category->status = 1;
                        $category->save();
                        $subCatId = $category->id;
                    }

                    $checkChildCategory = DB::table('categories')->where('name', $productChildCategory)->limit(1)->get();
                    if(isset($checkChildCategory) && !empty($checkChildCategory) && isset($checkChildCategory[0]) && !empty($checkChildCategory[0])){
                        $childCatId = $checkChildCategory[0]->id;
                    } else {
                        $category = new Category();
                        $categoryCount = $category->count();
                        
                        $category->name = $productChildCategory;
                        $category->parent_id = $subCatId;
                        $category->position = 2;
                        $category->status = 1;
                        $category->save();
                        $childCatId = $category->id;
                    }

                    $checkProduct = DB::table('products')->where('sku', $productSku)->limit(1)->get();
                    
                    if($checkProduct && !empty($checkProduct) && isset($checkProduct[0]) && !empty($checkProduct[0])){

                        $productSku = $product['product_sku'];
                        
                        Product::where(['sku' => $productSku])->update([
                            'name' => $product['product_name'],
                            'sub_title' => $product['product_subtitle'],
                            'description' => $product['description'],
                            'bar_code' => $product['bar/qr_code'],
                            'unit' => $product['unit'],
                            'cat_id' => $catId,
                            'sub_cat_id' => $subCatId,
                            'child_cat_id' => $childCatId,
                            'weight' => $product['weight'],
                            'org_price' => $product['mrp'],
                            'price' => $product['offer_price'],
                            'total_stock' => $product['stock'],
                        ]);

                    } else {

                        $p = new Product;
                        $p->sku = $product['product_sku'];
                        $p->name = $product['product_name'];
                        $p->sub_title = $product['product_subtitle'];
                        $p->description = $product['description'];
                        $p->bar_code = $product['bar/qr_code'];
                        $p->unit = $product['unit'];
                        $p->cat_id = $catId;
                        $p->sub_cat_id = $subCatId;
                        $p->child_cat_id = $childCatId;
                        $p->weight = $product['weight'];
                        $p->org_price = $product['mrp'];
                        $p->price = $product['offer_price'];
                        $p->total_stock = $product['stock'];

                        $category = [];
                        if ($catId != null) {
                            array_push($category, ['id' => $catId, 'position' => 1,]);
                        }
                        if ($subCatId != null) {
                            array_push($category, ['id' => $subCatId, 'position' => 2,]);
                        }
                        if ($childCatId != null) {
                            array_push($category, ['id' => $childCatId, 'position' => 3,]);
                        }

                        $p->category_ids = json_encode($category);
                        $p->image =json_encode([]);


                        $choice_options = [];
                        $p->choice_options = json_encode($choice_options);
                        $variations = [];
                        $options = [];
                        $combinations = Helpers::combinations($options);
                        $p->variations = json_encode($variations);
                        $p->save();


                    }
                }
            }
        }
        
        return response()->json([], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
            'total_stock' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:1',
        ], [
            'name.required' => 'Product name is required!',
            'category_id.required' => 'category  is required!',
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', 'Discount can not be more or equal to the price!');
        }

        $p = Product::find($id);
        $images = json_decode($p->image);
        if (!empty($request->file('images'))) {
            foreach ($request->images as $img) {
                $image_data = Helpers::upload('product/', 'png', $img);
                array_push($images, $image_data);
            }

        }

        if (!count($images)) {
            $validator->getMessageBag()->add('images', 'Image can not be empty!');
        }

        $p->name = $request->name[array_search('en', $request->lang)];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
            $p->cat_id = $request->category_id;
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
            $p->sub_cat_id = $request->sub_category_id;
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
            $p->child_cat_id = $request->sub_sub_category_id;
        }

        $p->category_ids = json_encode($category);
        $p->description = $request->description[array_search('en', $request->lang)];

        $choice_options = [];
        if ($request->has('choice')) {
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_' . $no;
                if ($request[$str][0] == null) {
                    $validator->getMessageBag()->add('name', 'Attribute choice option values can not be null!');
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $item['name'] = 'choice_' . $no;
                $item['title'] = $request->choice[$key];
                $item['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', $request[$str])));
                array_push($choice_options, $item);
            }
        }
        $p->choice_options = json_encode($choice_options);
        $variations = [];
        $options = [];
        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('|', $request[$name]);
                array_push($options, explode(',', $my_str));
            }
        }

        //Generates the combinations of customer choice options
        $combinations = Helpers::combinations($options);
        $stock_count = 0;
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $k => $item) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        $str .= str_replace(' ', '', $item);
                    }
                }
                $item = [];
                $item['type'] = $str;
                $item['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
                $item['stock'] = abs($request['stock_' . str_replace('.', '_', $str)]);
                $item['barcode'] = $request['barcode_' . str_replace('.', '_', $str)];
                array_push($variations, $item);
                $stock_count += $item['stock'];
            }
        } else {
            $stock_count = (integer)$request['total_stock'];
        }

        if ((integer)$request['total_stock'] != $stock_count) {
            $validator->getMessageBag()->add('total_stock', 'Stock calculation mismatch!');
        }

        if ($validator->getMessageBag()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        if(isset($request->barcode) && $request->barcode != ""){
            $barcode = $request->barcode;
        } else {
            $barcode = NULL;
        }
        
        if(isset($request->sku) && $request->sku != ""){
            $sku = $request->sku;
        } else {
            $sku = NULL;
        }

        //combinations end
        $p->variations = json_encode($variations);
        $p->price = $request->price;
        $p->org_price = $request->org_price;
        $p->bar_code = $barcode;
        $p->sku = $sku;
        $p->capacity = $request->capacity;
        $p->unit = $request->unit;
        // $p->image = json_encode(array_merge(json_decode($p['image'], true), json_decode($image_data, true)));
        // $p->set_menu = $request->item_type;
        $p->image = json_encode($images);
        // $p->available_time_starts = $request->available_time_starts;
        // $p->available_time_ends = $request->available_time_ends;

        $p->tax = $request->tax_type == 'amount' ? $request->tax : $request->tax;
        $p->tax_type = $request->tax_type;

        $p->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $p->discount_type = $request->discount_type;
        $p->total_stock = $request->total_stock;

        $p->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $p->save();


        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                Translation::updateOrInsert(
                    ['translationable_type'  => 'App\Model\Product',
                        'translationable_id'    => $p->id,
                        'locale'                => $key,
                        'key'                   => 'name'],
                    ['value'                 => $request->name[$index]]
                );
            }
            if($request->description[$index] && $key != 'en')
            {
                Translation::updateOrInsert(
                    ['translationable_type'  => 'App\Model\Product',
                        'translationable_id'    => $p->id,
                        'locale'                => $key,
                        'key'                   => 'description'],
                    ['value'                 => $request->description[$index]]
                );
            }
        }

        return response()->json([], 200);
    }

    public function delete(Request $request)
    {
        $product = Product::find($request->id);
        foreach (json_decode($product['image'], true) as $img) {
            if (Storage::disk('public')->exists('product/' . $img)) {
                Storage::disk('public')->delete('product/' . $img);
            }
        }

        $product->delete();
        Toastr::success('Product removed!');
        return back();
    }

    
    public function remove_image($id, $name)
    {
        if (Storage::disk('public')->exists('product/' . $name)) {
            Storage::disk('public')->delete('product/' . $name);
        }

        $product = Product::find($id);
        $img_arr = [];
        foreach (json_decode($product['image'], true) as $img) {
            if (strcmp($img, $name) != 0) {
                array_push($img_arr, $img);
            }
        }

        Product::where(['id' => $id])->update([
            'image' => json_encode($img_arr),
        ]);

        Toastr::success('Image removed successfully!');
        return back();
    }

    public function bulk_import_index()
    {
        return view('admin-views.product.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            Toastr::error('You have uploaded a wrong format file, please upload the right file.');
            return back();
        }

        foreach ($collections as $key => $collection) {
            if ($collection['name'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: name ');
                return back();
            } elseif ($collection['description'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: description ');
                return back();
            } elseif (!is_numeric($collection['price'])) {
                Toastr::error('Price of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif (!is_numeric($collection['price'])) {
                Toastr::error('Price of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif (!is_numeric($collection['tax'])) {
                Toastr::error('Tax of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif ($collection['price'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: price ');
                return back();
            } elseif ($collection['category_id'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: category_id ');
                return back();
            } elseif ($collection['sub_category_id'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: sub_category_id ');
                return back();
            } elseif (!is_numeric($collection['discount'])) {
                Toastr::error('Discount of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif ($collection['discount'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: discount ');
                return back();
            } elseif ($collection['discount_type'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: discount_type ');
                return back();
            } elseif ($collection['tax_type'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: tax_type ');
                return back();
            } elseif ($collection['unit'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: unit ');
                return back();
            } elseif (!is_numeric($collection['total_stock'])) {
                Toastr::error('Total Stock of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif ($collection['total_stock'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: total_stock ');
                return back();
            } elseif (!is_numeric($collection['capacity'])) {
                Toastr::error('Capacity of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif ($collection['capacity'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: capacity ');
                return back();
            } elseif (!is_numeric($collection['daily_needs'])) {
                Toastr::error('Daily Needs of row ' . ($key + 2) . ' must be number');
                return back();
            } elseif ($collection['daily_needs'] === "") {
                Toastr::error('Please fill row:' . ($key + 2) . ' field: daily_needs ');
                return back();
            }

            $product = [
                'discount_type' => $collection['discount_type'],
                'discount' => $collection['discount'],
            ];
            if ($collection['price'] <= Helpers::discount_calculate($product, $collection['price'])) {
                Toastr::error('Discount can not be more or equal to the price in row '. ($key + 2));
                return back();
            }
        }
        $data = [];
        foreach ($collections as $collection) {

            array_push($data, [
                'name' => $collection['name'],
                'description' => $collection['description'],
                'image' => json_encode(['def.png']),
                'price' => $collection['price'],
                'variations' => json_encode([]),
                'tax' => $collection['tax'],
                'status' => 1,
                'attributes' => json_encode([]),
                'category_ids' => json_encode([['id' => $collection['category_id'], 'position' => 0], ['id' => $collection['sub_category_id'], 'position' => 1]]),
                'choice_options' => json_encode([]),
                'discount' => $collection['discount'],
                'discount_type' => $collection['discount_type'],
                'tax_type' => $collection['tax_type'],
                'unit' => $collection['unit'],
                'total_stock' => $collection['total_stock'],
                'capacity' => $collection['capacity'],
                'daily_needs' => $collection['daily_needs'],
            ]);
        }
        DB::table('products')->insert($data);
        Toastr::success(count($data) . ' - Products imported successfully!');
        return back();
    }

    public function bulk_export_data()
    {
        $products = Product::get();
        $storage = [];
        foreach($products as $item){
            $category_id = 0;
            $sub_category_id = 0;

            foreach(json_decode($item->category_ids, true) as $category)
            {
                if($category['position']==1)
                {
                    $category_id = $category['id'];
                }
                else if($category['position']==2)
                {
                    $sub_category_id = $category['id'];
                }
            }

            if (!isset($item['description'])) {
                $item['description'] = 'No description available';
            }

            if (!isset($item['capacity'])) {
                $item['capacity'] = 0;
            }

            $storage[] = [
                'name' => $item['name'],
                'description' => $item['description'],
                'price' => $item['price'],
                'tax' => $item['tax'],
                'category_id'=>$category_id,
                'sub_category_id'=>$sub_category_id,
                'discount'=>$item['discount'],
                'discount_type'=>$item['discount_type'],
                'tax_type'=>$item['tax_type'],
                'unit'=>$item['unit'],
                'total_stock'=>$item['total_stock'],
                'capacity'=>$item['capacity'],
                'daily_needs'=>$item['daily_needs'],
            ];

        }
        return (new FastExcel($storage))->download('products.xlsx');
    }
}
