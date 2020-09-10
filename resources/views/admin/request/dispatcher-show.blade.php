@extends('dispatcher.layout.dispatcher_base')
@section("title","Ride Details ")

@section("css")
<style>
    #map {
        height: 450px;
    }
</style>
@endsection

@section('content')
    <div class="content-body">
        <section id="column-selectors">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1 col-md-6 pl-0">@lang('admin.request.request_details')</h4>
                            <a href="#" id="back_btn" class="btn btn-default pull-right" style="color: blue;">
                                <i class="fa fa-angle-left"></i> @lang('admin.back')
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">@lang('admin.request.Booking_ID') :</dt>
                                        <dd class="col-sm-8">{{ $request->booking_id }}</dd>

                                        <dt class="col-sm-4">@lang('admin.request.User_Name') :</dt>
                                        <dd class="col-sm-8">
                                            @if ($request->user->company_name =='')
                                                {{ $request->user->first_name }}
                                            @else
                                                {{ $request->user->company_name }}
                                            @endif
                                        </dd>
                                        @if ($request->user->company_name !='')
                                            <dt class="col-sm-4">@lang('admin.custom.user_passname') :</dt>
                                            <dd class="col-sm-8">
                                                {{$request->user->first_name}} {{$request->user->last_name}}
                                            </dd>
                                        @endif
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
                                        @endif
                                        <dt class="col-sm-4">@lang('admin.request.ride_return_time') :</dt>
                                        <dd class="col-sm-8">
                                            @if($request->schedule_return_at != "")
                                                {{ appDateTime($request->schedule_return_at) }}
                                            @else
                                                -
                                            @endif
                                        </dd>
                                        <dt class="col-sm-4">@lang('admin.request.pickup_address') :</dt>
                                        <dd class="col-sm-8">{{ $request->s_address ? $request->s_address : '-' }}</dd>

                                        <dt class="col-sm-4">@lang('admin.request.waypoints') :</dt>
                                        <dd class="col-sm-8">
                                            {{ $request->way_points ? \App\UserRequests::getWayPointAddress($request->way_points) : '-' }}
                                        </dd>

                                        <dt class="col-sm-4">@lang('admin.request.drop_address') :</dt>
                                        <dd class="col-sm-8">{{ $request->d_address ? $request->d_address : '-' }}</dd>

                                        <dt class="col-sm-4">@lang('admin.request.comments') :</dt>
                                        <dd class="col-sm-8">{{ $request->comment ? $request->comment : '-' }}</dd>

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
            </div>
        </section>
    </div>
@endsection
@section('script')
<script type="text/javascript">

    $('#back_btn').on('click', function(e) {
        window.history.back();
    });

    function initMap()
    {

        var zoomLevel = 6;
        var geocoder;
        var map;
        var directionsDisplay;
        var directionsService = new google.maps.DirectionsService();
        var bounds = new google.maps.LatLngBounds();
        // var locations = [
        //     [0, 40.71277530, -74.00597280],
        //     [0, 40.6022939,-75.4714098],
        //     [0, 40.0378755,-76.30551439999999],
        //     [0, 38.9071923, -77.03687070000001]
        // ];
        var locations = <?php echo json_encode($request['coordinate']); ?>;
        console.log(locations);
        directionsDisplay = new google.maps.DirectionsRenderer();

        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 10,
            center: new google.maps.LatLng(40.71277530, -74.00597280),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        directionsDisplay.setMap(map);
        var infowindow = new google.maps.InfoWindow();

        var marker, i;
        var request = {
            travelMode: google.maps.TravelMode.DRIVING
        };

        for (i = 0; i < locations.length; i++) {
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                icon: '/asset/img/marker-end.png',
                anchorPoint: new google.maps.Point(0, -29)
            });

            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    infowindow.setContent(locations[i][0]);
                    infowindow.open(map, marker);
                }
            })(marker, i));

            if (i == 0) request.origin = marker.getPosition();
            else if (i == locations.length - 1) request.destination = marker.getPosition();
            else {
                if (!request.waypoints) request.waypoints = [];
                request.waypoints.push({
                    location: marker.getPosition(),
                    stopover: true
                });
            }
        }
        directionsService.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);
            }
        });

{{--        @if($request->provider && $request->status != 'COMPLETED')--}}
{{--        var markerProvider = new google.maps.Marker({--}}
{{--            map: map,--}}
{{--            icon: "/asset/img/marker-car.png",--}}
{{--            anchorPoint: new google.maps.Point(0, -29)--}}
{{--        });--}}

        {{--provider = new google.maps.LatLng({{ $request->provider->latitude }}, {{ $request->provider->longitude }});--}}
        {{--markerProvider.setVisible(true);--}}
        {{--markerProvider.setPosition(provider);--}}
        {{--console.log('Provider Bounds', markerProvider.getPosition());--}}
        {{--bounds.extend(markerProvider.getPosition());--}}
        {{--@endif--}}

        {{--bounds.extend(marker.getPosition());--}}
        {{--bounds.extend(marker.getPosition());--}}
        {{--map.fitBounds(bounds);--}}
    }

</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('constants.map_key') }}&libraries=places&callback=initMap"></script>
@endsection
