@extends('user.layout.base')

@section('title', 'My Trips ')
@section('styles')
    <link rel="stylesheet" href="{{ asset('main/assets/css/style_pagination.css')}}">

    <style>
        /* The Modal (background) */
        #myModal,
        #myModal1 {
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

        .invoiceForm:hover{
            background: #b531ba;
            color: white;
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

        .clients .table-responsive table.table.table-new a.paidstatus {
            background: transparent;
            padding: 0px 20px;
            display: inline;
        }

        .clients .table-responsive table.table.table-new .btn[name=payments],
        .clients .table-responsive table.table.table-new a {
            background: transparent;
            display: inline;
            padding: 0;
        }

        /* .modal.transition.visible.active {
            margin-top: 0 !important;
        } */
    </style>
@stop


@section('content')
    <!-- Modal content -->
    <div id="myModal" class="modal">
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
    <!-- Modal content -->
    <div id="myModal1" class="modal">

        <!-- Modal content -->
        <div class="modal-content text-center">
            <span class="close1">&times;</span>
            <div id="iteamHead">
                <div class="row dis">
                    <div id="lostitemBody">
                        <div id="title">
                        </div>
                        <div class="col-xs-3" id="name">
                        </div>
                        <div class="col-xs-7" id="msg">
                        </div>
                    </div>
                </div>
                <div id="lostitemMsgBody">
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    @foreach($trips as $trip)
        @include('user.invoice.trip-invoice', ['trip' => $trip, 'admin' => $admin,'fleet_id'=>$fleet_id])
    @endforeach
    <!-- Invoice Modal -->

    <div class="col-md-12 margin-top-10 clients">
        @include('common.date_filter', ['action' => 'trips'])
        <div class="row project-dash">
            <div class="col-sm-1">
                <div class="pm-heading" style="width:100%;">
                    <h2>@lang('user.my_trips')</h2>
                    <span>@lang('user.my_trips')</span>
                </div>
            </div>
            <div class="col-sm-2" style="margin-top: 14px;">
                <a href="{{ URL::to('downloadExcel/csv') }}"
                   class="btn btn-secondary buttons-copy buttons-html5">CSV</a>
            </div>
            <div class="col-sm-4 creative-right text-right" style="float: right;">
                <div class="pm-form" style="width: 100%;">
                    <form class="form-inline md-form form-sm">
                        <input class="form-control form-control" type="text" placeholder="Search Trips..."
                               id="protbl-input">
                        <!-- <button type="submit"><i class="fa fa-search" aria-hidden="true"></i></button> -->
                    </form>
                </div>
                <!-- <a href="add-new-project.php" class="btn orange"> Add new project</a></div>-->
            </div>
        </div>
        <div class="clearfix"></div>
        @if($trips->count() > 0)
            <div class="row">
                <div class="table-responsive">
                    <table class="table table-new projectspage clientside" data-pagination="true" data-page-size="5">
                        <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th class="">@lang('user.booking_id')</th>
                            <th>@lang('user.date')</th>
                            <th class="">@lang('user.profile.name')</th>
                            {{--                        @if(\Illuminate\Support\Facades\Auth::user()->company_name === "COMPANY") --}}
                            <th>@lang('user.ride.passenger')</th>
                            {{--                        @endif--}}
                            <th>@lang('user.amount')</th>
                            {{--                        <th>@lang('user.total_without_tax')</th>--}}
                            {{--                        <th>@lang('user.tax_amount')</th>--}}
                            <th>@lang('user.type')</th>
                            <th>Status</th>
                            <th>Invoice</th>
                        </tr>
                        </thead>
                        <tbody id="projects-tbl">
                        @foreach($trips as $trip)
                            <tr>
                                <td tid="{{ $trip->id }}" s_lon="{{ $trip->s_longitude }}"
                                    s_lat="{{ $trip->s_latitude }}" d_lon="{{ $trip->d_longitude }}"
                                    d_lat="{{ $trip->d_latitude }}" data-toggle="collapse"
                                    data-target="#trip_{{$trip->id}}" class="accordion-toggle collapsed"><span
                                            class="arrow-icon fa fa-chevron-right"></span></td>
                                <td class="tbl-ttl">
                                    <span class="onmobile">@lang('user.booking_id'): </span>
                                    {{ $trip->booking_id }}
                                </td>
                                <td class="clients-rpt" style="text-align: left;">
                                    <span class="onmobile centera">@lang('user.date') </span>
                                    {{date('d-m-Y',strtotime($trip->assigned_at))}}
                                </td>
                                <td>
                                    <div class="user-box"><input type="hidden" name="project_id" value="64">
                                        <button name="chat" type="submit" data-toggle="tooltip" data-placement="top"
                                                title=""
                                                data-original-title="{{$trip->provider->first_name}} {{$trip->provider->last_name}}">
                                            <img src="{{img($trip->provider->avatar)}}"></button>
                                    </div>
                                </td>
                                @if($trip->user->user_type === 'FLEET_COMPANY' || $trip->user->user_type === 'COMPANY')
                                    <td>{{$trip->user->company_name}}</td>
                                @else
                                    <td>{{$trip->user?$trip->user->first_name:''}} {{$trip->user?$trip->user->last_name:''}}</td>
                                @endif
                                <td class="prostatus">
                                    <span class="onmobile centera">@lang('user.amount') </span>
                                    <span class="inprogress">
                                @if($trip->payment_mode=='CASH' && $trip->use_wallet == 0)
                                            {{currency(round($trip->payment->total-$trip->payment->discount+$trip->payment->tips))}}
                                        @else
                                            {{currency($trip->payment->total-$trip->payment->discount+$trip->payment->tips)}}
                                        @endif
                            </span>
                                </td>
                                {{--     <td style="text-align: left;color: blue;">{{currency($trip->payment->fixed + $trip->payment->round_of + $trip->payment->distance)}}</td>
                                     <td style="color: red;">{{currency($trip->payment->tax)}}</td>--}}
                                <td>
                                    <span class="onmobile centera">@lang('user.type') </span> {{$trip->service_type->name}}
                                </td>
                                <td class="text-center"><span
                                            class="onmobile centera">@lang('user.payment')</span> @if($trip->paid == 1)
                                        <span class="paidstatus paid">Paid<span> @else <a href="{{url('/wallet')}}"
                                                                                          class="paidstatus unpaid">Make Payment</a> @endif
                                </td>
                                <td class="viewinvoicebt text-center">
                                    <form method="get" class="invoiceForm" idx="{{$trip->id}}" action="#"
                                          style="margin:0">
                                        <button type="submit" class="btn outine">View invoice</button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="hiddenRow">
                                <td colspan="12">
                                    <div class="accordian-body collapse row" id="trip_{{$trip->id}}">
                                        <div class="col-md-6">
                                            <div class="my-trip-left">
												<?php
												$map_icon = asset( 'asset/img/marker-start.png' );
												$static_map
													=
													"https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=600x450&maptype=terrain&format=png&visual_refresh=true&markers=icon:"
													. $map_icon . "%7C" . $trip->s_latitude . "," . $trip->s_longitude
													. "&markers=icon:" . $map_icon . "%7C" . $trip->d_latitude . ","
													. $trip->d_longitude . "&path=color:0x191919|weight:8|enc:"
													. $trip->route_key . "&key="
													. Config::get( 'constants.map_key' ); ?>
                                                <div class="map-static">
                                                    <div id="map{{$trip->id}}" style="width:100%;height:280px"></div>
                                                    <!-- <img src="" height="280px;"> -->
                                                </div>
                                                <div class="from-to row no-margin text-left">
                                                    <div class="from">
                                                        <h5>@lang('user.from')</h5>
                                                        <h6>{{date('H:i A - d-m-y', strtotime($trip->started_at))}}</h6>
                                                        <p>{{$trip->s_address}}</p>
                                                    </div>
                                                    <div class="to">
                                                        <h5>@lang('user.to')</h5>
                                                        <h6>{{date('H:i A - d-m-y', strtotime($trip->finished_at))}}</h6>
                                                        <p>{{$trip->d_address}}</p>
                                                        <input type="hidden" id="trip_id" class="dispute"
                                                               value="{{$trip->id}}"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">

                                            <div class="mytrip-right">
                                                <div class="whlbtn">
                                                    <button id="{{$trip->id}}" class="dispute" value="{{$trip->id}}">
                                                        Dispute
                                                    </button>
                                                    <button id="{{$trip->id}}" class="lostitem" value="{{$trip->id}}">
                                                        LostItem
                                                    </button>
                                                </div>
                                                <div class="fare-break">

                                                    <h4 class="text-left">
                                                        <strong>
                                                            @if($trip->service_type)
                                                                {{$trip->service_type->name}}
                                                            @endif
                                                            - @lang('user.fare_breakdown')</strong></h4>

                                                    <h5 class="text-left">@lang('user.ride.base_price') <span>
                                                    @if($trip->payment)
                                                                {{currency($trip->payment->fixed)}}
                                                            @endif
                                                </span></h5>
                                                    @if($trip->service_type->calculator=='MIN')
                                                        <h5 class="text-left">@lang('user.ride.minutes_price') <span>
                                                    @if($trip->payment)
                                                                    {{currency($trip->payment->minute)}}
                                                                @endif
                                                </span></h5>
                                                    @endif
                                                    @if($trip->service_type->calculator=='HOUR')
                                                        <h5 class="text-left">@lang('user.ride.hours_price') <span>
                                                    @if($trip->payment)
                                                                    {{currency($trip->payment->hour)}}
                                                                @endif
                                                </span></h5>
                                                    @endif
                                                    @if($trip->service_type->calculator=='DISTANCE')
                                                        <h5 class="text-left">@lang('user.ride.distance_price') <span>
                                                    @if($trip->payment)
                                                                    {{currency($trip->payment->distance)}}
                                                                @endif
                                                </span></h5>
                                                    @endif
                                                    @if($trip->service_type->calculator=='DISTANCEMIN')
                                                        <h5 class="text-left">@lang('user.ride.minutes_price') <span>
                                                    @if($trip->payment)
                                                                    {{currency($trip->payment->minute)}}
                                                                @endif
                                                </span></h5>
                                                        <h5 class="text-left">@lang('user.ride.distance_price') <span>
                                                    @if($trip->payment)
                                                                    {{currency($trip->payment->distance)}}
                                                                @endif
                                                </span></h5>
                                                    @endif
                                                    @if($trip->service_type->calculator=='DISTANCEHOUR')
                                                        <h5 class="text-left">@lang('user.ride.hours_price') <span>
                                                    @if($trip->payment)
                                                                    {{currency($trip->payment->hour)}}
                                                                @endif
                                                </span></h5>
                                                        <h5 class="text-left">@lang('user.ride.distance_price') <span>
                                                    @if($trip->payment)
                                                                    {{currency($trip->payment->distance)}}
                                                                @endif
                                                </span></h5>
                                                    @endif

                                                    @if($trip->payment)
                                                        @if($trip->payment->wallet)
                                                            <h5 class="text-left">@lang('user.ride.wallet_deduction')
                                                                <span>
                                                    {{currency($trip->payment->wallet)}}
                                                </span></h5>
                                                        @endif
                                                    @endif
                                                    @if($trip->payment)
                                                        @if($trip->payment->discount)
                                                            <h5 class="text-left">@lang('user.ride.promotion_applied')
                                                                <span>
                                                    {{currency($trip->payment->discount)}}
                                                </span></h5>
                                                        @endif
                                                    @endif
                                                    @if($trip->payment)
                                                        @if($trip->payment->tips)
                                                            <h5 class="text-left">@lang('user.ride.tips') <span>
                                                    {{currency($trip->payment->tips)}}
                                                </span></h5>
                                                        @endif
                                                    @endif
                                                    {{-- peak hours charge --}}
                                                    <h5 class="text-left"><strong>@lang('user.ride.peak_hours_charge') </strong><span><strong>
                                                        @if($trip->payment)
                                                                    {{currency($trip->payment->peak_amount)}}
                                                                @endif
                                                    </strong></span></h5>
                                                    <h5 class="text-left"><strong>@lang('user.ride.tax_price') </strong><span><strong>
                                                        @if($trip->payment)
                                                                    {{currency($trip->payment->tax)}}
                                                                @endif
                                                    </strong></span></h5>

                                                    @if($trip->payment->waiting_amount>0)
                                                        <h5 class="text-left">
                                                            <strong>@lang('user.ride.waiting_price') </strong><span><strong>
                                                        {{currency($trip->payment->waiting_amount)}}
                                                    </strong></span></h5>
                                                    @endif

                                                    @if($trip->payment->round_of)
                                                        <h5 class="text-left">
                                                            <strong>@lang('user.ride.round_off') </strong><span><strong>
                                                        {{currency($trip->payment->round_of)}}
                                                    </strong></span></h5>
                                                    @endif

                                                    <h5 class="big text-left"><strong>@lang('user.charged')
                                                            - @if($trip->use_wallet == 0){{$trip->payment_mode}}@else
                                                                Wallet @endif </strong><span><strong>
                                                @if($trip->payment)
                                                                    @if($trip->payment_mode=='CASH' && $trip->use_wallet == 0)
                                                                        {{currency(round($trip->payment->total-$trip->payment->discount+$trip->payment->tips))}}
                                                                    @else
                                                                        {{currency($trip->payment->total-$trip->payment->discount+$trip->payment->tips)}}
                                                                    @endif
                                                                @endif
                                            </strong></span></h5>

                                                </div>

                                                <div class="trip-user">
                                                    <div class="user-img"
                                                         style="background-image: url({{img($trip->provider->avatar)}});">
                                                    </div>
                                                    <div class="user-right">
                                                        @if($trip->provider)
                                                            <h5 class="text-left">{{$trip->provider->first_name}} {{$trip->provider->last_name}}</h5>
                                                        @else
                                                            <h5>- </h5>
                                                        @endif
                                                        @if($trip->rating)
                                                            <div class="rating-outer text-left">
                                                                <input type="hidden" class="rating"
                                                                       value="{{$trip->rating->provider_rating}}"
                                                                       disabled="disabled"/>

                                                            </div>
                                                            <p class="text-left">{{$trip->rating->user_comment}} </p>
                                                        @else
                                                            -
                                                        @endif
                                                    </div>
                                                </div>

                                            </div>

                                        </div>

                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>
            </div>
        @else
            <hr>
            <p style="text-align: center;">@lang('user.no_trips')</p>
        @endif
    </div>

@endsection
@section('scripts')
    <script type="text/javascript" src="{{ asset('asset/js/rating.js') }}"></script>
    <script type="text/javascript">
        $('.rating').rating();
        $('.invoiceForm').on('submit', function (e) {
            var id = $(this).attr('idx');
            console.log(id);
            $('#view-invoice' + id).modal('show');
            $("#view-invoice" + id).css('top', 0);
            $("#view-invoice" + id).css('margin-top', 0);
            $("#view-invoice" + id).scrollTop(0);
            e.preventDefault();
            return false;
        });

        $('a.download_pdf').on('click', function () {
            $('#formDownloadPDF' + $(this).attr('trip_id')).submit();
        });
    </script>
    <script>
        // Get the modal
        var modal = document.getElementById('myModal');
        var modal1 = document.getElementById('myModal1');
        // Get the button that opens the modal
        // var btn = document.getElementById("myBtn");
        // var btn1 = document.getElementById("myBtn1");
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        var span1 = document.getElementsByClassName("close1")[0];
        // When the user clicks the button, open the modal
        // btn.onclick = function() {
        //   modal.style.display = "block";

        // }
        // btn1.onclick = function() {
        //    modal1.style.display = "block";
        // }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function () {
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
            // modal1.style.display = "none";
        };
        span1.onclick = function () {
            modal1.style.display = "none";
        };
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
                modal1.style.display = "none";
            }
        };
        /* Click LostItem */
        $(document).on("click", ".lostitem", function () {
            var trip_id = this.id;

            modal1.style.display = "block";
            var url = "{{url('lostitem','id')}}";
            url = url.replace('id', trip_id);
            $.ajax({
                url: url,
                type: "GET",
                success: function (data) {
                    if (data.lostitem.length != 0 && typeof (data.lostitem) !== "undefined" && data.lostitem != null) {
                        $("#lostitemBody").find("#title").empty().append(`<h2 class="loshead">` + data.lostitem[0]['lost_item_name'] + `</h2>
                            <input type="hidden"  id="lostitemTitle" name="title" value="` + data.lostitem[0]['lost_item_name'] + `"/>`);
                        $("#lostitemBody").find("#name").empty();
                        $("#lostitemBody").find("#msg").empty();
                        $.each(data.lostitem, function (key, item) {
                            $("#lostitemBody").find("#name").append(`<p class="loshead">` + item.comments_by + `</p>`);
                            $("#lostitemBody").find("#msg").append(`<p class="loshead">` + item.comments + `</p>`);
                        });
                        if (data.sendBtn == "yes") {
                            $("#lostitemMsgBody").empty()
                                .append(` <div class="row dis">
                                    <div class="col-xs-3">
                                    <h2>Status : Closed</h2>
                                    </div>
                                    </div>
                                `);
                        } else {
                            $("#lostitemMsgBody").empty()
                                .append(`<div class="row dis">
                            <div class="col-xs-3">
                                    <h2>Comments</h2>
                                </div>
                                <div class="col-xs-7">
                                <textarea class="dispdesc" name="msg" id="lostitemMsg" placeholder="Type your message"></textarea>
                            </div>
                            </div>
                            <input type="submit" name="submitbtn" class="submitbtn lostitemBtn" id="` + trip_id + `" value="send">`);
                        }
                    } else {

                        $("#lostitemBody").empty()
                            .append(` <div class="row dis">
                                <h2 class="loshead">Lostitem</h2>
                                <div class="col-xs-3">
                                    <h2>Title</h2>
                                </div>
                                <div class="col-xs-7">
                                <input type="text" class="disptitle" id="lostitemTitle" name="title" placeholder="Enter title"/>
                                </div>
                            </div>`);
                        $("#lostitemMsgBody").empty()
                            .append(`<div class="row dis">
                            <div class="col-xs-3">
                                    <h2>Comments</h2>
                                </div>
                                <div class="col-xs-7">
                                <textarea class="dispdesc" name="msg" id="lostitemMsg" placeholder="Type your message"></textarea>
                            </div>
                            </div>
                            <input type="submit" name="submitbtn" class="submitbtn lostitemBtn" id="` + trip_id + `" value="send">`);
                    }
                }
            });
        });
        /* Save lostItem Record */
        $(document).on("click", ".lostitemBtn", function (event) {
            event.preventDefault();
            var tripId = this.id;
            if ($("#lostitemTitle").val() == null || $("#lostitemTitle").val() == '') {
                alert("Please Enter Title");
            } else if ($("#lostitemMsg").val() == null || $("#lostitemMsg").val() == '') {
                alert("Please Enter Message");
            } else {
                var url = "{{url('lostitem')}}";
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{csrf_token()}}',
                        lost_item_name: $("#lostitemTitle").val(),
                        comments: $("#lostitemMsg").val(),
                        request_id: tripId,
                    },
                    success: function (response) {
                        $("#lostitemMsg").val('');
                        $("#iteamHead").empty()
                            .append(`
                                <div class="row dis">
                                <div id="lostitemBody">
                                        <div id="title">
                                            </div>
                                            <div class="col-xs-3" id="name">
                                            </div>
                                            <div class="col-xs-7" id="msg">
                                            </div>
                                        </div>
                        </div>
                            <div id="lostitemMsgBody">
                        </div>`);
                        modal1.style.display = "none";
                        // $("#myBtn1").trigger("click");
                    },
                    error: function (responce) {
                        alert(responce);
                    }
                });
            }

        });

        /* Click Dispute */
        $(document).on("click", ".dispute", function () {
            modal.style.display = "block";
            var trip_id = this.id;
            var url = "{{url('dispute','id')}}";
            var disputeMsgBody;
            url = url.replace('id', trip_id);
            $.ajax({
                url: url,
                type: "GET",
                success: function (data) {
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
                            if (item.dispute_type == "user") {
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
                            <input type="submit" name="submitbtn" class="submitbtn disputeSendBtn" id="` + trip_id + `" value="send">`);
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
                          <input type="submit" name="submitbtn" class="submitbtn disputeSendBtn" id="` + trip_id + `" value="send">`);
                    }
                }
            });
        });
        /* Save dispute Record */

        $(document).on("click", ".disputeSendBtn", function (event) {
            event.preventDefault();
            var tripId = this.id;
            if ($("#disputeReason").val() == null || $("#disputeReason").val() == '') {
                alert("Please Select Reason");
            }
            if ($("#disputeTitle").val() == null || $("#disputeTitle").val() == '') {
                alert("Please Enter Title");
            } else if (($("#disputeReason").val() == 'others') && ($("#disputeMsg").val() == null || $("#disputeMsg").val() == '')) {
                alert("Please Enter Message");
            } else {
                var url = "{{url('dispute')}}";

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{csrf_token()}}',
                        dispute_name: $("#disputeReason").val(),
                        dispute_title: $("#disputeTitle").val(),
                        comments: $("#disputeMsg").val(),
                        request_id: tripId,
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
                        // $("#myBtn").trigger("click");
                        modal.style.display = "none";

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

        function initMap(id, s_lat, s_lon, d_lat, d_lon) {
                    @foreach($trips as $trip)
            var map;

            map = new google.maps.Map(document.getElementById('map' + id));

            var marker = new google.maps.Marker({
                map: map,
                icon: '/asset/img/marker-start.png',
                anchorPoint: new google.maps.Point(0, -29)
            });

            var markerSecond = new google.maps.Marker({
                map: map,
                icon: '/asset/img/marker-end.png',
                anchorPoint: new google.maps.Point(0, -29)
            });

            var bounds = new google.maps.LatLngBounds();

            source = new google.maps.LatLng(s_lat, s_lon);
            destination = new google.maps.LatLng(d_lat, d_lon);

            marker.setPosition(source);
            markerSecond.setPosition(destination);

            var directionsService = new google.maps.DirectionsService;
            var directionsDisplay = new google.maps.DirectionsRenderer({
                suppressMarkers: true,
                preserveViewport: true
            });
            directionsDisplay.setMap(map);

            directionsService.route({
                origin: source,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING
            }, function (result, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    console.log(result);
                    directionsDisplay.setDirections(result);

                    marker.setPosition(result.routes[0].legs[0].start_location);
                    markerSecond.setPosition(result.routes[0].legs[0].end_location);
                }
            });

            bounds.extend(marker.getPosition());
            bounds.extend(markerSecond.getPosition());
            map.fitBounds(bounds);
            @endforeach
        }

        $(document).on('click', '.accordion-toggle.collapsed', function (e) {
            initMap($(this).attr('tid'), $(this).attr('s_lat'), $(this).attr('s_lon'), $(this).attr('d_lat'), $(this).attr('d_lon'));
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
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('constants.map_key') }}&libraries=places"
            async defer></script>
@endsection
