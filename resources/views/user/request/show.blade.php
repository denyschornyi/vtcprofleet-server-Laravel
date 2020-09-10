@extends('user.layout.base')
@section('title', 'Request details ')

@section('styles-in')
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.2);
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        min-width: 500px;
        padding: 20px;
        border: 1px solid #888;
        width: 50%;
        margin-top: 50px;
        height: calc(100vh - 100px);
        overflow-y: auto;
    }

    /* .provider_lists {
        overflow-y: auto;
    } */

    /* The Close Button */
    .close {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
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

    .ui.dimmer {
        background-color: rgba(0, 0, 0, 0.4);
    }

    .column {
        float: left;
        width: 25%;
        padding: 10px;
    }

    /* Clear floats after the columns */
    .gd-row:after {
        content: "";
        display: table;
        clear: both;
    }

    .gd-row {
        border-bottom: 1px solid gray;
    }

    .gd-row .column.bg1{
        background-color: #bbb;
    }
    .gd-row .column.bg2{
        background-color: #ccc;
    }
    .gd-row .column.bg3{
        background-color: #ddd;
    }
    .gd-row .column.bg0{
        background-color: #aaa;
    }

    .limited_line1 {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        line-height: 36px;     /* fallback */
        max-height: 30px;      /* fallback */
        -webkit-line-clamp: 1; /* number of lines to show */
        -webkit-box-orient: vertical;
    }
    .limited_line2 {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        line-height: 16px;     /* fallback */
        max-height: 22px;      /* fallback */
        -webkit-line-clamp: 1; /* number of lines to show */
        -webkit-box-orient: vertical;
    }
</style>
@endsection

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        @include('common.notify')
        <div id="myModal" class="modal">
            <div class="modal-content text-center">
                <span class="close">&times;</span>
                <span class="modal_title" style="font-weight:800; font-size:20px;">Assign Provider</span>
                <br><br><br>
                <div class="provider_lists">
                </div>
            </div>
        </div>

        <div class="box box-block bg-white">
            <h4>@lang('admin.request.request_details')</h4><br>
            <a href="#" id="back_btn" class="btn btn-default pull-right">
                <i class="fa fa-angle-left"></i> @lang('admin.back')
            </a>
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">@lang('admin.request.Booking_ID') :</dt>
                        <dd class="col-sm-8">{{ $request->booking_id }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.User_Name') :</dt>
                        <dd class="col-sm-8">{{ $request->user->first_name }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.Provider_Name') :</dt>
                        @if($request->provider)
                        <dd class="col-sm-8">{{ $request->provider->first_name }}</dd>
                        @else
                        <dd class="col-sm-8">@lang('admin.request.provider_not_assigned')</dd>
                        @endif

                        <dt class="col-sm-4">@lang('admin.request.total_distance') :</dt>
                        <dd class="col-sm-8">{{ $request->distance ? $request->distance : '-' }}{{$request->unit}}</dd>

                        @if($request->status == 'SCHEDULED')
                        <dt class="col-sm-4">@lang('admin.request.ride_scheduled_time') :</dt>
                        <dd class="col-sm-8">
                            @if($request->schedule_at != "")
                                {{ appDateTime($request->schedule_at) }}
                            @else
                                -
                            @endif
                        </dd>
                        @else
                        <dt class="col-sm-4">@lang('admin.request.ride_start_time') :</dt>
                        <dd class="col-sm-8">
                            @if($request->started_at != "")
                                {{ appDateTime($request->started_at) }}
                            @else
                                -
                            @endif
                         </dd>

                        <dt class="col-sm-4">@lang('admin.request.ride_end_time') :</dt>
                        <dd class="col-sm-8">
                            @if($request->finished_at != "")
                                {{ appDateTime($request->finished_at) }}
                            @else
                                -
                            @endif
                        </dd>
                        @endif

                        <dt class="col-sm-4">@lang('admin.request.pickup_address') :</dt>
                        <dd class="col-sm-8">{{ $request->s_address ? $request->s_address : '-' }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.drop_address') :</dt>
                        <dd class="col-sm-8">{{ $request->d_address ? $request->d_address : '-' }}</dd>

                        @if($request->payment)
                        <dt class="col-sm-4">@lang('admin.request.base_price') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->fixed) }}</dd>
                        @if($request->service_type->calculator=='MIN')
                            <dt class="col-sm-4">@lang('admin.request.minutes_price') :</dt>
                            <dd class="col-sm-8">{{ currency($request->payment->minute) }}</dd>
                        @endif
                        @if($request->service_type->calculator=='HOUR')
                            <dt class="col-sm-4">@lang('admin.request.hours_price') :</dt>
                            <dd class="col-sm-8">{{ currency($request->payment->hour) }}</dd>
                        @endif
                        @if($request->service_type->calculator=='DISTANCE')
                            <dt class="col-sm-4">@lang('admin.request.distance_price') :</dt>
                            <dd class="col-sm-8">{{ currency($request->payment->distance) }}</dd>
                        @endif
                        @if($request->service_type->calculator=='DISTANCEMIN')
                            <dt class="col-sm-4">@lang('admin.request.minutes_price') :</dt>
                            <dd class="col-sm-8">{{ currency($request->payment->minute) }}</dd>
                            <dt class="col-sm-4">@lang('admin.request.distance_price') :</dt>
                            <dd class="col-sm-8">{{ currency($request->payment->distance) }}</dd>
                        @endif
                        @if($request->service_type->calculator=='DISTANCEHOUR')
                            <dt class="col-sm-4">@lang('admin.request.hours_price') :</dt>
                            <dd class="col-sm-8">{{ currency($request->payment->hour) }}</dd>
                            <dt class="col-sm-4">@lang('admin.request.distance_price') :</dt>
                            <dd class="col-sm-8">{{ currency($request->payment->distance) }}</dd>
                        @endif
                        <dt class="col-sm-4">@lang('admin.request.commission') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->commision) }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.fleet_commission') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->fleet) }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.discount_price') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->discount) }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.peak_amount') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->peak_amount) }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.peak_commission') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->peak_comm_amount) }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.waiting_charge') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->waiting_amount) }}</dd>

                        <dt class="col-sm-4" style="padding-right:0px;">@lang('admin.request.waiting_commission') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->waiting_comm_amount) }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.tax_price') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->tax) }}</dd>

                       <!--  <dt class="col-sm-4">@lang('admin.request.surge_price') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->surge) }}</dd> -->

                        <dt class="col-sm-4">@lang('admin.request.tips') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->tips) }}</dd>

                        <dt class="col-sm-4">@lang('user.ride.round_off') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->round_of) }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.total_amount') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->total+$request->payment->tips) }}</dd>

                        <dt class="col-sm-4">@lang('admin.request.wallet_deduction') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->wallet) }}</dd>

                        <!-- <dt class="col-sm-4">@lang('admin.request.paid_amount') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->payable) }}</dd> -->

                        <dt class="col-sm-4">@lang('admin.request.payment_mode') :</dt>
                        <dd class="col-sm-8">{{ $request->payment->payment_mode }}</dd>
                        @if($request->payment->payment_mode=='CASH')
                            <dt class="col-sm-4">@lang('admin.request.cash_amount') :</dt>
                            <dd class="col-sm-8">{{ currency($request->payment->cash) }}</dd>
                        @else
                            <dt class="col-sm-4">@lang('admin.request.card_amount') :</dt>
                            <dd class="col-sm-8">{{ currency($request->payment->card) }}</dd>
                        @endif
                        <dt class="col-sm-4">@lang('admin.request.provider_earnings'):</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->provider_pay) }}</dd>

                       <!--  <dt class="col-sm-4">Provider Admin Commission :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->provider_commission) }}</dd> -->
                        @endif

                        <dt class="col-sm-4">@lang('admin.request.ride_status') : </dt>
                        <dd class="col-sm-8">
                            {{ $request->status }}
                             <br /> {{ $request->cancel_reason }}
                        </dd>

                        @if($request->status =="COMPLETED")
                        <dt class="col-sm-4">@lang('admin.request.user_rating') : </dt>
                        <dd class="col-sm-8">
                        @if($request->user_rated ==1)
                            {{ $request->rating->user_rating}}
                             @else
                                -
                            @endif
                        </dd>
                        <dt class="col-sm-4">@lang('admin.request.user_comment') : </dt>
                        <dd class="col-sm-8">
                        @if($request->user_rated ==1)
                          {{ $request->rating->user_comment }}
                             @else
                                -
                            @endif
                        </dd>
                        <dt class="col-sm-4">@lang('admin.request.provider_rating') : </dt>
                        <dd class="col-sm-8">
                        @if($request->provider_rated ==1)
                            {{ $request->rating->provider_rating }}
                             @else
                                -
                            @endif
                        </dd>
                        <dt class="col-sm-4">@lang('admin.request.provider_comment') : </dt>
                        <dd class="col-sm-8">
                        @if($request->provider_rated ==1)
                            {{ $request->rating->provider_comment }}
                             @else
                                -
                            @endif
                        </dd>
                       @endif
                       @if(!empty($request->repeated))
                       <dt class="col-sm-4">Schedule Date:</dt>
                       <dd class="col-sm-8">@php echo implode(" | ",$request->repeated)@endphp</dd>
                       @endif
                       <dt class="col-sm-4">Note : </dt>
                       <dd class="col-sm-8">
                           {{ $request->note }}
                       </dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <div id="map"></div>
                </div>
            </div>
            @if($request->status == 'SCHEDULED')
            <!-- <a class="btn btn-info assign_provider" href="javascript:void(0);" >Assign Provider</a> -->
            <!-- <a class="btn btn-info assign_fleet" style="margin-left:10px; background-color:#2fb920" href="javascript:void(0);" >Assign Fleet</a> -->
            @endif
            </div>
    </div>
