@extends('layouts.admin.app')

@section('title','Update Coupon')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-edit"></i> {{\App\CentralLogics\translate('coupon')}} {{\App\CentralLogics\translate('update')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.coupon.update',[$coupon['id']])}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('title')}}</label>
                                <input type="text" name="title" value="{{$coupon['title']}}" class="form-control"
                                       placeholder="New coupon" required>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('coupon')}} {{\App\CentralLogics\translate('type')}}</label>
                                <select name="coupon_type" class="form-control" onchange="coupon_type_change(this.value)">
                                    <option value="default" {{$coupon['coupon_type']=='default'?'selected':''}}>
                                        {{\App\CentralLogics\translate('default')}}
                                    </option>
<!--                                    <option value="first_order" {{$coupon['coupon_type']=='first_order'?'selected':''}}>
                                        {{\App\CentralLogics\translate('first')}} {{\App\CentralLogics\translate('order')}}
                                    </option>-->
                                </select>
                            </div>
                        </div>
                        <div class="col-4" id="limit-for-user" style="display: {{$coupon['coupon_type']=='first_order'?'none':'block'}}">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('limit')}} {{\App\CentralLogics\translate('for')}} {{\App\CentralLogics\translate('same')}} {{\App\CentralLogics\translate('user')}}</label>
                                <input type="number" name="limit" value="{{$coupon['limit']}}" class="form-control"
                                       placeholder="EX: 10">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('code')}}</label>
                                <input type="text" name="code" class="form-control" value="{{$coupon['code']}}"
                                       placeholder="{{\Illuminate\Support\Str::random(8)}}" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="">{{\App\CentralLogics\translate('start')}} {{\App\CentralLogics\translate('date')}}</label>
                                <input type="text" name="start_date" class="js-flatpickr form-control flatpickr-custom" placeholder="Select dates" value="{{date('Y/m/d',strtotime($coupon['start_date']))}}"
                                       data-hs-flatpickr-options='{
                                     "dateFormat": "Y/m/d"
                                   }'>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="">{{\App\CentralLogics\translate('expire')}} {{\App\CentralLogics\translate('date')}}</label>
                                <input type="text" name="expire_date" class="js-flatpickr form-control flatpickr-custom" placeholder="Select dates" value="{{date('Y/m/d',strtotime($coupon['expire_date']))}}"
                                       data-hs-flatpickr-options='{
                                     "dateFormat": "Y/m/d"
                                   }'>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('min')}} {{\App\CentralLogics\translate('purchase')}}</label>
                                <input type="number" name="min_purchase" step="0.01" value="{{$coupon['min_purchase']}}"
                                       min="0" max="100000" class="form-control"
                                       placeholder="100">
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('max')}} {{\App\CentralLogics\translate('discount')}}</label>
                                <input type="number" min="0" max="1000000" step="0.01"
                                       value="{{$coupon['max_discount']}}" name="max_discount" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('discount')}}</label>
                                <input type="number" min="1" max="10000" step="0.01" value="{{$coupon['discount']}}"
                                       name="discount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('discount')}} {{\App\CentralLogics\translate('type')}}</label>
                                <select name="discount_type" class="form-control">
                                    <option value="amount" {{$coupon['discount_type']=='amount'?'selected':''}}>{{\App\CentralLogics\translate('amount')}}
                                    </option>
                                    <option value="percent" {{$coupon['discount_type']=='percent'?'selected':''}}>
                                        {{\App\CentralLogics\translate('percent')}}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                       <div class="col-md-6 col-6">
                           <div class="form-group">
                               <label class="input-label" for="exampleFormControlInput1">Coupon Text</label>
                               <input type="text" name="valid_text" value="{{$coupon['valid_text']}}" class="form-control" placeholder="Coupon Text" required>
                           </div>
                       </div>
                       <div class="col-md-6 col-6">
                           <div class="form-group">
                               <label class="input-label" for="exampleFormControlInput1">Coupon Details</label>
                               <textarea name="valid_detail" class="form-control" placeholder="Coupon Detail">{{$coupon['valid_detail']}}</textarea>
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
    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF FLATPICKR
            // =======================================================
            $('.js-flatpickr').each(function () {
                $.HSCore.components.HSFlatpickr.init($(this));
            });
        });

        function coupon_type_change(order_type) {
            if(order_type=='first_order'){
                $('#limit-for-user').hide();
            }else{
                $('#limit-for-user').show();
            }
        }
    </script>
@endpush
