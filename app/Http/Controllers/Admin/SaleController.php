<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Banner;
use App\Model\Sale;
use App\Model\Category;
use App\Model\Product;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{

    public function deal_delete(Request $request)
    {
        $deal = DB::table('smart_deals')->find($request->id);
        if (Storage::disk('public')->exists('smart_deals/' . $deal->slider_image)) {
            Storage::disk('public')->delete('smart_deals/' . $deal->slider_image);
        }
        
        DB::table('smart_deals')->delete($request->id);
        Toastr::success('Deal removed!');
        return back();
    }

    public function deal_update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
        ], [
            'title.required' => 'Title is required!',
        ]);
        
        $deal = DB::table('smart_deals')->find($id);

        $title = $request->title;
        if($request->status == "on"){
            $status = '1';
        } else {
            $status = '0';
        }

        if($request->has('slider_image')){
            $image_name =  Helpers::upload('smart_deals/', 'png', $request->file('slider_image'));
        } else {
            $image_name =  $deal->slider_image;
        }
        
        $dealStatus = DB::table('smart_deals')->where('id', $id)->update([
            'title' => $title,
            'slider_image' => $image_name,
            'status' => $status
        ]);
        
        Toastr::success('Smart deals updated successfully!');
        return back();
    }

    public function deal_edit($id)
    {
        $deal = DB::table('smart_deals')->find($id);
        return view('admin-views.sale.smart-deal-edit', compact('deal'));
    }

    function deal_store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ], [
            'title.required' => 'Title is required!',
        ]);

        if (!empty($request->file('slider_image'))) {
            $image_name =  Helpers::upload('smart_deals/', 'png', $request->file('slider_image'));
        } else {
            $image_name = 'def.png';
        }

        if($request->status == "on"){
            $status = 1;
        } else {
            $status = 0;
        }

        $smartArray = [
            'title' => $request->title,
            'slider_image' => $image_name,
            'status' => $status
        ];
        DB::table('smart_deals')->insert($smartArray);
        Toastr::success('Deal added successfully!');
        return back();
    }

    function smart_deals(Request $request){
        $smartDeals = DB::table('smart_deals')->orderBy('id', 'DESC')->paginate(Helpers::getPagination());
        return view('admin-views.sale.smart-deals',compact('smartDeals'));
    }

    function welcome_icons(){
        $welcomeIcons = DB::table('miscellaneous')->where(['setting_type' => 'welcome_icons'])->get();
        return view('admin-views.sale.welcome-icons',compact('welcomeIcons'));
    }

    function index()
    {
        $products = Product::orderBy('name')->get();
        $categories = Category::where(['parent_id'=>0])->orderBy('name')->get();
        return view('admin-views.sale.index', compact('products', 'categories'));
    }

    function list()
    {
        $banners=Sale::latest()->paginate(Helpers::getPagination());
        return view('admin-views.sale.list',compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'image' => 'required',
        ], [
            'title.required' => 'Title is required!',
        ]);

        $banner = new Banner;
        $banner->title = $request->title;
        if ($request['item_type'] == 'product') {
            $banner->product_id = $request->product_id;
        } elseif ($request['item_type'] == 'category') {
            $banner->category_id = $request->category_id;
        }
        $banner->image = Helpers::upload('banner/', 'png', $request->file('image'));
        $banner->save();
        Toastr::success('Banner added successfully!');
        return redirect('admin/banner/list');
    }


    public function storeicons(Request $request)
    {

        $request->validate([
            'title' => 'required',
            'sub_title' => 'required',
        ], [
            'title.required' => 'Title is required!',
            'sub_title.required' => 'Title is required!',
        ]);

        $wId = $request->form_id;
        $wTitle = $request->title;
        $wSubTitle = $request->sub_title;
        if(isset($request->status) && $request->status == 1){
            $wStatus = '1';
        } else {
            $wStatus = '0';
        }
        $wPriorty = $request->priorty;

        if($request->has('images')){
            //die("in right");
            $image = Helpers::upload('settings/', 'png', $request->file('images'));
        } else {
            //die("in else");
            $image = $request->old_image;
        }
        
        $welcomData = [
            'setting_title' => $wTitle,
            'setting_val_second' => $wSubTitle,
            'status' => $wStatus,
            'priorty' => $wPriorty,
            'setting_val_first' => $image
        ];

        DB::table('miscellaneous')->where('id', $wId)->update($welcomData);

        Toastr::success('Welcome Icons updated successfully!');
        return redirect('admin/sale/welcome-icons');
    }

    public function edit($id)
    {
        $products = Product::orderBy('name')->get();
        //$banner = Banner::find($id);
        $banner = Sale::find($id);
        $categories = Category::where(['parent_id'=>0])->orderBy('name')->get();
        $subCategories = Category::where(['parent_id'=>1])->orderBy('name')->get();
        $childCategories = Category::where(['parent_id'=>2])->orderBy('name')->get();
        return view('admin-views.sale.edit', compact('banner', 'products', 'categories', 'subCategories', 'childCategories'));
    }

    public function status(Request $request)
    {
        $banner = Banner::find($request->id);
        $banner->status = $request->status;
        $banner->save();
        Toastr::success('Banner status updated!');
        return back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
        ], [
            'title.required' => 'Title is required!',
        ]);

        $banner = Banner::find($id);
        $banner->title = $request->title;
        if ($request['item_type'] == 'product') {
            $banner->product_id = $request->product_id;
            $banner->category_id = null;
        } elseif ($request['item_type'] == 'category') {
            $banner->product_id = null;
            $banner->category_id = $request->category_id;
        }
        $banner->image = $request->has('image') ? Helpers::update('banner/', $banner->image, 'png', $request->file('image')) : $banner->image;
        $banner->save();
        Toastr::success('Banner updated successfully!');
        return redirect('admin/banner/list');
    }

    public function delete(Request $request)
    {
        $banner = Banner::find($request->id);
        if (Storage::disk('public')->exists('banner/' . $banner['image'])) {
            Storage::disk('public')->delete('banner/' . $banner['image']);
        }
        $banner->delete();
        Toastr::success('Banner removed!');
        return back();
    }
}