</div>
@endsection

@section('styles')
<style type="text/css">
    #map {
        height: 450px;
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript">

    $('#back_btn').on('click', function(e) {
        window.history.back();
    });

    var zoomLevel = 11;
    function initMap() {
        var map;

        map = new google.maps.Map(document.getElementById('map'));

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

        source = new google.maps.LatLng({{ $request->s_latitude }}, {{ $request->s_longitude }});
        destination = new google.maps.LatLng({{ $request->d_latitude }}, {{ $request->d_longitude }});

        marker.setPosition(source);
        markerSecond.setPosition(destination);

        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true, preserveViewport: true});
        directionsDisplay.setMap(map);

        directionsService.route({
            origin: source,
            destination: destination,
            travelMode: google.maps.TravelMode.DRIVING
        }, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                console.log(result);
                directionsDisplay.setDirections(result);

                marker.setPosition(result.routes[0].legs[0].start_location);
                markerSecond.setPosition(result.routes[0].legs[0].end_location);
            }
        });

        @if($request->provider && $request->status != 'COMPLETED')
        var markerProvider = new google.maps.Marker({
            map: map,
            icon: "/asset/img/marker-car.png",
            anchorPoint: new google.maps.Point(0, -29)
        });

        provider = new google.maps.LatLng({{ $request->provider->latitude }}, {{ $request->provider->longitude }});
        markerProvider.setVisible(true);
        markerProvider.setPosition(provider);
        console.log('Provider Bounds', markerProvider.getPosition());
        bounds.extend(markerProvider.getPosition());
        @endif

        bounds.extend(marker.getPosition());
        bounds.extend(markerSecond.getPosition());
        map.fitBounds(bounds);
    }

    $('.assign_provider').click(function(e) {
        $('.modal_title').html("Assign Provider");
        $('.provider_lists').html("...");
        $('#myModal').modal('show');
        var url = "{{url('/adm/prov/list')}}";
        $.ajax({
            url: url,
            type: "GET",
            data: {_token: '{{csrf_token()}}'},
            beforeSend: function() {
            },
            success: function(responseJSON) {
                console.log(responseJSON);
                var data = JSON.parse(responseJSON);
                var provs = data.data;
                var html = "";
                for(i = 0 ; i < provs.length ; i++) {
                    if (i % 4 == 0)
                    html += '<div class="gd-row">';
                    // <img src="{{url("/")}}/'+provs[i]['avatar']+'" width=50 height=50 class="img-fluid rounded-circle">\
                    html += '<div onclick="javascript:onClickProvider('+provs[i]['id']+', \''+provs[i]['first_name']+" "+provs[i]['last_name']+'\')" data-prov-id="'+provs[i]['id']+'" class="provider column bg'+(i % 4)+'">\
                                <img src="{{url("/")}}/main/avatar.jpg" width=50 height=50 class="img-fluid rounded-circle">\
                                <h2 class="limited_line1">'+provs[i]['first_name']+" "+provs[i]['last_name']+'</h2>\
                                <p class="limited_line2">'+provs[i]['email']+'</p>\
                                <p style="color:#b531ba;font-weight:bold">Click to Assign</p>\
                            </div>';
                    if (i % 4 == 3 || i == provs.length - 1)
                        html += '</div>'
                }
                var form = '<form id="assign_provider_form"  action="{{url("/admin/assign/provider")}}" method="POST">\
                            <input type="hidden" name="_token" value="{{csrf_token()}}">\
                            <input type="hidden" id="p_i_d" name="provider_id" value="">\
                            <input type="hidden" name="id" value="{{$request->id}}">\
                            '+html+'\
                            </form>';
                $('.provider_lists').html(form);
            },
            error: function(){
                $('.provider_lists').html("There are no providers.");
            }
        });
    });

    $('.assign_fleet').click(function(e) {
        $('.modal_title').html("Assign Fleet");
        $('.provider_lists').html("...");
        $('#myModal').modal('show');
        var url = "{{url('/adm/flt/list')}}";
        $.ajax({
            url: url,
            type: "GET",
            data: {_token: '{{csrf_token()}}'},
            beforeSend: function() {
            },
            success: function(responseJSON) {
                console.log(responseJSON);
                var data = JSON.parse(responseJSON);
                var fleet = data.data;
                var html = "";
                for(i = 0 ; i < fleet.length ; i++) {
                    if (i % 4 == 0)
                    html += '<div class="gd-row">';
                    html += '<div onclick="onClickFleet('+fleet[i]['id']+', \''+fleet[i]['name']+'\')" data-fleet-id="'+fleet[i]['id']+'" class="provider column bg'+(i % 4)+'">\
                                <h2 class="limited_line1">'+fleet[i]['name']+'</h2>\
                                <p class="limited_line2">'+fleet[i]['email']+'</p>\
                                <p style="color:#b531ba;font-weight:bold">Click to Assign</p>\
                            </div>';
                    if (i % 4 == 3 || i == fleet.length - 1)
                        html += '</div>'
                }
                var form = '<form id="assign_fleet_form" action="{{url("/admin/assign/fleet")}}" method="POST">\
                            <input type="hidden" name="_token" value="{{csrf_token()}}">\
                            <input type="hidden" id="f_i_d" name="fleet_id" value="">\
                            <input type="hidden" name="id" value="{{$request->id}}">\
                            '+html+'\
                            </form>';
                $('.provider_lists').html(form);
            },
            error: function(){
                $('.provider_lists').html("There are no fleets.");
            }
        });
    });

    function onClickFleet(fid, name) {
        if (confirm("Are you sure to assign with <"+name+">?") == true) {
            $("#f_i_d").val(fid);
            $("#assign_fleet_form").submit();
        }
    }

    function onClickProvider(pid, name) {
        if (confirm("Are you sure to assign with <"+name+">?") == true) {
            $("#p_i_d").val(pid);
            $("#assign_provider_form").submit();
        }
    }

</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('constants.map_key') }}&libraries=places&callback=initMap" async defer></script>
@endsection
