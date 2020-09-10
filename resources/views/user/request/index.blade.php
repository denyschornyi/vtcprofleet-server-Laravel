@extends('user.layout.base')
@section('styles')
<style>
    /* The Modal (background) */
    .modal,
    .modal1 {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        /* padding-top: 100px; */
        /* Location of the box */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content,
    .modal-content1 {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }


    /* The Close Button */
    .close,
    .close1 {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close1:hover,
    .close:focus,
    .close1:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-icon-box {
        border: 2px solid #ccc;
        padding: 5px 10px;
        border-radius: 5px;
        background: 0 0;
    }

    #view-invoice {
        margin-top: 0 !important;
        top: 0 !important;
    }

    .ui.dimmer {
        background-color: rgba(0, 0, 0, 0.4) !important;
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

@section('title', 'Request History ')

@section('content')


<!-- Invoice Modal -->
@foreach($trips as $trip)
@if($trip->status == 'COMPLETED')
@include('admin.invoice.trip-invoice', ['trip' => $trip, 'admin' => $admin])
@endif
@endforeach
<!-- Invoice Modal -->

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            @if(Setting::get('demo_mode', 0) == 1)
            <div class="col-md-12" style="height:50px;color:red;">
                ** Demo Mode : @lang('admin.demomode')
            </div>
            @endif
            <h5 class="mb-1">Request History @if($from_date) From {{appDate($from_date)}} @endif @if($to_date) To {{appDate($to_date)}} @endif </h5>
            @include('common.date_filter', ['action' => 'requests.index'])
            @if(count($requests) != 0)
            <table class="table table-striped table-bordered dataTable" id="table-6">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('admin.request.Booking_ID')</th>
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
                        <td>{{ $request->id }}</td>
                        <td>{{ $request->booking_id }}</td>
                        @if($request->user->user_type === 'COMPANY')
                        <td>{{$request->user->company_name}}</td>
                        @else
                        <td>{{$request->user?$request->user->first_name:''}} {{$request->user?$request->user->last_name:''}}</td>
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
                            <span class="">{{appDateTime($request->created_at)}}</span>
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ $request->status }} <br /> {{ $request->cancel_reason }} </td>
                        <td>
                            @if($request->payment != "")
                            {{ currency($request->payment->total) }}
                            @else
                            N/A
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
                                    <a href="{{ route('requests.show', $request->id) }}" class="dropdown-item">
                                        <i class="fa fa-search"></i> More Details
                                    </a>
                                    <form action="{{ route('requests.destroy', $request->id) }}" method="POST">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button type="submit" class="dropdown-item" onclick="return confirm('Are you Sure?')">
                                            <i class = "fa fa-trash"></i> @lang('admin.delete')
                                        </button>

                                    </form>
                                </div>
                            </div>
                            @if($request->status === 'COMPLETED')
                            <div class="input-group-btn" style="left:5px; display:inline-block">
                                <a href="#" class="btn btn-info downloadpdf" idx="{{$request->id}}" style="background-color:#b531ba;border-color:#b531ba;"><i class=""></i>Invoice</a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>@lang('admin.request.Booking_ID')</th>
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
@endsection

@section('scripts')
<script>
    $('#table-6').on('click', '.downloadpdf', function(e) {
        var id = $(this).attr('idx');
        $('#view-invoice' + id).modal('show');
        $("#view-invoice" + id).css('top', 0);
        $("#view-invoice" + id).css('margin-top', 0);
        $("#view-invoice" + id).scrollTop(0);
        e.returnValue = false;
        return false;
    });

    $('a.download_pdf').on('click', function() {
        $('#formDownloadPDF' + $(this).attr('trip_id')).submit();
    });

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
