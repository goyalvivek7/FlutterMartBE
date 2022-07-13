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
        $banner = Banner::find($id);
        $categories = Category::where(['parent_id'=>0])->orderBy('name')->get();
        return view('admin-views.banner.edit', compact('banner', 'products', 'categories'));
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
