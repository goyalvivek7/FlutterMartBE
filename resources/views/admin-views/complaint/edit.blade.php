@extends('layouts.admin.app')

@section('title','View Complaint')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">View Complaint</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.complaint.update',[$complaintMain['id']])}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <b>USER NAME: </b>
                                <a class="text-body text-capitalize" href="{{route('admin.customer.view',[$complaintMain['user_id']])}}">
                                    <?php if(isset($complaintMain->customer['f_name']) && $complaintMain->customer['f_name'] != NULL){
                                        echo $complaintMain->customer['f_name'];
                                    }
                                    if(isset($complaintMain->customer['l_name']) && $complaintMain->customer['l_name'] != NULL){
                                        echo " ".$complaintMain->customer['l_name'];
                                    } ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Issue</label>
                                <input disabled="disabled" type="text" name="issue" class="form-control" value="{{$complaintMain->complaint_issues['issue_title']}}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Comment</label>
                                <textarea disabled="disabled" name="comment" class="form-control" id="comment" placeholder="Add Answer" required>{{$complaintMain['comment']}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="pending" <?php if($complaintMain['status'] =="pending"){ echo "selected"; } ?>>Pending</option>
                                    <option value="processing" <?php if($complaintMain['status'] == "processing"){ echo "selected"; } ?>>Processing</option>
                                    <option value="resolved" <?php if($complaintMain['status'] == "resolved"){ echo "selected"; } ?>>Resolved</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Priorty</label>
                                <!-- <input type="number" min="1" max="5" name="priorty" class="form-control" value="{{$complaintMain['priorty']}}" placeholder="Enter Priorty" required> -->
                                <select name="priorty" id="priorty" class="form-control">
                                    <option value="1" <?php if($complaintMain['priorty'] =="5"){ echo "selected"; } ?>>Informational</option>
                                    <option value="2" <?php if($complaintMain['priorty'] =="4"){ echo "selected"; } ?>>Normal</option>
                                    <option value="3" <?php if($complaintMain['priorty'] =="3"){ echo "selected"; } ?>>High</option>
                                    <option value="4" <?php if($complaintMain['priorty'] =="2"){ echo "selected"; } ?>>Critical</option>
                                    <option value="5" <?php if($complaintMain['priorty'] =="1"){ echo "selected"; } ?>>Very Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <?php if(isset($complaintMain['attachments']) && $complaintMain['attachments'] != ""){ ?>
                                <img src="{{asset('storage/app/public/complaints')}}/{{$complaintMain['attachments']}}" />
                            <?php } ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('update')}}</button>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>
    <div class="content container-fluid">
        <?php if(isset($complaintReplies) && isset($complaintReplies[0]) && !empty($complaintReplies[0])){
            echo '<div class="row border-bottom">
                <div class="col-md-2 col-sm-12 p-3"><b>Reply From</b></div>
                <div class="col-md-2 col-sm-12 p-3"><b>Date</b></div>
                <div class="col-md-5 col-sm-12 p-3"><b>Reply</b></div>
                <div class="col-md-3 col-sm-12 p-3"><b>Attachment</b></div>
            </div>';
            foreach($complaintReplies as $complaintReply){ ?>
                <div class="row border-bottom">
                    <div class="col-md-2 col-sm-12 p-3">
                        <?php if(isset($complaintReply['user_id']) && $complaintReply['user_id'] != NULL && $complaintReply['user_id'] != ""){
                            echo "User";
                        } else {
                            echo "Admin";
                        } ?>
                    </div>
                    <div class="col-md-2 col-sm-12 p-3">
                        <?php echo $complaintReply['created_at']; ?>
                    </div>
                    <div class="col-md-5 col-sm-12 p-3">
                        <?php echo $complaintReply['comment']; ?>
                    </div>
                    <div class="col-md-3 col-sm-12 p-3">
                        <?php if(isset($complaintReply['attachments']) && $complaintReply['attachments'] != ""){ ?>
                            <img width="200px" src="{{asset('storage/app/public/complaints')}}/{{$complaintReply['attachments']}}" />
                        <?php } ?>
                    </div>
                </div>
                <?php //echo '<pre />'; print_r($complaintReply); ?>
            <?php }
        } ?>
    </div>

    
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">Add Complaint Reply</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.complaint.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="parent_id" value="<?php echo $complaintMain['id']; ?>" />
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Comment</label>
                                <textarea name="comment" class="form-control" id="comment" placeholder="Add Reply" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 from_part_1">
                            <label>{{ \App\CentralLogics\translate('image') }}</label>
                            <div class="custom-file">
                                <input type="file" name="attachments" id="customFileEg1" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                <label class="custom-file-label" for="customFileEg1">{{ \App\CentralLogics\translate('choose') }}
                                    {{ \App\CentralLogics\translate('file') }}</label>
                            </div>
                        </div>
                        <div class="col-6 from_part_2">
                            <div class="form-group">
                                <center>
                                    <img style="width: 30%;border: 1px solid; border-radius: 10px;" id="viewer"
                                            src="{{ asset('public/assets/admin/img/900x400/img1.jpg') }}" alt="image" />
                                </center>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('add')}}</button>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>


@endsection

@push('script_2')

<script>
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#viewer').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#customFileEg1").change(function () {
        readURL(this);
    });
</script>

@endpush
