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
                $newTimeSlot = array();

                $twoHourTime = date("h:i A", strtotime('+3 hours'));
                $sTimeArray = explode(" ", $twoHourTime);
                $sTimeStr = $sTimeArray[0];
                $sTimeStrArray = explode(":", $sTimeStr);
                $sTimeHour = $sTimeStrArray[0];
                $sTimeMin = $sTimeStrArray[1];
                $sTimeAmPm = $sTimeArray[1];
                if($sTimeAmPm == "PM"){ $sTimeHour = $sTimeHour + 12; }
                $sTotalMinutes = ($sTimeHour*60)+$sTimeMin;

                foreach($timeSlots as $slot){
                    $totalMinutes = 0;
                    $timeArray = explode(" ", $slot['end_time']);
                    $timeStr = $timeArray[0];
                    $timeStrArray = explode(":", $timeStr);
                    $timeHour = $timeStrArray[0];
                    $timeMin = $timeStrArray[1];
                    $timeAmPm = $timeArray[1];
                    if($timeAmPm == "PM"){ $timeHour = $timeHour + 12; }
                    $totalMinutes = ($timeHour*60)+$timeMin;

                    //echo $sTotalMinutes.' - '.$totalMinutes.'@@@@<br />';
                    if($sTotalMinutes <= $totalMinutes){
                        $newTimeSlot[] = $slot;
                    }
                }
                $response['message'] = 'Time Slots list';
                $response['data'] = $newTimeSlot;

                //$response['message'] = 'Time Slots list';
                //$response['data'] = $timeSlots;
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

    public function all_pages()
    {
        try {

            $page = DB::table('pages')->where('status', 1)->get();
            
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


    public function delivery_options()
    {
        try {

            $options = DB::table('delivery_options')->where('status', 1)->get();
            
            if(count($options) > 0){
                $response['status'] = 'success';
                $response['message'] = 'Delivery Options Found';
                $response['data'] = $options;
            } else {
                $response['status'] = 'success';
                $response['message'] = 'Delivery Options Not Found';
                $response['data'] = [];
            }
            
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response['status'] = 'fail';
            $response['message'] = 'Delivery Options Not Found';
            $response['data'] = [];
            return response()->json($response, 200);
        }
    }

}
