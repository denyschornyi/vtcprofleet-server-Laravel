@extends('user.layout.base')

@section('title')
@section('content')

<div class="col-md-12 margin-top-10 clients">
    <div class="row project-dash">
            <div class="col-sm-6">
                <div class="pm-heading" style="width:100%;">
                    <h2>@lang('user.upcoming_trips')</h2><span>@lang('user.upcoming_trips')</span>
                </div>
            </div>
            <div class="col-sm-6 creative-right text-right">
                <div class="pm-form" style="width: 100%;">
                    <form class="form-inline md-form form-sm">
                        <input class="form-control form-control" type="text" placeholder="Search Trips..." id="protbl-input">
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

                        <th>@lang('user.ride.passenger')</th>
                        <th>@lang('user.type')</th>
                        <th>@lang('user.payment')</th>
                        <th>@lang('admin.request.total_amount')</th>
                    </tr>
                </thead>
                <tbody id="projects-tbl">
                @php $i = 0 @endphp
                @foreach($trips as $trip)
                    <tr>
                        <td data-toggle="collapse" data-target="#trip_{{$trip->id}}" class="accordion-toggle collapsed"><span class="arrow-icon fa fa-chevron-right"></span></td>
                        <td class="tbl-ttl">
                            <span class="onmobile">@lang('user.booking_id'): </span>
                            {{ $trip->booking_id }}</td>
                        <td class="clients-rpt" style="text-align: left;">
                            <span class="onmobile centera">@lang('user.date') </span>
                            {{date('d-m-Y H:i:s',strtotime($trip->schedule_at))}}
                        </td>
{{--                        @if($trip->user->user_type == 'COMPANY' && $trip->user->company_name !== '' )--}}
{{--                            <td>{{$trip->user?$trip->user->first_name:''}} {{$trip->user?$trip->user->last_name:''}}</td>--}}
{{--                        @elseif($trip->user->user_type == 'NORMAL')--}}
{{--                            @if($request->user->first_name !== '')--}}
                                <td>{{$trip->user?$trip->user->first_name:''}} {{$trip->user?$trip->user->last_name:''}}</td>
{{--                            @else--}}
{{--                                <td>{{$trip->user->company_name}}</td>--}}
{{--                            @endif--}}
{{--                        @endif--}}
                        <td><span class="onmobile centera">@lang('user.type') </span> {{$trip->service_type->name}}</td>
                        <td>
                            <span class="onmobile centera">@lang('user.payment') </span> @lang('user.paid_via')
                            {{$trip->payment_mode}}
                        </td>
                        <td>  {{currency_number($trip->total_price)}} &euro;</td>
                    </tr>
                    <tr class="hiddenRow">
                        <td colspan="8" style='text-align:left'>
{{--                            <div class="accordian-body collapse row" id="trip_{{$trip->id}}" style="margin-top: 15px;">--}}
                            <div class="accordian-body collapse row" id="trip_{{$trip->id}}" style="margin-top: 15px;">
                                <div class="col-md-6">
                                    <div class="my-trip-left">
                                    <?php
//                                        $map_icon = asset('asset/img/marker-start.png');
//                                        $static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=600x450&maptype=terrain&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$trip->s_latitude.",".$trip->s_longitude."&markers=icon:".$map_icon."%7C".$trip->d_latitude.",".$trip->d_longitude."&path=color:0x191919|weight:8|enc:".$trip->route_key."&key=".Config::get('constants.map_key'); ?>
                                        <div class="map-static text-center" >
{{--                                            <img src="{{$static_map}}" height="280px;">--}}
                                            <div id="map_{{$trip->id}}" style="width: 100%;height: 280px;"></div>
                                        </div>
                                        <div class="from-to row no-margin">
                                            <div class="from">
                                                <h5>@lang('user.from')</h5>
                                                <p>{{$trip->s_address}}</p>
                                            </div>
                                            <div class="from">
                                                <h5>@lang('user.waypoints')</h5>
                                                <p> {{ $trip->way_points ? \App\UserRequests::getWayPointAddress($trip->way_points) : '-' }}</p>
                                            </div>
                                            <div class="to">
                                                <h5>@lang('user.to')</h5>
                                                <p>{{$trip->d_address}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">

                                    <div class="mytrip-right">
                                        <div>
                                            <h5>@lang('user.provider_details')</h5>
                                            <div class="trip-user">
                                            </div>
                                            <div class="user-right">
                                            @if($trip->provider)
                                                <h5>{{$trip->provider->first_name}} {{$trip->provider->last_name}}</h5>
                                            @endif
                                                <p>{{$trip->status}}</p>
                                            </div>
                                        </div>

                                        <div class="fare-break">

                                            <form method="POST" action="{{url('cancel/ride')}}">
                                                {{ csrf_field() }}
                                                    <input type="hidden" name="request_id" value="{{$trip->id}}" />
                                                <button class="full-primary-btn fare-btn" type="submit">@lang('user.ride_cancel')</button>
                                            </form>
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
    <script>

        function initMap()
        {
            @foreach($trips as $trip)
                var map_{{$trip->id}};
                var directionsService_{{$trip->id}};
                var bounds_{{$trip->id}};
                var directionsDisplay_{{$trip->id}};
                var locations_{{$trip->id}};
                directionsService_{{$trip->id}} = new google.maps.DirectionsService();
                bounds_{{$trip->id}} = new google.maps.LatLngBounds();
                directionsDisplay_{{$trip->id}} = new google.maps.DirectionsRenderer();

                locations_{{$trip->id}} = <?php echo json_encode($trip['coordinate']); ?>;


                map_{{$trip->id}} = new google.maps.Map(document.getElementById('map_{{$trip->id}}'), {
                    zoom: 10,
                    // center: new google.maps.LatLng(40.71277530, -74.00597280),
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                });
                directionsDisplay_{{$trip->id}}.setMap(map_{{$trip->id}});

                var infowindow = new google.maps.InfoWindow();

                var marker, i;
                var request = {
                    travelMode: google.maps.TravelMode.DRIVING
                };

                for (i = 0; i < locations_{{$trip->id}}.length; i++)
                {
                    marker = new google.maps.Marker({
                        position: new google.maps.LatLng(locations_{{$trip->id}}[i][1], locations_{{$trip->id}}[i][2]),
                        icon: '/asset/img/marker-end.png',
                        anchorPoint: new google.maps.Point(0, -29)
                    });

                    google.maps.event.addListener(marker, 'click', (function(marker, i) {
                        return function() {
                            infowindow.setContent(locations_{{$trip->id}}[i][0]);
                            infowindow.open(map_{{$trip->id}}, marker);
                        }
                    })(marker, i));

                    if (i == 0) request.origin = marker.getPosition();
                    else if (i == locations_{{$trip->id}}.length - 1) request.destination = marker.getPosition();
                    else {
                        if (!request.waypoints) request.waypoints = [];
                        request.waypoints.push({
                            location: marker.getPosition(),
                            stopover: true
                        });
                    }
                }

                directionsService_{{$trip->id}}.route(request, function(result, status) {
                    if (status == google.maps.DirectionsStatus.OK) {
                        directionsDisplay_{{$trip->id}}.setDirections(result);
                    }
                });
            @endforeach
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('constants.map_key') }}&libraries=places&callback=initMap"></script>
    <script>
        $("[id*='trip_']").on('hidden.bs.collapse', function () {
            initMap();
        });
        $("[id*='trip_']").on('shown.bs.collapse', function () {
            initMap();
        })
    </script>
@endsection
