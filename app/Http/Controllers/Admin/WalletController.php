<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Order;
use App\User;
use App\Wallet;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function wallet_list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $wallets = DB::table('wallet')->join('users', 'users.id', '=', 'wallet.user_id')->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            }); 
            $query_param = ['search' => $request['search']];
        }else{
            //$customers = User::with(['orders']);
            $wallets = DB::table('wallet')->join('users', 'users.id', '=', 'wallet.user_id');
        }
        //$customers = $customers->latest()->paginate(Helpers::getPagination())->appends($query_param);
        //$walletUsers = $wallets->orderBy('wallet.id', 'DESC')->paginate(Helpers::getPagination())->appends($query_param);
        $walletUsers = $wallets->select('wallet.*', 'users.f_name', 'users.l_name', 'users.phone', 'users.email')->orderBy('wallet.id', 'DESC')->get();
        //echo '<pre />'; print_r($walletUsers);
        return view('admin-views.wallet.list', compact('walletUsers','search'));
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $customers=User::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('f_name', 'like', "%{$value}%")
                    ->orWhere('l_name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%")
                    ->orWhere('phone', 'like', "%{$value}%");
            }
        })->get();
        return response()->json([
            'view'=>view('admin-views.customer.partials._table',compact('customers'))->render()
        ]);
    }

    public function view($id)
    {
        $wallet = DB::table('wallet')->join('users', 'users.id', '=', 'wallet.user_id')->where('wallet.user_id', $id)->first();
        $walletHistories = DB::table('wallet_histories')->where('user_id', $id)->get();
        $customer = User::find($id);
        if (isset($customer)) {
            $orders = Order::latest()->where('user_id', $id)->where('order_status', '!=', 'created')->paginate(10);
            $allOrders = Order::latest()->where('user_id', $id)->where('order_status', '!=', 'created');
            return view('admin-views.wallet.wallet-view', compact('customer', 'orders', 'allOrders', 'wallet', 'walletHistories'));
        }
        Toastr::error('Customer not found!');
        return back();
    }
}
