@extends('layouts.admin.app')

@section('title','Update Faq')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">Update FAQ</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.faq.update',[$faqs['id']])}}" method="post">
                    @csrf
                  
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Question</label>
                                <input type="text" name="question" class="form-control" value="{{$faqs['question']}}" placeholder="Enter Question" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Answer</label>
                                <textarea name="answer" class="form-control" id="answer" placeholder="Add Answer" required>{{$faqs['answer']}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="1" <?php if($faqs['status'] == 1){ echo "selected"; } ?>>Active</option>
                                    <option value="0" <?php if($faqs['status'] == 0){ echo "selected"; } ?>>De-Active</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Priorty</label>
                                <input type="text" name="priorty" class="form-control" value="{{$faqs['priorty']}}" placeholder="Enter Priorty" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('update')}}</button>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')

@endpush
