@extends('provider.layout.base')

@section('title', $page)
@section('styles')
    <style>
        .Dispute {
            background: #56b456;
            border: none;
            padding: 5px;
            color: white;
            margin-top: -5px;
            width: 90px;
            border-radius: 20px;
        }
        .modal-content {
            width: 100% !important;
        }
        .modal-header .close {
            margin-top: -36px;
        }
        .modal-footer {
            text-align: center;
        }
        .dispute-save{
            background: #b531ba;
            border-radius: 5px;
            width: 100px;
            color: white;
        }
        .dispute-save :hover {
            background: white;
            color: #b531ba;
        }
    </style>
@endsection
@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <h3>{{$page}}</h3>
                <div class="datemenu">
				<span>
					<a style="cursor:pointer" id="tday" class="showdate">@lang('admin.statement_date.Today')</a>
					<a style="cursor:pointer" id="yday" class="showdate">@lang('admin.statement_date.Yesterday')</a>
					<a style="cursor:pointer" id="cweek" class="showdate">@lang('admin.statement_date.Current_Week')</a>
					<a style="cursor:pointer" id="pweek" class="showdate">@lang('admin.statement_date.Previous_Week')</a>
					<a style="cursor:pointer" id="cmonth" class="showdate">@lang('admin.statement_date.Current_Month')</a>
					<a style="cursor:pointer" id="pmonth" class="showdate">@lang('admin.statement_date.Previous_Month')</a>
					<a style="cursor:pointer" id="cyear" class="showdate">@lang('admin.statement_date.Current_Year')</a>
					<a style="cursor:pointer" id="pyear" class="showdate">@lang('admin.statement_date.Previous_Year')</a>
				</span>
                </div>
                <div class="clearfix" style="margin-top: 15px;">
                    <form class="form-horizontal" action="{{route('provider.earnings')}}" method="GET" enctype="multipart/form-data" role="form" id="set-date">
                        <div class="form-group row col-md-5">
                            <label for="name" class="col-xs-4 col-form-label">Date From</label>
                            <div class="col-xs-8">

                                <input class="form-control" type="date" name="from_date" id="from_date" required placeholder="From Date">
                            </div>
                        </div>

                        <div class="form-group row col-md-5">
                            <label for="email" class="col-xs-4 col-form-label">Date To</label>
                            <div class="col-xs-8">
                                <input class="form-control" type="date" required name="to_date" id="to_date" placeholder="To Date">
                            </div>
                        </div>
                        <div class="form-group row col-md-2">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>


                </div>

                <div style="text-align: center;padding: 20px;color: blue;font-size: 24px;">

                </div>
                <div style="text-align: center;padding: 20px;color: blue;font-size: 24px;">
                    <p><strong>
                            <span>@lang('admin.dashboard.over_earning') : {{currency($revenue[0]->overall)}}</span>
                            <br>
                            <span>@lang('admin.dashboard.over_commission') : {{currency($revenue[0]->commission)}}</span>
                        </strong>
                    </p>
                </div>
                <div class="row">

                    <div class="col-lg-4 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                            <div class="t-icon right"><span class="bg-danger"></span><i class="ti-rocket"></i></div>
                            <div class="t-content">
                                <h6 class="text-uppercase mb-1">@lang('provider.partner.trips_completed')</h6>
                                <h1 class="mb-1">{{$provider[0]->accepted->count()}}</h1>
                                <i class="fa fa-caret-up text-success mr-0-5"></i><span>has been initiated by users</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                            <div class="t-icon right"><span class="bg-success"></span><i class="ti-bar-chart"></i></div>
                            <div class="t-content">
                                <h6 class="text-uppercase mb-1">@lang('admin.dashboard.Revenue')</h6>
                                <h1 class="mb-1">{{currency($fully_sum)}}</h1>
                                <i class="fa fa-caret-up text-success mr-0-5"></i><span>from {{$provider[0]->accepted->count()}} Rides</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-xs-12">
                        <div class="box box-block bg-white tile tile-1 mb-2">
                            <div class="t-icon right"><span class="bg-warning"></span><i class="ti-archive"></i></div>
                            <div class="t-content">
                                <h6 class="text-uppercase mb-1">@lang('admin.dashboard.cancel_rides')</h6>
                                <h1 class="mb-1">{{$provider[0]->cancelled->count()}}</h1>
                                <i class="fa fa-caret-down text-danger mr-0-5"></i><span>for @if($provider[0]->cancelled->count() == 0) 0.00 @else {{round($provider[0]->cancelled->count()/count($provider[0]->accepted->count()),2)}}% @endif Rides</span>
                            </div>
                        </div>
                    </div>

                    <div class="row row-md mb-2" style="padding: 15px;">
                        <div class="col-md-12">
                            <div class="box bg-white">
                                <div class="box-block clearfix">
                                    <h5 class="float-xs-left">{{$listname}}</h5>
                                    <div class="float-xs-right">
                                    </div>
                                </div>
                                <table class="table table-striped table-bordered dataTable" id='table-8'>
                                    <thead>
                                    <tr>
                                        <th>@lang('provider.partner.pickup')</th>
                                        <th>@lang('provider.partner.booking_id')</th>
                                        <th>@lang('provider.partner.vehicle')</th>
                                        <th>@lang('provider.partner.duration')</th>
                                        <th>@lang('provider.partner.status')</th>
                                        <th>@lang('provider.partner.distance(km)')</th>
                                    <!-- <th>@lang('provider.partner.invoice_amount')</th>
                                        <th>@lang('provider.partner.cash_collected')</th> -->
                                        <th>@lang('provider.partner.total_earnings')</th>
                                        <th>@lang('provider.partner.action')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
		                            <?php $fully_sum = 0; ?>
                                    @foreach($fully as $each)
                                        <tr>
                                            <td>{{date('Y D, M d - H:i A',strtotime($each->created_at))}}</td>
                                            <td>{{ $each->booking_id }}</td>
                                            <td>
                                                @if($each->service_type)
                                                    {{$each->service_type->name}}
                                                @endif
                                            </td>
                                            <td>
                                                @if($each->finished_at != null && $each->started_at != null)
						                            <?php

						                            $StartTime = \Carbon\Carbon::parse( $each->started_at );
						                            $EndTime = \Carbon\Carbon::parse( $each->finished_at );
						                            echo $StartTime->diffInHours( $EndTime )
						                                 . " "; ?>@lang('provider.hours')
						                            <?php
						                            echo " " . $StartTime->diffInMinutes( $EndTime ) . " ";
						                            ?>
                                                    @lang('provider.minutes')
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($each->status == "COMPLETED")
                                                    <span class="tag tag-success">{{$each->status}}</span>
                                                @elseif($each->status == "CANCELLED")
                                                    <span class="tag tag-danger">{{$each->status}}</span>
                                                    <br/> {{ $each->cancel_reason }}
                                                @else
                                                    <span class="tag tag-info">{{$each->status}}</span>
                                            @endif
                                            <td>{{$each->distance}}{{$each->unit}}</td>
                                        <!-- <td>
                                            @if($each->payment != "")
                                                {{currency($each->payment->total)}}
                                            @else
                                                {{currency(0.00)}}
                                            @endif
                                                </td>
                                                <td>
                                            @if($each->payment != "")
                                                <?php $each_sum = 0;
                                                $each_sum = $each->payment->provider_pay;
                                                $fully_sum += $each_sum;
                                                ?>
                                                {{currency($each_sum)}}
                                            @else
                                                -
                                            @endif
                                                </td> -->
                                            <td>@if($each->status=='CANCELLED')- @else{{currency($fully_sum)}}@endif</td>
                                            <td>
                                                @if(in_array($each->id, $dispute_id))
                                                    <button class="Dispute" data-toggle="modal" data-target = "#distpute-{{$each->id}}">Dispute</button>
                                                @else
                                                    <button data-id="{{$each->id}}" class="Dispute" data-toggle="modal" data-target="#myModal">Dispute
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="myModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Dispute</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/provider/dispute_store" method ="post">
                {{ csrf_field() }}
                    <!-- Modal body -->
                    <div class="modal-body">
                        <input type="hidden" name="request_id" id="request_id" />
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-xs-3" style="padding-top: 6px;">Reason</div>
                            <div class="col-xs-9">
                                <select class="form-control" name="dispute_name">
                                    @foreach($dispute_reason as $key=>$val)
                                        <option value="{{$val}}">{{$val}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-3" style="padding-top: 6px;margin-top: 15px;">Title</div>
                            <div class="col-xs-9">
                                <input type="text" class="form-control" style="margin-top: 15px;" id="disputeTitle" name="dispute_title" placeholder="Enter title">
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-secondary dispute-save">Send </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @foreach($dispute_content as $key=>$val)
        <div class="modal fade" id="distpute-{{$val->request_id}}">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Dispute</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="request_id" id="request_id" />
                        <div class="row">
                            <div class="col-xs-12" style="text-align: center;font-size: bold;">
                                {{$val->dispute_title}}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-3" style="padding-top: 6px;margin-top: 15px;">Reason</div>
                            <div class="col-xs-9" style="padding-top: 6px;margin-top: 15px;">
                                <span>{{$val->dispute_name}}</span>
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection

@section('scripts')

    <script type="text/javascript">

        $(document).on("click", ".Dispute", function () {
            var provider_id = $(this).data('id');
            $("#request_id").val(provider_id);
        });

        $(".showdate").on('click', function () {
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

        $('#table-8').DataTable({
            responsive: true,
            paging: true,
            info: false,
            aaSorting: [[0, "desc"]],
            dom: 'Bfrtip',
            buttons: [{
                extend: 'csv',
                text: 'CSV',
                charset: 'utf-8',
                extension: '.csv',
                fieldSeparator: ';',
                fieldBoundary: '',
                bom: true,
                exportOptions: {
                    columns: ':not(:eq(3))'
                }
            },
                {
                    extend: 'pdfHtml5',
                    action: function (e, dt, node, config) {
                        downloadPdf()
                    }
                }
            ]
        });

        function downloadPdf() {
            // $("form#set-date :input").each(function() {
            // 	var input = $(this);
            // });
            $("form#pdf-download").submit();
        }
    </script>
@endsection
