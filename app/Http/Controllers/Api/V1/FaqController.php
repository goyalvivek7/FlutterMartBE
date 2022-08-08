<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\Faq;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    public function list(){
        try {
            $faqs = DB::table('faqs')->where('status', 1)->orderBy('priorty', 'ASC')->get();
            if(count($faqs) > 0){
                $response['status'] = 'success';
                $response['message'] = 'Faq list';
                $response['data'] = $faqs;
            } else {
                $response['status'] = 'success';
                $response['message'] = 'No Faq Found.';
                $response['data'] = [];
            }
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response['status'] = 'fail';
            $response['message'] = 'No Faq Found.';
            $response['data'] = [];
            return response()->json($response, 200);
        }
    }
}
