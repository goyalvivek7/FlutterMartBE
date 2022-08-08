<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ComplaintController extends Controller
{


    public function complaint_detail(Request $request){

        $validator = Validator::make($request->all(), [
            'complaint_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $complaintId = $request->complaint_id;
        
        //$complaints = DB::table('complaints')->where('id', $complaintId)->orderBy('id','DESC')->get();
        $complaints = DB::table('complaints')->select('complaints.*', 'complaint_issues.issue_title')->join('complaint_issues', 'complaint_issues.id', '=', 'complaints.issue_id')->where('complaints.id', $complaintId)->orderBy('complaints.id','DESC')->get();
      	$complaintReplies = DB::table('complaints')->join('users', 'users.id', '=', 'complaints.user_id')->where('complaints.parent_id', $complaintId)->select('complaints.*', 'users.f_name', 'users.l_name')->orderBy('complaints.id','DESC')->get();
        $response['status'] = 'success';
        $response['message'] = 'Fetched complaint issues successfully.';
        $response['data'] = $complaints;
        $response['replies'] = $complaintReplies;
        return response()->json($response, 200);
        
    }


    public function complaint_reply(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'issue_id' => 'required',
            'parent_id' => 'required',
            'comment' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $userId = $request->user_id;
        $issueId = $request->issue_id;
        $comment = $request->comment;
        $parentId = $request->parent_id;

        $attachmentsFileName = "";
        if ($request->hasFile('attachments')) {
            $attachmentsFile = $request->file('attachments');
            //$attachmentsFileName = time().'-'.$userId.'.'.$attachmentsFile->extension(); 
            //$attachmentsFile->move(public_path('complaints'), $attachmentsFileName);
            //$attachmentsFile->move(asset('/storage/app/public/complaints/'), $attachmentsFileName);
            $attachmentsFileName =  Helpers::upload('complaints/', 'png', $request->file('attachments'));
        }

        $complaint = Complaint::create([
            'user_id' => $userId,
            'issue_id' => $issueId,
            'comment' => $comment,
            'attachments' => $attachmentsFileName,
            'parent_id' => $parentId,
            'status' => 'pending'
        ]);

        $response['status'] = 'success';
        $response['state'] = 'register';
        $response['message'] = 'Reply register successfully';
        $response['data'][] = $complaint;

        return response()->json($response, 200);
    }

    public function complaint_list(Request $request){

        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $userId = $request->user_id;
        
        //$complaints = DB::table('complaints')->where('user_id', $userId)->where('parent_id', 0)->orderBy('id','DESC')->get();
      	$complaints = DB::table('complaints')->select('complaints.*', 'complaint_issues.issue_title')->join('complaint_issues', 'complaint_issues.id', '=', 'complaints.issue_id')->where('complaints.user_id', $userId)->where('complaints.parent_id', 0)->orderBy('complaints.id','DESC')->get();
        $response['status'] = 'success';
        $response['message'] = 'Fetched complaint issues successfully.';
        $response['data'] = $complaints;
        return response()->json($response, 200);
        
    }

    public function issues_list(Request $request){

        try {
            $issues = DB::table('complaint_issues')->where('status', 1)->get();
            $response['status'] = 'success';
            $response['message'] = 'Fetched complaint issues successfully.';
            $response['data'] = $issues;
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response['status'] = 'fail';
            $response['message'] = 'Getting Some Error';
            $response['data'] = [];
            return response()->json($response, 200);
        }
        
    }


    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'issue_id' => 'required',
            'comment' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $userId = $request->user_id;
        $issueId = $request->issue_id;
        $comment = $request->comment;

        $attachmentsFileName = "";
        if ($request->hasFile('attachments')) {
            $attachmentsFile = $request->file('attachments');
            $attachmentsFileName = time().'-'.$userId.'.'.$attachmentsFile->extension(); 
            //$attachmentsFile->move(public_path('complaints'), $attachmentsFileName);
            //$attachmentsFile->move(asset('/storage/app/public/complaints/'), $attachmentsFileName);
            $attachmentsFileName =  Helpers::upload('complaints/', 'png', $request->file('attachments'));
        }

        $complaint = Complaint::create([
            'user_id' => $userId,
            'issue_id' => $issueId,
            'comment' => $comment,
            'attachments' => $attachmentsFileName,
            'parent_id' => 0,
            'status' => 'pending'
        ]);

        $response['status'] = 'success';
        $response['state'] = 'register';
        $response['message'] = 'Complaint register successfully';
        $response['data'][] = $complaint;

        return response()->json($response, 200);
    }


}
