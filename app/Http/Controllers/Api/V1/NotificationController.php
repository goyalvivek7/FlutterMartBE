<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function get_notifications(){
        try {
            $notifications = Notification::active()->get();
            //echo '<pre />'; print_r($notifications);
            $response['status'] = 'success';
            if(count($notifications) > 0){
                $response['message'] = 'Notification list';
                $response['data'] = $notifications;
            } else {
                $response['message'] = 'Notification list';
                $response['data'] = [];
            }
            
            return response()->json($response, 200);
            //return response()->json(Notification::active()->get(), 200);
        } catch (\Exception $e) {
            $response['status'] = 'fail';
            $response['message'] = 'No Notification Found';
            $response['data'] = [];
            return response()->json($response, 200);
            //return response()->json([], 200);
        }
    }
}
