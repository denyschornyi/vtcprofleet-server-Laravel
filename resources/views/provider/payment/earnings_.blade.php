<link rel="stylesheet" href="{{ asset('main/vendor/DataTables/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{ asset('main/vendor/DataTables/Responsive/css/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{ asset('main/vendor/DataTables/Buttons/css/buttons.dataTables.min.css')}}">
<link rel="stylesheet" href="{{ asset('main/vendor/DataTables/Buttons/css/buttons.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{ asset('main/assets/css/style_pagination.css')}}">
<link rel="stylesheet" href="{{asset('main/vendor/themify-icons/themify-icons.css')}}">
<style>
    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0, 0, 0); /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
    }

    .modal1 {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0, 0, 0); /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content, .modal-content1 {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }


    /* The Close Button */
    .close, .close1 {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover, .close1:hover,
    .close:focus, .close1:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .btn {
        border-color: lightgray !important;
    }

    .box-block {
        padding: 1.25rem;
    }

    .box {
        position: relative;
        display: block;
        margin-bottom: 0.75rem;
        border: 1px solid rgba(0, 0, 0, 0.125);
        overflow: hidden;
    }

    .bg-white {
        background-color: #fff !important;
    }
    .bg-success {
        color: #fff !important;
        background-color: #43b968 !important;
    }
    .bg-warning {
        color: #fff !important;
        background-color: #f59345 !important;
    }

    .py-1 {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }

    tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, .05);
    }

    .earning-element {
        border-bottom: 0px !important;
        padding: 20px 0;
    }

    .earning-price{
        display: none;
    }
    .earning-txt{
        display: none;
    }
    .tile-1 .t-icon.right {
        right: 0;
    }
    .tile-1 .t-icon {
        position: absolute;
        top: 0;
        width: 60px;
        height: 60px;
        line-height: 60px;
        text-align: center;
    }
    .tile-1 .t-icon.right span {
        right: -60px;
    }
    .tile-1 .t-icon span {
        position: absolute;
        z-index: 8;
        top: -60px;
        width: 120px;
        height: 120px;
        line-height: 120px;
        border-radius: 50%;
    }
    .bg-danger {
        color: #fff !important;
        background-color: #f44236 !important;
    }
    .tile-1 .t-icon.right i {
        position: absolute;
        padding-left: 0px;
        padding-top: 12px;
        text-align: center;
        color: rgba(255, 255, 255, 0.9);
    }
    .tile-1 .t-icon i {
        position: relative;
        z-index: 9;
        font-size: 1.75rem;
        color: #fff;
    }
    .t-content h6{
        font-weight: normal;
        color: #999;
    }
    .h-150{
        height: 150px;
    }
    .mb-1{
        margin-bottom: 1rem;
    }
    .mr-0-5 {
        margin-right: 0.5rem !important;
    }
    .text-success {
        color: #43b968 !important;
    }
    .t-content h1 {
        font-family: 'Roboto', sans-serif;
        font-weight: normal;
    }
    .ti-bar-chart:before{
        margin-left: -5px;
    }
    .ti-rocket:before{
        margin-left: -5px;
    }
    .ti-archive:before{
        margin-left: -5px;
    }
    .col-form-label{
        margin-top: 10px;
    }
</style>

@extends('provider.layout.app')

