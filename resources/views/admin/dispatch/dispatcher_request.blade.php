@extends('dispatcher.layout.dispatcher_base')

@section("title") Dashboard - Ride History
@stop
@section("css")
    <link href="{{asset('app-assets/vendors/css/tables/datatable/datatables.min.css')}}" rel="stylesheet"
          type="text/css">
<style>
    @media (max-width: 575.98px) {
        div.dataTables_wrapper div.dataTables_filter input {
            width: 150px;
            height: 38px;
        }
    }
    @media (min-width: 576px) {
        div.dataTables_wrapper div.dataTables_filter input {
            width: 150px;
            display: inline-block;
            font-size: 15px;
            height: 38px;
        }
    }
    input.form-control.form-control-sm::-webkit-input-placeholder {
        font-size: 16px;
    }
    .datemenu {
        margin: 0px;
        margin-bottom: 21px;
        padding: 0px;
        float: left;
        width: 100%;
    }
    .datemenu span a {
        margin: 0 15px 0 0;
        padding: 0 15px 0 0;
        float: left;
        color: #3e70c9 !important;
        font-size: 15px;
        cursor: pointer;
        background: url(../../../line.jpg) no-repeat right 3px;
    }
</style>
@stop
@section('content')
    <div class="content-body">
        <section id="column-selectors">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1 col-md-6 pl-0">Ride History &nbsp;&nbsp;</h4>
                        </div>
                        @include('common.dispatcher_date_filter', ['action' => 'admin.requests.dispatcher'])
                        <div class="card-content">

                            <div class="card-body card-dashboard">
                                <div class="table-responsive">
                                    @if(count($requests) != 0)
                                        <table class="table table-striped dataex-html5-selectors" cellspacing="0"  width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="sorting_desc">#</th>
                                                    <th>@lang('admin.request.Booking_ID')</th>
                                                    <th>@lang('admin.request.Created_by')</th>
                                                    <th>@lang('admin.request.User_Name')</th>
                                                    <th>@lang('admin.request.Provider_Name')</th>
                                                    <th>@lang('admin.request.Date_Time')</th>
                                                    <th>@lang('admin.status')</th>
                                                    <th>@lang('admin.amount')</th>
                                                    <th>@lang('admin.request.Payment_Mode')</th>
                                                    <th>@lang('admin.request.Payment_Status')</th>
                                                    <th>@lang('admin.action')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($requests as $index => $request)
                                                <tr>
                                                    <td>{{ $index +1 }}</td>
                                                    <td>{{ $request->booking_id }}</td>
                                                    <td>{{ $request->created_by }}</td>
                                                    @if($request->user->user_type == 'COMPANY')
                                                        <td>{{$request->user->company_name}}</td>
                                                    @else
                                                        @if($request->user->company_name === '')
                                                            <td>{{$request->user?$request->user->first_name:''}} {{$request->user?$request->user->last_name:''}}</td>
                                                        @else
                                                            <td>{{$request->user->company_name}}</td>
                                                        @endif
                                                    @endif
                                                    <td>
                                                        @if($request->provider)
                                                            {{ $request->provider?$request->provider->first_name:'' }} {{ $request->provider?$request->provider->last_name:'' }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($request->created_at)
                                                            <span class="text-muted">{{appDateTime($request->created_at)}}</span>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ $request->status }} <br /> {{ $request->cancel_reason }} </td>
                                                    <td>
                                                        @if($request->payment != "")
                                                            {{ currency($request->payment->total) }}
                                                        @else
                                                           {{ currency( $request->total_price) }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $request->payment_mode }}</td>
                                                    <td>
                                                        @if($request->paid)
                                                            Paid
                                                        @else
                                                            Not Paid
                                                        @endif
                                                    </td>
                                                    <td width="200">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-primary waves-effect dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                                Action
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a href="{{ route('admin.requests.show', $request->id) }}" class="dropdown-item">
                                                                    <i class="fa fa-search"></i> More Details
                                                                </a>
                                                                <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST">
                                                                    {{ csrf_field() }}
                                                                    @if( Setting::get('demo_mode', 0) == 0)
                                                                        {{ method_field('DELETE') }}
                                                                        @can('ride-delete')
                                                                            <button type="submit" class="dropdown-item" onclick="return confirm('Are you Sure?')">
                                                                                <i class="fa fa-trash"></i> Delete
                                                                            </button>
                                                                        @endcan
                                                                    @endif
                                                                </form>
                                                            </div>
                                                        </div>
{{--                                                        @if($request->status == 'COMPLETED')--}}
{{--                                                            <div class="input-group-btn" style="left:5px; display:inline-block">--}}
{{--                                                                <a href="#" class="btn btn-info downloadpdf" idx="{{$request->id}}" style="background-color:#b531ba;border-color:#b531ba;"><i class=""></i>Invoice</a>--}}
{{--                                                            </div>--}}
{{--                                                        @endif--}}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <th class="sorting_desc">#</th>
                                                <th>@lang('admin.request.Booking_ID')</th>
                                                <th>@lang('admin.request.Created_by')</th>
                                                <th>@lang('admin.request.User_Name')</th>
                                                <th>@lang('admin.request.Provider_Name')</th>
                                                <th>@lang('admin.request.Date_Time')</th>
                                                <th>@lang('admin.status')</th>
                                                <th>@lang('admin.amount')</th>
                                                <th>@lang('admin.request.Payment_Mode')</th>
                                                <th>@lang('admin.request.Payment_Status')</th>
                                                <th>@lang('admin.action')</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    @else
                                        <h6 class="no-result">@lang('admin.custom.reds')</h6>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@stop

