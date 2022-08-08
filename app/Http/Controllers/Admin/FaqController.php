<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Faq;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    public function add_new()
    {
        $faqs = Faq::latest()->get();
        return view('admin-views.faq.index', compact('faqs'));
    }

    public function store(Request $request)
    {
        $request->validate([

            'question' => 'required',
            'answer'   => 'required',

        ]);

        DB::table('faqs')->insert([
            'question' => $request->question,
            'answer'   => $request->answer,
            'status' => $request->status,
            'priorty' => $request->priorty,
        ]);

        Toastr::success('Faq added successfully!');
        return back();
    }

    public function edit($id)
    {
        $faqs = Faq::where(['id' => $id])->first();
        return view('admin-views.faq.edit', compact('faqs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([

            'question' => 'required',
            'answer'   => 'required',

        ]);

        DB::table('faqs')->where(['id' => $id])->update([
            'question' => $request->question,
            'answer'   => $request->answer,
            'status' => $request->status,
            'priorty' => $request->priorty,
        ]);

        Toastr::success('Faq updated successfully!');
        return back();
    }

    public function status(Request $request)
    {
        $faqs = Faq::find($request->id);
        $faqs->status = $request->status;
        $faqs->save();
        Toastr::success('Faq status updated!');
        return back();
    }

    public function delete(Request $request)
    {
        $faqs = Faq::find($request->id);
        $faqs->delete();
        Toastr::success('Faq removed!');
        return back();
    }
}
