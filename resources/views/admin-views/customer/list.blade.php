@extends('layouts.admin.app')

@section('title','Customer List')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center mb-3">
                <div class="col-sm">
                    <h1 class="page-header-title">{{\App\CentralLogics\translate('customers')}}
                        <span class="badge badge-soft-dark ml-2">{{\App\User::count()}}</span>
                    </h1>
                </div>
            </div>
            <!-- End Row -->

            <!-- Nav Scroller -->
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev" style="display: none;">
              <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                <i class="tio-chevron-left"></i>
              </a>
            </span>

                <span class="hs-nav-scroller-arrow-next" style="display: none;">
              <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                <i class="tio-chevron-right"></i>
              </a>
            </span>

                <!-- Nav -->
                <ul class="nav nav-tabs page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">{{\App\CentralLogics\translate('customer')}} {{\App\CentralLogics\translate('list')}}</a>
                    </li>
                </ul>
                <!-- End Nav -->
            </div>
            <!-- End Nav Scroller -->
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div class="row justify-content-between align-items-center flex-grow-1">

                        <h5>Customer Table <span style="color: red;">({{ $customers->total() }})</span></h5>

                    <div class="col-lg-6 mb-3 mb-lg-0">

                        <form action="{{url()->current()}}" method="GET">
                            <!-- Search -->
                            <div class="input-group input-group-merge input-group-flush">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input  type="search" name="search" class="form-control"
                                       placeholder="Search by Name or Phone or email" value="{{ $search }}" aria-label="Search" required>
                                <button type="submit" class="btn btn-primary">search</button>

                            </div>
                            <!-- End Search -->
                        </form>
                    </div>

                </div>
                <!-- End Row -->
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       style="width: 100%">
                    <thead class="thead-light">
                    <tr>
                        <th class="">
                            {{\App\CentralLogics\translate('#')}}
                        </th>
                        <th class="table-column-pl-0">{{\App\CentralLogics\translate('name')}}</th>
                        <th>{{\App\CentralLogics\translate('email')}}</th>
                        <th>{{\App\CentralLogics\translate('phone')}}</th>
                        <th>{{\App\CentralLogics\translate('total')}} {{\App\CentralLogics\translate('order')}}</th>
                        <th>{{\App\CentralLogics\translate('actions')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($customers as $key=>$customer)
                        <tr class="">
                            <td class="">
                                {{$customers->firstItem()+$key}}
                            </td>
                            <td class="table-column-pl-0">
                                <a href="{{route('admin.customer.view',[$customer['id']])}}">
                                    {{$customer['f_name']." ".$customer['l_name']}}
                                </a>
                            </td>
                            <td>
                                {{$customer['email']}}
                            </td>
                            <td>
                               {{$customer['phone']}}
                            </td>
                            <td>
                                <label class="badge badge-soft-info">
                                    {{$customer->orders->count()}}
                                </label>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                            id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                        <i class="tio-settings"></i>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="{{route('admin.customer.view',[$customer['id']])}}">
                                            <i class="tio-visible"></i> {{\App\CentralLogics\translate('view')}}
                                        </a>
                                        {{--<a class="dropdown-item" target="" href="">
                                            <i class="tio-download"></i> Suspend
                                        </a>--}}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

            <!-- Footer -->
            <div class="card-footer">
                <!-- Pagination -->
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm-auto">
                        <div class="d-flex justify-content-center justify-content-sm-end">
                            <!-- Pagination -->
                            {!! $customers->links() !!}
                            {{--<nav id="datatablePagination" aria-label="Activity pagination"></nav>--}}
                        </div>
                    </div>
                </div>
                <!-- End Pagination -->
            </div>
            <!-- End Footer -->
        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script_2')
    <script>
        $('#search-form').on('submit', function () {
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.customer.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('.card-footer').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