@section('content')
	<?php use Carbon\Carbon;$sum_weekly = 0; ?>


    <div class="pro-dashboard-content">
        <!-- Earning head -->
        <div class="earning-head">
            <div class="container providerrr">
                <div class="earning-element">
{{--                    <p class="earning-txt">@lang('provider.partner.total_earnings')</p>--}}
{{--                    <p class="earning-price" id="set_fully_sum">00.00</p>--}}
                </div>
            </div>
        </div>
        <!-- End of earning head -->

        <!-- Earning Content -->
        <div class="earning-content bg-white">
            <div class="container providerrr">


                @include('common.date_filter', ['action' => 'provider.earnings'])
                <form action="{{route('provider.earnings.pdf')}}" method="GET" id="pdf-download">
                    <input type="hidden" name="from_date" value="@isset($from_date){{$from_date}}@endisset">
                    <input type="hidden" name="to_date" value="@isset($to_date){{$to_date}}@endisset">
                </form>

                <!-- Earning section -->
                <div class="earning-section earn-main-sec pad20">
                    <!-- Earning section head -->
                    <div class="earning-section-head row no-margin">
                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                            <div class="daily-earn-right text-right">
                                <div class="status-block display-inline row no-margin">
                                <!-- <form class="form-inline status-form">
                                        <div class="form-group">
                                            <label>@lang('provider.partner.status')</label>
                                            <select type="password" class="form-control mx-sm-3">
                                                <option>@lang('provider.partner.all_trip')</option>
                                                <option>@lang('provider.partner.completed')</option>
                                                <option>@lang('provider.partner.pending')</option>
                                            </select>
                                        </div>
                                    </form> -->
                                </div>
                                <!-- View tab -->

                                <!-- End of view tab -->
                            </div>
                        </div>
                    </div>
                    <!-- End of earning section head -->
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-xs-12">
                            <div class="box box-block bg-white tile-1 mb-2 h-150">
                                <div class="t-icon right"><span class="bg-danger"></span><i class="ti-rocket"></i></div>
                                <div class="t-content">
                                    <h6 class="text-uppercase mb-1">@lang('provider.partner.trips_completed')</h6>
                                    <h1 class="mb-1">{{$provider[0]->accepted->count()}}</h1>
                                    <i class="fa fa-caret-up text-success mr-0-5"></i><span>has been initiated by users</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-xs-12">
                            <div class="box box-block bg-white tile-1 mb-2 h-150">
                                <div class="t-icon right"><span class="bg-success"></span><i class="ti-bar-chart"></i></div>
                                <div class="t-content">
                                    <h6 class="text-uppercase mb-1">@lang('provider.partner.total_earnings')</h6>
                                    <h1 class="mb-1">{{currency($fully_sum)}}</h1>
                                    <i class="fa fa-caret-up text-success mr-0-5"></i><span>from {{$provider[0]->accepted->count()}} Rides</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-xs-12">
                            <div class="box box-block bg-white tile-1 mb-2 h-150">
                                <div class="t-icon right"><span class="bg-warning"></span><i class="ti-archive"></i></div>
                                <div class="t-content">
                                    <h6 class="text-uppercase mb-1">@lang('provider.partner.driver_cancel')</h6>
                                    <h1 class="mb-1">{{$provider[0]->cancelled->count()}}</h1>
                                    <i class="fa fa-caret-down text-danger mr-0-5"></i><span>for @if($provider[0]->cancelled->count() == 0) 0.00 @else {{round($provider[0]->cancelled->count()/count($provider[0]->accepted->count()),2)}}% @endif Rides</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Earning-section content -->
                    <div class="tab-content list-content">
                        <div class="list-view pad30 ">
                            <table class="earning-table table table-responsive" id='table-8'>
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
												$StartTime = Carbon::parse( $each->started_at );
												$EndTime = Carbon::parse( $each->finished_at );
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
                                            <button id="{{$each->id}}" class="Dispute" value="{{$each->id}}">Dispute
                                            </button>
                                        </td>

                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- End of earning section -->
                    <div id="myModal" class="modal">
                        <!-- Modal content -->
                        <div class="modal-content text-center">
                            <span class="close">&times;</span>
                            <div id="disputeHead">
                                <div class="row dis">
                                    <div id="disputeBody">
                                        <div id="disputetitle">
                                        </div>
                                        <div id="disputeReasonBody">
                                        </div>

                                        <div class="col-xs-3" id="disputename">
                                        </div>
                                        <div class="col-xs-7" id="disputemsg">
                                        </div>
                                    </div>
                                </div>
                                <div id="disputeMsgBody">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal content end-->

                </div>
            </div>
            <!-- Endd of earning content -->
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('main/vendor/DataTables/js/jquery.dataTables.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('main/vendor/DataTables/js/dataTables.bootstrap4.min.js')}}"></script>
    <script type="text/javascript"
            src="{{ asset('main/vendor/DataTables/Responsive/js/dataTables.responsive.min.js')}}"></script>
    <script type="text/javascript"
            src="{{ asset('main/vendor/DataTables/Responsive/js/responsive.bootstrap4.min.js')}}"></script>
    <script type="text/javascript"
            src="{{ asset('main/vendor/DataTables/Buttons/js/dataTables.buttons.min.js')}}"></script>
    <script type="text/javascript"
            src="{{ asset('main/vendor/DataTables/Buttons/js/buttons.bootstrap4.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('main/vendor/DataTables/JSZip/jszip.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('main/vendor/DataTables/pdfmake/build/pdfmake.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('main/vendor/DataTables/pdfmake/build/vfs_fonts.js')}}"></script>
    <script type="text/javascript" src="{{ asset('main/vendor/DataTables/Buttons/js/buttons.html5.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('main/vendor/DataTables/Buttons/js/buttons.print.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('main/assets/js/tables-datatable.js')}}"></script>


    <script type="text/javascript">
        {{--document.getElementById('set_fully_sum').textContent = "{{currency($fully_sum)}}";--}}
        // Get the modal
        var modal = document.getElementById('myModal');

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        // When the user clicks the button, open the modal

        // When the user clicks on <span> (x), close the modal
        span.onclick = function () {
            modal.style.display = "none";
            $("#disputeHead").empty()
                .append(`<div class="row dis">
                  <div id="disputeBody">
                  <div id="disputetitle">
                            </div>
                      <div id="disputeReasonBody">
                            </div>

                            <div class="col-xs-3" id="disputename">
                            </div>
                            <div class="col-xs-7" id="disputemsg">
                            </div>
                                    </div>
                            </div>
                            <div id="disputeMsgBody">
                        </div>
                        </div>`);
            // modal1.style.display = "none";
        };

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
        var trip_id;
        /* Click Dispute */
        $(document).on("click", ".Dispute", function () {
            trip_id = this.id;
            modal.style.display = "block";
            var url = "{{url('provider/dispute','tripId')}}";
            var disputeMsgBody;
            url = url.replace('tripId', trip_id);
            $.ajax({
                url: url,
                type: "GET",
                success: function (data) {
                    console.log(data);
                    if (data.dispute.length != 0 && typeof (data.dispute) !== "undefined" && data.dispute != null) {
                        $("#disputeBody").find("#disputeReasonBody").empty().append(`<div class="col-xs-3">
                            <p class="loshead">Reason</p>
                            </div>
                            <div class="col-xs-7">
                            <h2 class="loshead">` + data.dispute[0]['dispute_name'] + `</h2></div>`);
                        $("#disputeBody").find("#disputetitle").empty().append(`<h2 class="loshead">` + data.dispute[0]['dispute_title'] + `</h2>
                        <input type="hidden"  id="disputeTitle" name="title" value="` + data.dispute[0]['dispute_title'] + `"/>`);
                        $("#disputeBody").find("#disputename").empty();
                        $("#disputeBody").find("#disputemsg").empty();
                        $.each(data.dispute, function (key, item) {
                            if (item.dispute_type == "provider") {
                                disputeMsgBody = "false";
                            }
                            if (typeof (item.comments) !== "undefined" && item.comments != null) {
                                $("#disputeBody").find("#disputename").append(`<p class="loshead">` + item.dispute_type + `</p>`);
                                $("#disputeBody").find("#disputemsg").append(`<p class="loshead">` + item.comments + `</p>`);
                            }
                        });
                        if (data.sendBtn == "yes") {
                            $("#disputeMsgBody").empty()
                                .append(` <div class="row dis">
                                   <div class="col-xs-3">
                                <h2>Status : Closed</h2>
                                </div>
                                </div>
                            `);
                        } else {
                            if (disputeMsgBody != "false") {
                                $("#disputeMsgBody").empty()
                                    .append(`<div class="row dis">
                              <div class="col-xs-3">
                                <h2>Comments</h2>
                             </div>
                             <div class="col-xs-7">
                             <textarea class="dispdesc" name="msg" id="disputeMsg" placeholder="Type your message"></textarea>
                             </div>
                             </div>
                            <input type="submit" name="submitbtn" class="submitbtn" id="sendBtn" value="send">`);
                            } else {
                                $("#disputeMsgBody").empty();
                            }
                        }
                    } else {
                        $("#disputeBody").empty()
                            .append(` <div class="row dis">
                            <h2 class="loshead">dispute</h2>
                            <div class="col-xs-3">
                                <h2>Reasons</h2>
                            </div>
                            <div class="col-xs-7">
                            <select class="form-control" id="disputeReason">
                            </select>
                             </div>
                             </div>
                             <div class="row dis">
                            <div class="col-xs-3">
                                <h2>Title</h2>
                            </div>
                            <div class="col-xs-7">
                            <input type="text" class="disptitle" id="disputeTitle" name="title" placeholder="Enter title"/>
                            </div>
                        </div>`);
                        $("#disputeReason").empty();
                        $.each(data.disputeReason, function (key, item) {
                            $("#disputeReason").append('<option value="' + item.dispute_name + '">' + item.dispute_name + '</option>');
                        });
                        $("#disputeReason").append('<option value="others">Others</option>');
                        $("#disputeMsgBody").empty()
                            .append(`<div id="disputeComments">
                           </div>
                          <input type="submit" name="submitbtn" class="submitbtn" id="disputeSendBtn" value="send">`);
                    }
                }
            });
        });
        /* Save dispute Record */

        $(document).on("click", "#disputeSendBtn", function (event) {
            event.preventDefault();
            if ($("#disputeReason").val() == null || $("#disputeReason").val() == '') {
                alert("Please Select Reason");
            }
            if ($("#disputeTitle").val() == null || $("#disputeTitle").val() == '') {
                alert("Please Enter Title");
            } else if (($("#disputeReason").val() == 'others') && ($("#disputeMsg").val() == null || $("#disputeMsg").val() == '')) {
                alert("Please Enter Message");
            } else {
                var url = "{{url('provider/dispute')}}";
                // console.log(trip_id);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{csrf_token()}}',
                        dispute_name: $("#disputeReason").val(),
                        dispute_title: $("#disputeTitle").val(),
                        comments: $("#disputeMsg").val(),
                        request_id: trip_id,
                    },
                    success: function (response) {
                        $("#disputeHead").empty()
                            .append(`<div class="row dis">
                  <div id="disputeBody">
                  <div id="disputetitle">
                            </div>
                      <div id="disputeReasonBody">
                            </div>

                            <div class="col-xs-3" id="disputename">
                            </div>
                            <div class="col-xs-7" id="disputemsg">
                            </div>
                                    </div>
                            </div>
                            <div id="disputeMsgBody">
                        </div>
                        </div>`);
                        modal.style.display = "none";
                        // $(".Dispute").trigger("click");
                    },
                    error: function (responce) {
                        alert(responce);
                    }
                });
            }

        });

        /* Select Project */
        $(document).on("change", "#disputeReason", function () {
            var disputeReason = $('#disputeReason').val();
            if (disputeReason == 'others') {
                $("#disputeComments").empty()
                    .append(`<div class="row dis">
                        <div class="col-xs-3">
                                <h2>Comments</h2>
                            </div>
                            <div class="col-xs-7">
                            <textarea class="dispdesc" name="msg" id="disputeMsg" placeholder="Type your message"></textarea>
                           </div>
                           </div>`);

            } else {
                $("#disputeComments").empty();
            }
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
