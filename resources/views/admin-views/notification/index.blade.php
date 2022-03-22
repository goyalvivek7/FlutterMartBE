@extends('layouts.admin.app')

@section('title','Add new notification')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-notifications"></i> {{\App\CentralLogics\translate('notification')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.notification.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('title')}}</label>
                        <input type="text" name="title" class="form-control" placeholder="New notification" required>
                    </div>
                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('description')}}</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>{{\App\CentralLogics\translate('image')}}</label><small style="color: red">* ( {{\App\CentralLogics\translate('ratio')}} 3:1 )</small>
                        <div class="custom-file">
                            <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            <label class="custom-file-label" for="customFileEg1">{{\App\CentralLogics\translate('choose')}} {{\App\CentralLogics\translate('file')}}</label>
                        </div>
                        <hr>
                        <center>
                            <img style="width: 30%;border: 1px solid; border-radius: 10px;" id="viewer"
                                 src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}" alt="image"/>
                        </center>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('send')}} {{\App\CentralLogics\translate('notification')}}</button>
                </form>
            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <hr>
                <div class="card">
                    <div class="card-header">
                       <div class="row">
                            <div class="col-md-4">
                                <h5>Notifications Table <span style="color: red;">({{ $notifications->total() }})</span></h5>
                            </div>
                            <div class="col-md-8 float-right" style="width: 30vw">
                                <form action="{{url()->current()}}" method="GET">
                                    <!-- Search -->
                                    <div class="input-group input-group-merge input-group-flush">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                            placeholder="Search by Title" value="{{$search}}" aria-label="Search"  required>
                                        <button type="submit" class="btn btn-primary">search</button>

                                    </div>
                                    <!-- End Search -->
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <tr>
                                <th>{{\App\CentralLogics\translate('#')}}</th>
                                <th style="width: 50%">{{\App\CentralLogics\translate('title')}}</th>
                                <th>{{\App\CentralLogics\translate('description')}}</th>
                                <th>{{\App\CentralLogics\translate('image')}}</th>
                                <th>{{\App\CentralLogics\translate('status')}}</th>
                                <th style="width: 10%">{{\App\CentralLogics\translate('action')}}</th>
                            </tr>

                            <tbody>
                            @foreach($notifications as $key=>$notification)
                                <tr>
                                    <td>{{$notifications->firstItem()+$key}}</td>
                                    <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{substr($notification['title'],0,25)}} {{strlen($notification['title'])>25?'...':''}}
                                    </span>
                                    </td>
                                    <td>
                                        {{substr($notification['description'],0,25)}} {{strlen($notification['description'])>25?'...':''}}
                                    </td>
                                    <td>
                                        @if($notification['image']!=null)
                                            <img style="height: 75px"
                                                 src="{{asset('storage/app/public/notification')}}/{{$notification['image']}}">
                                        @else
                                            <label class="badge badge-soft-warning">No {{\App\CentralLogics\translate('image')}}</label>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification['status']==1)
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.notification.status',[$notification['id'],0])}}'">
                                                <span class="legend-indicator bg-success"></span>{{\App\CentralLogics\translate('active')}}
                                            </div>
                                        @else
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.notification.status',[$notification['id'],1])}}'">
                                                <span class="legend-indicator bg-danger"></span>{{\App\CentralLogics\translate('disabled')}}
                                            </div>
                                        @endif
                                    </td>
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
                                                   href="{{route('admin.notification.edit',[$notification['id']])}}">{{\App\CentralLogics\translate('edit')}}</a>
                                                <a class="dropdown-item" href="javascript:"
                                                   onclick="$('#notification-{{$notification['id']}}').submit()">{{\App\CentralLogics\translate('delete')}}</a>
                                                <form
                                                    action="{{route('admin.notification.delete',[$notification['id']])}}"
                                                    method="post" id="notification-{{$notification['id']}}">
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

                        <hr>
                        <table>
                            <tfoot>
                            {!! $notifications->links() !!}
                            </tfoot>
                        </table>
                        @if(count($notifications)==0)
                            <div class="text-center p-4">
                                <img class="mb-3" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="Image Description" style="width: 7rem;">
                                <p class="mb-0">{{ \App\CentralLogics\translate('No_data_to_show')}}</p>
                            </div>
                        @endif
                    </div>
                </div>
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
