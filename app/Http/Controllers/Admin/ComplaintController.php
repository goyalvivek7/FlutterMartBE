<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Complaint;
use App\Model\ComplaintIssues;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\Helpers;

class ComplaintController extends Controller
{
    public function list()
    {
        $complaints = Complaint::with(['customer', 'complaint_issues'])->where(['parent_id' => 0])->latest()->get();
        //echo '<pre />'; print_r($complaints); die;
        return view('admin-views.complaint.index', compact('complaints'));
    }

    public function store(Request $request){
        $request->validate([
            'comment' => 'required'
        ]);
        if (!empty($request->file('attachments'))) {
            $image_name =  Helpers::upload('complaints/', 'png', $request->file('attachments'));
        } else {
            $image_name = NULL;
        }
        DB::table('complaints')->insert([
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
            'attachments' => $image_name
        ]);
        Toastr::success('Complaint reply added successfully!');
        return back();
    }

    public function edit($id)
    {
        $complaintMain = Complaint::with(['customer', 'complaint_issues'])->where(['id' => $id])->first();
        $complaintReplies = Complaint::with(['customer', 'complaint_issues'])->where(['parent_id' => $id])->get();
        return view('admin-views.complaint.edit', compact('complaintMain', 'complaintReplies'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required',
        ]);

        DB::table('complaints')->where(['id' => $id])->update([
            'status' => $request->status,
            'priorty' => $request->priorty,
        ]);

        Toastr::success('Complaint updated successfully!');
        return back();
    }

    public function status(Request $request)
    {
        $complaints = Complaint::find($request->id);
        $complaints->status = $request->status;
        $complaints->save();
        Toastr::success('Complaint status updated!');
        return back();
    }

    public function delete(Request $request)
    {
        $complaints = Complaint::find($request->id);
        $complaints->delete();
        Toastr::success('Complaint removed!');
        return back();
    }
}
