<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\TimeSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TimeSlotController extends Controller
{
    public function getTime_slot()
    {
        try {
            $timeSlots = TimeSlot::active()->get();
            if(count($timeSlots) > 0){
                $response['message'] = 'Time Slots list';
                $response['data'] = $timeSlots;
            } else {
                $response['message'] = 'Time Slots list';
                $response['data'] = [];
            }
            $response['status'] = 'success';
            
            return response()->json($response, 200);
            //return response()->json(TimeSlot::active()->get(), 200);
        } catch (\Exception $e) {
            $response['status'] = 'fail';
            $response['message'] = 'No Time Slots';
            $response['data'] = [];
            return response()->json($response, 200);
            //return response()->json([], 200);
        }
    }

    public function branch_list()
    {
        try {
            $braches = DB::table('branches')->where('status', 1)->get();
            //echo count($braches);
            if(count($braches) > 0){
                $response['status'] = 'success';
                $response['message'] = 'Branch list';
                $response['data'] = $braches;
            } else {
                $response['status'] = 'success';
                $response['message'] = 'No Branches Found.';
                $response['data'] = [];
            }
            
            return response()->json($response, 200);
            //return response()->json(TimeSlot::active()->get(), 200);
        } catch (\Exception $e) {
            $response['status'] = 'fail';
            $response['message'] = 'No Branches Found.';
            $response['data'] = [];
            return response()->json($response, 200);
            //return response()->json([], 200);
        }
    }

    public function static_pages($page_url)
    {
        try {

            $page = DB::table('pages')->where('url', $page_url)->where('status', 1)->get();
            
            if(count($page) > 0){
                $response['status'] = 'success';
                $response['message'] = 'Page Found';
                $response['data'] = $page;
            } else {
                $response['status'] = 'success';
                $response['message'] = 'Page Not Found';
                $response['data'] = [];
            }
            
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response['status'] = 'fail';
            $response['message'] = 'Page Not Found';
            $response['data'] = [];
            return response()->json($response, 200);
        }
    }

}
