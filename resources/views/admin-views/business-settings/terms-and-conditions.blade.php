@extends('layouts.admin.app')

@section('title','Terms and Conditions')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header_ pb-4">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{\App\CentralLogics\translate('terms_and_condition')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.terms-and-conditions')}}" method="post" id="tnc-form">
                    @csrf
                    <div class="form-group">
                        <label for="terms_title">Title</label>
                        <input class="ckeditor form-control" name="terms_title" value="{!! $data->title !!}" />
                    </div>
                    <div class="form-group">
                        <label for="tnc">Description</label>
                        <textarea class="ckeditor form-control" name="tnc">{!! $data->description !!}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('submit')}}</button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="//cdn.ckeditor.com/4.14.1/standard/ckeditor.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.ckeditor').ckeditor();
        });
    </script>
@endpush
