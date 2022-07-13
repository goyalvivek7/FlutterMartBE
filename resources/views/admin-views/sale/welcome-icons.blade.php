@extends('layouts.admin.app')

@section('title','Welcome Icons')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <?php if(isset($welcomeIcons) && !empty($welcomeIcons)){
            $i=1;
            foreach($welcomeIcons as $welcomeIcon){ ?>
                
                <div class="row gx-2 gx-lg-3">
                    <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                        <form action="{{route('admin.sale.storeicons')}}" method="post" enctype="multipart/form-data">
                        @csrf
                            <input type="hidden" name="form_id" value="<?php echo $welcomeIcon->id; ?>" />
                            <input type="hidden" name="old_image" value="<?php echo $welcomeIcon->setting_val_first; ?>" />
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">Title</label>
                                        <input type="text" name="title" class="form-control" placeholder="Title" value="<?php echo $welcomeIcon->setting_title; ?>" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">Sub Title</label>
                                        <input type="text" name="sub_title" class="form-control" placeholder="Sub Title" value="<?php echo $welcomeIcon->setting_val_second; ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Welcome Icon</label><small
                                            style="color: red">* ( {{\App\CentralLogics\translate('ratio')}} 1:1 )</small>
                                        <div>
                                            <div class="row coba" id="coba<?php echo $i; ?>"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <?php if(isset($welcomeIcon->setting_val_first) && $welcomeIcon->setting_val_first != ""){ ?>
                                        <img style="height: 50px;width: 100%" src="{{asset('storage/app/public/settings')}}/<?php echo $welcomeIcon->setting_val_first; ?>">
                                    <?php } ?>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <input type="checkbox" name="status" value="<?php if(isset($welcomeIcon->status) && $welcomeIcon->status == 1){ echo 1; } else {echo 0; } ?>" <?php if(isset($welcomeIcon->status) && $welcomeIcon->status == 1){ echo "checked"; } ?> />
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>Priorty</label>
                                        <input type="text" name="priorty" value="<?php if(isset($welcomeIcon->priorty) && $welcomeIcon->priorty != ""){ echo $welcomeIcon->priorty; } ?>" />
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('submit')}}</button>
                        </form>
                    </div>
                </div>
                <hr>
            <?php $i++;
            }
        } ?>
        
    </div>

@endsection

@push('script_2')
<script src="{{asset('public/assets/admin/js/spartan-multi-image-picker.js')}}"></script>
<script type="text/javascript">
    $(function () {
        $(".coba").spartanMultiImagePicker({
            fieldName: 'images',
            maxCount: 4,
            rowHeight: '215px',
            groupClassName: 'col-3',
            maxFileSize: '',
            placeholderImage: {
                image: '{{asset('public/assets/admin/img/400x400/img2.jpg')}}',
                width: '100%'
            },
            dropFileLabel: "Drop Here",
            onAddRow: function (index, file) {

            },
            onRenderedPreview: function (index) {

            },
            onRemoveRow: function (index) {

            },
            onExtensionErr: function (index, file) {
                toastr.error('Please only input png or jpg type file', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onSizeErr: function (index, file) {
                toastr.error('File size too big', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        });
    });
</script>
@endpush