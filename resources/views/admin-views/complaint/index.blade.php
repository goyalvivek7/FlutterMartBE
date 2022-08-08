@extends('layouts.admin.app')

@section('title','Complaint List')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">Complaint List</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">

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
                                <th width="5%">{{\App\CentralLogics\translate('#')}}</th>
                                <th width="20%">Type</th>
                                <th width="15%">User</th>
                                <th width="30%">Issue</th>
                                <th width="10%">Priorty</th>
                                <th width="10%">Status</th>
                                <th width="10%">{{\App\CentralLogics\translate('action')}}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($complaints as $key=>$complaint)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{$complaint->complaint_issues['issue_title']}}</td>
                                    <td>
                                        <a class="text-body text-capitalize" href="{{route('admin.customer.view',[$complaint['user_id']])}}">
                                            <?php if(isset($complaint->customer['f_name']) && $complaint->customer['f_name'] != NULL){
                                                echo $complaint->customer['f_name'];
                                            }
                                            if(isset($complaint->customer['l_name']) && $complaint->customer['l_name'] != NULL){
                                                echo " ".$complaint->customer['l_name'];
                                            } ?>
                                        </a>
                                    </td>
                                    <td>{{$complaint['comment']}}</td>
                                    <td>
                                        <?php 
                                        if(isset($complaint['priorty']) && $complaint['priorty'] == 1){
                                            echo '<span style="color:#cb0032; font-weight: bold;">Very Critical</span>';
                                        } elseif(isset($complaint['priorty']) && $complaint['priorty'] == 2){
                                            echo '<span style="color:#fe6700; font-weight: bold;">Critical</span>';
                                        } elseif(isset($complaint['priorty']) && $complaint['priorty'] == 3){
                                            echo '<span style="color:#fde101; font-weight: bold;">High</span>';
                                        } elseif(isset($complaint['priorty']) && $complaint['priorty'] == 4){
                                            echo '<span style="color:#3566cd; font-weight: bold;">Normal</span>';
                                        } elseif(isset($complaint['priorty']) && $complaint['priorty'] == 5){
                                            echo '<span style="color:#009a66; font-weight: bold;">Informational</span>';
                                        } else {
                                            echo '<span style="font-weight: bold;">Not Assigned</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        @if($complaint['status']=='pending')
                                            <span class="legend-indicator bg-danger"></span>Pending
                                        @elseif($complaint['status']=='processing')
                                            <span class="legend-indicator bg-info"></span>Processing
                                        @elseif($complaint['status']=='resolved')
                                            <span class="legend-indicator bg-success"></span>Resolved
                                        @endif
                                    </td>
                                    <!-- <td>
                                        @if($complaint['status']=='pending')
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.complaint.status',[$complaint['id'],'pending'])}}'">
                                                <span class="legend-indicator bg-danger"></span>Pending
                                            </div>
                                        @elseif($complaint['status']=='processing')
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.complaint.status',[$complaint['id'],'processing'])}}'">
                                                <span class="legend-indicator bg-info"></span>Processing
                                            </div>
                                        @elseif($complaint['status']=='resolved')
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.complaint.status',[$complaint['id'],'resolved'])}}'">
                                                <span class="legend-indicator bg-success"></span>Resolved
                                            </div>
                                        @endif
                                    </td> -->
                                    
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
                                                   href="{{route('admin.complaint.edit',[$complaint['id']])}}">{{\App\CentralLogics\translate('view')}}</a>
                                                <!-- <a class="dropdown-item" href="javascript:"
                                                onclick="form_alert('complaint-{{$complaint['id']}}','Want to delete this complaint')">{{\App\CentralLogics\translate('delete')}}  </a>
                                                <form action="{{route('admin.complaint.delete',[$complaint['id']])}}"
                                                      method="post" id="complaint-{{$complaint['id']}}">
                                                    @csrf @method('delete')
                                                </form> -->
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