@section('script')
    <script src="{{asset('app-assets/vendors/js/tables/datatable/pdfmake.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/vfs_fonts.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/datatables.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/datatables.buttons.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/buttons.html5.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/buttons.print.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/buttons.bootstrap.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/jszip.min.js')}}"></script>
    <!-- END: Page Vendor JS -->

    <!-- BEGIN: Page JS-->
    <script src="{{asset('app-assets/js/scripts/datatables/datatable.js')}}"></script>
    <!-- END: Page JS-->
    <script>
        $(".showdate").on('click', function() {
            var ddattr = $(this).attr('id');
            // console.log(ddattr);
            if (ddattr == 'tday') {
                $("#from_date").val('{{$dates["today"]}}');
                $("#to_date").val('{{$dates["today"]}}');
                $('#date_filter').val('tday');
            } else if (ddattr == 'yday') {
                $("#from_date").val('{{$dates["yesterday"]}}');
                $("#to_date").val('{{$dates["yesterday"]}}');
                $('#date_filter').val('yday');
            } else if (ddattr == 'cweek') {
                $("#from_date").val('{{$dates["cur_week_start"]}}');
                $("#to_date").val('{{$dates["cur_week_end"]}}');
                $('#date_filter').val('cweek');
            } else if (ddattr == 'pweek') {
                $("#from_date").val('{{$dates["pre_week_start"]}}');
                $("#to_date").val('{{$dates["pre_week_end"]}}');
                $('#date_filter').val('pweek');
            } else if (ddattr == 'cmonth') {
                $("#from_date").val('{{$dates["cur_month_start"]}}');
                $("#to_date").val('{{$dates["cur_month_end"]}}');
                $('#date_filter').val('cmonth');
            } else if (ddattr == 'pmonth') {
                $("#from_date").val('{{$dates["pre_month_start"]}}');
                $("#to_date").val('{{$dates["pre_month_end"]}}');
                $('#date_filter').val('pmonth');
            } else if (ddattr == 'pyear') {
                $("#from_date").val('{{$dates["pre_year_start"]}}');
                $("#to_date").val('{{$dates["pre_year_end"]}}');
                $('#date_filter').val('pyear');
            } else if (ddattr == 'cyear') {
                $("#from_date").val('{{$dates["cur_year_start"]}}');
                $("#to_date").val('{{$dates["cur_year_end"]}}');
                $('#date_filter').val('cyear');
            } else {
                alert('invalid dates');
                $('#date_filter').val('');
            }
        });
    </script>
@endsection




