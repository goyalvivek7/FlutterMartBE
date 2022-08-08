@extends('layouts.admin.app')

@section('title','Add new Faq')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">FAQ</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.faq.store')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Question</label>
                                <input type="text" name="question" class="form-control" value="" placeholder="Enter Question" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Answer</label>
                                <textarea name="answer" class="form-control" id="answer" placeholder="Add Answer" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">De-Active</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Priorty</label>
                                <input type="text" name="priorty" class="form-control" value="" placeholder="Enter Priorty" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('submit')}}</button>
                </form>
            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <hr>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-header-title"></h5>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th>{{\App\CentralLogics\translate('#')}}</th>
                                <th class="text-center">Question</th>
                                <th class="text-center">Status</th>
                                <th>Priorty</th>
                                <th style="width: 100px">{{\App\CentralLogics\translate('action')}}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($faqs as $key=>$faq)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{$faq['question']}}</td>
                                    <td>
                                        @if($faq['status']==1)
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.faq.status',[$faq['id'],0])}}'">
                                                <span class="legend-indicator bg-success"></span>{{\App\CentralLogics\translate('active')}}
                                            </div>
                                        @else
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.faq.status',[$faq['id'],1])}}'">
                                                <span class="legend-indicator bg-danger"></span>{{\App\CentralLogics\translate('disabled')}}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{$faq['priorty']}}</td>
                                    <td>
                                        <!-- Dropdown -->
                                        <div class="dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                <i class="tio-settings"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item"
                                                   href="{{route('admin.faq.update',[$faq['id']])}}">{{\App\CentralLogics\translate('edit')}}</a>
                                                <a class="dropdown-item" href="javascript:"
                                                onclick="form_alert('faq-{{$faq['id']}}','Want to delete this faq')">{{\App\CentralLogics\translate('delete')}}  </a>
                                                <form action="{{route('admin.faq.delete',[$faq['id']])}}"
                                                      method="post" id="faq-{{$faq['id']}}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </div>
                                        <!-- End Dropdown -->
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')

@endpush