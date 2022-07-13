<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    function index(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $categories=Category::where(['position'=>0])->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('name', 'like', "%{$value}%");
                        }
            });
            $query_param = ['search' => $request['search']];
        }else{
            $categories=Category::where(['position'=>0]);
        }
        
        $categories=$categories->orderBy('id', 'DESC')->paginate(Helpers::getPagination())->appends($query_param);
        //$categories=$categories->latest()->paginate(Helpers::getPagination())->appends($query_param);
        return view('admin-views.category.index',compact('categories','search'));
    }

    function sub_index(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $categories=Category::with(['parent'])->where(['position'=>1])
                    ->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('name', 'like', "%{$value}%");
                        }
            });
            $query_param = ['search' => $request['search']];
        }else{
            $categories=Category::with(['parent'])->where(['position'=>1]);
        }
        $categories=$categories->orderBy('id', 'DESC')->paginate(Helpers::getPagination())->appends($query_param);
        //$categories=$categories->latest()->paginate(Helpers::getPagination())->appends($query_param);
        return view('admin-views.category.sub-index',compact('categories' ,'search'));
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $categories=Category::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })->get();
        return response()->json([
            'view'=>view('admin-views.category.partials._table',compact('categories'))->render()
        ]);
    }

    function sub_sub_index()
    {
        return view('admin-views.category.sub-sub-index');
    }

    function sub_category_index()
    {
        return view('admin-views.category.index');
    }

    function sub_sub_category_index()
    {
        return view('admin-views.category.index');
    }

    function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Name is required!',
        ]);

        if (!empty($request->file('image'))) {
            $image_name =  Helpers::upload('category/', 'png', $request->file('image'));
        } else {
            $image_name = 'def.png';
        }

        if (!empty($request->file('cat_icon'))) {
            $icon_name =  Helpers::upload('category/', 'png', $request->file('cat_icon'));
        } else {
            $icon_name = 'def.png';
        }

        $category = new Category();
        $category->name = $request->name[0];
        $category->image = $image_name;
        $category->cat_icon = $icon_name;
        $category->parent_id = $request->parent_id == null ? 0 : $request->parent_id;
        $category->position = $request->position;
        $category->save();

        $data = [];
        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                array_push($data, Array(
                    'translationable_type'  => 'App\Model\Category',
                    'translationable_id'    => $category->id,
                    'locale'                => $key,
                    'key'                   => 'name',
                    'value'                 => $request->name[$index],
                ));
            }
        }
        if(count($data))
        {
            Translation::insert($data);
        }

        return back();
    }

    public function edit($id)
    {
        $category = category::withoutGlobalScopes()->with('translations')->find($id);
        if(isset($category) && !empty($category)){
            $catPosition = $category['position'];
            if($catPosition == 1){ $catFList=Category::where('position', 0)->get(); }
            if($catPosition == 2){ $catFList=Category::where('position', 1)->get(); }

            $catDropDown = [];
            if(isset($catFList) && !empty($catFList)){
                
                foreach($catFList as $catL){
                    $catId = $catL['id'];
                    $catName = $catL['name'];
                    $catDropDown[$catId] = $catName;
                }
            }
            
        } else {
            $catDropDown = [];
        }
        
        return view('admin-views.category.edit', compact('category', 'catDropDown'));
    }

    public function status(Request $request)
    {
        $category = category::find($request->id);
        $category->status = $request->status;
        $category->save();
        Toastr::success('Category status updated!');
        return back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Name is required!',
        ]);
        $category = category::find($id);
        if($request->parent_id && $request->parent_id != ""){
            $category->parent_id = $request->parent_id;
        }
        $category->name = $request->name[array_search('en', $request->lang)];
        $category->image = $request->has('image') ? Helpers::update('category/', $category->image, 'png', $request->file('image')) : $category->image;
        $category->cat_icon = $request->has('cat_icon') ? Helpers::update('category/', $category->cat_icon, 'png', $request->file('cat_icon')) : $category->cat_icon;
        $category->save();
        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                Translation::updateOrInsert(
                    ['translationable_type'  => 'App\Model\Category',
                        'translationable_id'    => $category->id,
                        'locale'                => $key,
                        'key'                   => 'name'],
                    ['value'                 => $request->name[$index]]
                );
            }
        }
        Toastr::success('Category updated successfully!');
        return back();
    }

    public function delete(Request $request)
    {
        $category = category::find($request->id);
        if (Storage::disk('public')->exists('category/' . $category['image'])) {
            Storage::disk('public')->delete('category/' . $category['image']);
        }
        if ($category->childes->count()==0){
            $category->delete();
            Toastr::success('Category removed!');
        }else{
            Toastr::warning('Remove subcategories first!');
        }
        return back();
    }

    function subsearch(Request $request){
        //$query_param = [];
        // echo 'post---'; print_r($request['search']); echo '<br />';
        // echo 'post!!!'; print_r($request->search); echo '<br />';
        // echo '<pre />'; print_r($request->input('search')); echo '<br />';
        //$categoryParam = $request['search'];
        $categoryParam = $request['search'];

        //echo "categoryParam---".$categoryParam.'<br />';
        if($categoryParam && $categoryParam != "")
        {
            //$key = explode(',', $categoryParam);
            //echo 'key---'; print_r($key); echo '<br />';
            $categoryArray = Category::where(['position'=>1, 'status'=>1])->where(function ($q) use ($key) {
                foreach ($categoryParam as $value) {
                    //echo "key---".$value.'<br />';
                    $q->orWhere('parent_id', "$value");
                }
            })->get();
        }else{
            $categoryArray=Category::where(['position'=>1, 'status'=>1])->get();
        }
        $subCatString = "";
        if($categoryArray && !empty($categoryArray)){
            $subCatString .= '<option value="">Select Sub Category</option>';
            foreach($categoryArray as $categories){
                $catId = $categories['id'];
                $catName = $categories['name'];
                $subCatString .= '<option value="'.$catId.'">'.$catName.'</option>';
            }
        }
        return $subCatString;
        //$categories=$categories->latest()->paginate(Helpers::getPagination())->appends($query_param);
        //return view('admin-views.category.index',compact('categories','search'));
    }


    function childsearch(Request $request){
        $categoryParam = $request['search'];

        if($categoryParam && $categoryParam != "")
        {
            $key = explode(',', $categoryParam);
            $categoryArray = Category::where(['position'=>2, 'status'=>1])->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            })->get();
        }else{
            $categoryArray=Category::where(['position'=>2, 'status'=>1])->get();
        }
        $subCatString = "";
        if($categoryArray && !empty($categoryArray)){
            $subCatString .= '<option value="">Select Sub Category</option>';
            foreach($categoryArray as $categories){
                $catId = $categories['id'];
                $catName = $categories['name'];
                $subCatString .= '<option value="'.$catId.'">'.$catName.'</option>';
            }
        }
        return $subCatString;
    }
}
