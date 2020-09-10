@extends('fleet.layout.base')
@section('styles')
    <style>
        #floating-panel {
            margin-bottom: 15px;
        }

        .mr-15 {
            margin-right: 15px;
        }

        .map {
            width: 100%;
            height: 480px;
            position: relative;
            overflow: hidden;
            background-color: rgb(229, 227, 223);
            margin-top: 20px;
        }

        .form-table {
            width: 100%;
        }

        .form-table th {
            vertical-align: top;
            text-align: left;
            padding: 20px 10px 20px 0;
            /*width: 200px;*/
            line-height: 1.3;
            font-weight: 600;
        }

        .form-table td {
            margin-bottom: 9px;
            padding: 15px 10px;
            line-height: 1.3;
            vertical-align: middle;
        }


        .form-table td p {
            margin-top: 4px;
            margin-bottom: 0;
        }

        input[type=number] {
            height: 28px;
            line-height: 1;
        }

        ul {
            padding: 0px;
            list-style: none;
        }
        @media screen and (max-width: 600px) {
            .mr-15{
                margin-right: 15px;
                margin-bottom: 10px;
            }

            .map{
                height: 350px;
                width: 112%;
                margin-bottom: 10px;
            }
        }
    </style>
@stop
@section('title')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">

                <a href="{{ route('fleet.pointInterest.index') }}" class="btn btn-default pull-right"><i
                            class="fa fa-angle-left"></i> @lang('admin.back')</a>
                <a href="{{ route('fleet.pointInterest.edit', $point_interest->id) }}" class="btn btn-default pull-right">
                    <i class="fa fa-pencil"></i> @lang('admin.poi.edit')
                </a>
                <h5 style="margin-bottom: 2em;">@lang('admin.point.show_Point_Interest')</h5>

                <form class="form-horizontal" action="{{route('fleet.pointInterest.store')}}" method="POST" role="form">
                    {{ csrf_field() }}
                    <table class="form-table">
                        <tbody id="quickcab_geofence_ignore_surge_pricing_rule_cont">
                            <tr>
                                <th scope="row" style="padding: 20px 0px 20px 17px;width: 15%">
                                    <label for="quickcab_geofence_fixed_price_amount" class="col-form-label">Name</label>
                                </th>
                                <td style="padding: 15px 30px;">
                                    {{$point_interest->rule_name}}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <input id="location_in_airport" name="location_in_airport" type="hidden"  value="0">
                    <input id="zonecordinates" name="zonecordinates[]" type="hidden">
                    <input id="methododel_id" name="model_id" type="hidden">
                    <input id="added_zone_details" name="added_zone_details" type="hidden"
                           value="">

                    <div class="form-group row">

                        <input id="lat" name="lat" type="hidden" value="{{json_decode($point_interest->start_coordinate)->lat}}">
                        <input id="lng" name="lng" type="hidden" value="{{json_decode($point_interest->start_coordinate)->lng}}">

                        <input id="lat_dest" name="lat_dest" type="hidden" value="{{json_decode($point_interest->dest_coordinate)->lat}}">
                        <input id="lng_dest" name="lng_dest" type="hidden" value="{{json_decode($point_interest->dest_coordinate)->lng}}">
                        <input id="bound" name="bound" style="display: block;" type="hidden" value="">
                        <input id="bound1" name="bound1" style="display: block;" type="hidden" value="">
                    </div>
                    <div class="form-group row" style="margin-left: 0.25em;">
                        <div class="col-xs-12">
                            <input type="text" name="bound" id="bound" style="display: none;">
                            <div class="new_input_field row" style="width: 100%">
                                <div class="col-md-6 col-xs-12">
                                    <textarea id="geofence_latlng" name="geofence_latlng"
                                              style="display:none;width: 100%;"></textarea>
                                    <h2>Location 1</h2>
                                    <div id="map" class="map"></div>
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    <textarea id="geofence_other_latlng" name="geofence_other_latlng"
                                              style="display:none;width: 100%;"></textarea>
                                    <h2>Location 2</h2>
                                    <div id="map1" class="map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tbody id="quickcab_geofence_ignore_surge_pricing_rule_cont">
                            <tr>
                                <th scope="row">
                                    <label for="quickcab_geofence_ignore_surge_pricing_rule"
                                           class="col-xs-10 col-form-label">Ignore surge price rules</label>
                                </th>
                                <td>
                                    <input name="ignore_surge_pricing_rule" type="checkbox"
                                           id="ignore_surge_pricing_rule" @if($point_interest->ignore_surge_price === 1) checked @endif disabled
                                           class="quickcab__geofence--override-surge-price">
                                    <p class="description">If yes, any surge price increases will be ignored. If no and
                                        a surge pricing rule applies to the journey, the journey will be charged the
                                        fixed price specified + the surge price increase.</p>
                                </td>
                            </tr>
                            </tbody>
                            <tbody id="quickcab_geofence_fixed_price">
                            <tr>
                                <th scope="row">
                                    <label for="quickcab_geofence_fixed_price_amount" class="col-xs-10 col-form-label">Fixed Price</label>
                                </th>
                                <td>
                                    <span class="quickcab-input-prefix">€</span>
                                    <input min="0" step="0.01" value={{$point_interest->price}}   class="medium-text" id="quickcab_geofence_fixed_price_amount"  name="quickcab_geofence_fixed_price_amount" type="number">
                                    <span class="quickcab-input-suffix"></span></td>
                            </tr>
                            </tbody>
                            <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="quickcab_geofence_vehicle_ids"
                                           class="col-xs-10 col-form-label">Vehicle</label>
                                </th>
                                <td>
                                    <ul>
                                        @foreach ($service_type as $val)
                                            <li>
                                                <label>
                                                    <input class="checkbox" type="checkbox" name="vehicle[]"
                                                              value="{{$val->id}}" checked disabled=""
                                                              style="margin-right: 5px;">{{$val->name}}
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <p class="description">Which vehicles do you want this pricing condition to apply
                                        to?</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="quickcab_geofence_direction"
                                           class="col-xs-10 col-form-label">Direction</label>
                                </th>
                                <td>
                                    @if($point_interest->direction_state === 1) <span style="color: green;">Origin =&gt; Destination (One Direction)</span>
                                    @elseif($point_interest->direction_state === 2)<span style="color: red;">Origin &lt;=&gt; Destination (Both Directions)</span>
                                    @endif
                                    <p class="description">Does this rule apply for Location A =&gt; Location B only, or
                                        will it apply to Location B =&gt; Location A as well?</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="calculator"
                                           class="col-xs-10 col-form-label">@lang('admin.poi.status')</label>
                                </th>
                                <td>
                                    @if($point_interest->status === 1) <span style="color: green;">@lang('admin.poi.active')</span>
                                    @elseif($point_interest->status === 0)<span style="color: red;">@lang('admin.poi.inactive')</span>
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group row">
                        <div class="col-xs-12">
                            <div class="row">
                                <div class="col-xs-6 col-sm-3 col-md-2">
                                    <a href="{{ route('fleet.pointInterest.index') }}"
                                       class="btn btn-danger">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;key={{config('constants.map_key')}}&amp;libraries=places&amp;libraries=drawing,places&amp;language=&amp;region=JP"></script>

    <script charset="UTF-8" src="https://maps.googleapis.com/maps-api-v3/api/js/37/10a/map.js"
            type="text/javascript"></script>
    <script charset="UTF-8" src="https://maps.googleapis.com/maps-api-v3/api/js/37/10a/marker.js"
            type="text/javascript"></script>
    <script charset="UTF-8" src="https://maps.googleapis.com/maps-api-v3/api/js/37/10a/geometry.js"
            type="text/javascript"></script>
    <script charset="UTF-8" src="https://maps.googleapis.com/maps-api-v3/api/js/37/10a/directions.js"
            type="text/javascript"></script>
    <script charset="UTF-8" src="https://maps.googleapis.com/maps-api-v3/api/js/37/10a/drawing_impl.js"
            type="text/javascript"></script>
    <script charset="UTF-8" src="https://maps.googleapis.com/maps-api-v3/api/js/37/10a/onion.js"
            type="text/javascript"></script>
    <script charset="UTF-8" src="https://maps.googleapis.com/maps-api-v3/api/js/37/10a/infowindow.js"
            type="text/javascript"></script>

    <script type="text/javascript" src="{{asset('main/assets/js/jsts.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/assets/js/jquery.validate.min.js')}}"></script>


    <script type="text/javascript">

        var poly_values = [];
        var drawingManager;
        var selectedShape;
        var drawingManager1;

        $('#model_id').val("");
        $('#added_zone_details').val("");

        function clearSelection() {
            if (selectedShape) {
                selectedShape.setEditable(false);
                selectedShape = null;
            }
        }

        function setSelection(shape) {
            selectedShape = shape;
            shape.setEditable(true);
            shape.setDraggable(true);
        }

        function deleteSelectedShape() {
            document.getElementById('geofence_latlng').innerHTML = "";

            if (selectedShape) {
                selectedShape.setMap(null);

                drawingManager.setOptions({
                    drawingControl: true
                });
            }
        }

        function removePolygon() {
            document.getElementById('geofence_latlng').innerHTML = "";
            initialize();
            // $('#remove_polygon').hide();
        }

        function removePolygon1() {
            document.getElementById('geofence_other_latlng').innerHTML = "";
            initialize_destination();
        }

        function get_polygon_cordinates(polygon) {

            document.getElementById('geofence_latlng').innerHTML = "";
            var bounds = new google.maps.LatLngBounds();

            var aa = [];
            for (var i = 0; i < polygon.getPath().getLength(); i++) {
                var text_value = polygon.getPath().getAt(i).toUrlValue(14);

                coordinates = [];
                arr = text_value.split(",");

                coordinates.push(parseFloat(arr[1]));
                coordinates.push(parseFloat(arr[0]));
                aa.push(coordinates);

                text_value = text_value.replace(',', ':');
                lat_lng = text_value.split(':');

                bounds.extend(new google.maps.LatLng(lat_lng[0], lat_lng[1]));

                poly_values[i] = text_value;
                geo_val = arr[1] + ':' + arr[0];

                document.getElementById('geofence_latlng').innerHTML += geo_val + ",";
            }
            $('#zone_lat_long').val(JSON.stringify(aa));

            var center = bounds.getCenter();
            document.getElementById('bound').value = center;
        }

        function get_polygon_cordinates_destination(polygon) {

            document.getElementById('geofence_other_latlng').innerHTML = "";
            var bounds = new google.maps.LatLngBounds();

            var aa = [];
            for (var i = 0; i < polygon.getPath().getLength(); i++) {
                var text_value = polygon.getPath().getAt(i).toUrlValue(14);

                coordinates = [];
                arr = text_value.split(",");

                coordinates.push(parseFloat(arr[1]));
                coordinates.push(parseFloat(arr[0]));
                aa.push(coordinates);

                text_value = text_value.replace(',', ':');
                lat_lng = text_value.split(':');

                bounds.extend(new google.maps.LatLng(lat_lng[0], lat_lng[1]));

                poly_values[i] = text_value;
                geo_val = arr[1] + ':' + arr[0];

                document.getElementById('geofence_other_latlng').innerHTML += geo_val + ",";
            }
            $('#zone_lat_long').val(JSON.stringify(aa));

            var center = bounds.getCenter();
            document.getElementById('bound1').value = center;
        }

        //initialize original map
        function initialize() {
            var lt = $("#lat").val();
            var ln = $("#lng").val();
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 14,
                center: new google.maps.LatLng(lt, ln),
                disableDefaultUI: true,
                zoomControl: true,
                draggable: true,
                fullscreenControl: true,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });

            directionsDisplay = new google.maps.DirectionsRenderer();

            marker = new google.maps.Marker({
                position: new google.maps.LatLng(lt, ln),
                title: 'Location',
                map: map,
                draggable: false
            });

            directionsDisplay.setMap(map);


            map.data.addGeoJson({
                "type": "FeatureCollection", "features": [{
                    "type": "Feature", "properties": {"title": "rewqwr"}, "geometry": {
                        "coordinates": {{$point_interest->start_mapdata}},
                        "type": "Polygon"
                    }
                }]
            });
            map.data.setStyle(function (feature) {
                return ({
                    strokeColor: 'black',
                    fillColor: 'black',
                    strokeWeight: 2,
                    fillOpacity: 0.50,
                    strokeOpacity: 0.8,
                    draggable: false,
                });
            });

            drawingManager = new google.maps.drawing.DrawingManager({
               /* drawingMode: google.maps.drawing.OverlayType.POLYGON,
                draggable: false,
                drawingControlOptions: {
                    draggable: false,
                    drawingModes: [
                        // google.maps.drawing.OverlayType.POLYGON
                    ]
                },
                circleOptions: {
                    fillColor: '#ffff00',
                    fillOpacity: 1,
                    strokeWeight: 5,
                    clickable: false,
                    zIndex: 1
                },
                map: map*/
            });

            //to convert google map to JTS graph using jsts.min.js
            var googleMaps2JTS = function (boundaries) {

                var coordinates = [];
                for (var i = 0; i < boundaries.getLength(); i++) {
                    coordinates.push(new jsts.geom.Coordinate(
                        boundaries.getAt(i).lat(), boundaries.getAt(i).lng()));
                }
                coordinates.push(coordinates[0]);
                console.log(coordinates);
                return coordinates;
            };

            //to find whether the polygon line intersect each other
            var findSelfIntersects = function (googlePolygonPath) {
                var coordinates = googleMaps2JTS(googlePolygonPath);
                var geometryFactory = new jsts.geom.GeometryFactory();
                var shell = geometryFactory.createLinearRing(coordinates);
                var jstsPolygon = geometryFactory.createPolygon(shell);

                // if the geometry is aleady a simple linear ring, do not
                // try to find self intersection points.
                var validator = new jsts.operation.IsSimpleOp(jstsPolygon);
                if (validator.isSimpleLinearGeometry(jstsPolygon)) {
                    return;
                }

                var res = [];
                var graph = new jsts.geomgraph.GeometryGraph(0, jstsPolygon);
                var cat = new jsts.operation.valid.ConsistentAreaTester(graph);
                var r = cat.isNodeConsistentArea();
                if (!r) {
                    var pt = cat.getInvalidPoint();

                    if (isNaN(pt.x) || isNaN(pt.y)) {
                        return res;
                    } else if (pt.x == 0 || pt.y == 0) {
                        return res;
                    } else {
                        res.push([pt.x, pt.y]);
                    }


                }
                return res;
            };

            google.maps.event.addListener(drawingManager, 'polygoncomplete', function (polygon) {
                var intersects = findSelfIntersects(polygon.getPath());
                // console.log(intersects);
                if (intersects && intersects.length) {
                    alert('Polygon cannot intersects itself');
                    removePolygon();
                    return false;
                }

                drawingManager.setDrawingMode(null);

                drawingManager.setOptions({
                    drawingControl: false,
                });
                get_polygon_cordinates(polygon);

                polygon.getPaths().forEach(function (path, index) {

                    google.maps.event.addListener(path, 'insert_at', function () {
                        // New point
                        get_polygon_cordinates(polygon);
                    });

                    //to enable the save shape button after the polygon has drawn
                    $('#save_shape').attr('disabled', false);

                    google.maps.event.addListener(path, 'remove_at', function () {
                        // Point was removed
                        get_polygon_cordinates(polygon);
                    });

                    google.maps.event.addListener(path, 'set_at', function () {
                        // Point was moved
                        get_polygon_cordinates(polygon);
                    });
                });

                google.maps.event.addListener(polygon, 'dragend', function () {
                    get_polygon_cordinates(polygon);
                });

            });

            google.maps.event.addListener(drawingManager, 'overlaycomplete', function (e) {
                // Add an event listener that selects the newly-drawn shape when the user
                // mouses down on it.
                var newShape = e.overlay;
                newShape.type = e.type;

                google.maps.event.addListener(newShape, 'click', function () {
                    setSelection(newShape);
                });
                setSelection(newShape);
            });

        }

        function initialize_destination() {
            var lt = $("#lat_dest").val();
            var ln = $("#lng_dest").val();

            var map1 = new google.maps.Map(document.getElementById('map1'), {
                zoom: 14,
                center: new google.maps.LatLng(lt, ln),
                disableDefaultUI: true,
                zoomControl: true,
                draggable: true,
                fullscreenControl: true,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });

            directionsDisplay = new google.maps.DirectionsRenderer();

            marker = new google.maps.Marker({
                position: new google.maps.LatLng(lt, ln),
                title: 'Location2',
                map: map1,
                draggable: false
            });

            directionsDisplay.setMap(map1);

            map1.data.addGeoJson({
                "type": "FeatureCollection", "features": [{
                    "type": "Feature", "properties": {"title": "rewqwr"}, "geometry": {
                        "coordinates": {{$point_interest->dest_mapdata}},
                        "type": "Polygon"
                    }
                }]
            });

            map1.data.setStyle(function (feature) {
                return ({
                    strokeColor: 'black',
                    fillColor: 'black',
                    strokeWeight: 2,
                    fillOpacity: 0.50,
                    strokeOpacity: 0.8,
                    draggable: false,
                });
            });

            drawingManager1 = new google.maps.drawing.DrawingManager({
                /*drawingMode: google.maps.drawing.OverlayType.POLYGON,
                draggable: true,
                drawingControlOptions: {
                    draggable: true,
                    drawingModes: [
                        google.maps.drawing.OverlayType.POLYGON
                    ]
                },
                circleOptions: {
                    fillColor: '#ffff00',
                    fillOpacity: 1,
                    strokeWeight: 5,
                    clickable: false,
                    editable: true,
                    zIndex: 1
                },
                map: map1*/
            });

            //to convert google map to JTS graph using jsts.min.js
            var googleMaps2JTS = function (boundaries) {

                var coordinates = [];
                for (var i = 0; i < boundaries.getLength(); i++) {
                    coordinates.push(new jsts.geom.Coordinate(
                        boundaries.getAt(i).lat(), boundaries.getAt(i).lng()));
                }
                coordinates.push(coordinates[0]);
                console.log(coordinates);
                return coordinates;
            };

            //to find whether the polygon line intersect each other
            var findSelfIntersects = function (googlePolygonPath) {
                var coordinates = googleMaps2JTS(googlePolygonPath);
                var geometryFactory = new jsts.geom.GeometryFactory();
                var shell = geometryFactory.createLinearRing(coordinates);
                var jstsPolygon = geometryFactory.createPolygon(shell);

                // if the geometry is aleady a simple linear ring, do not
                // try to find self intersection points.
                var validator = new jsts.operation.IsSimpleOp(jstsPolygon);
                if (validator.isSimpleLinearGeometry(jstsPolygon)) {
                    return;
                }

                var res = [];
                var graph = new jsts.geomgraph.GeometryGraph(0, jstsPolygon);
                var cat = new jsts.operation.valid.ConsistentAreaTester(graph);
                var r = cat.isNodeConsistentArea();
                if (!r) {
                    var pt = cat.getInvalidPoint();

                    if (isNaN(pt.x) || isNaN(pt.y)) {
                        return res;
                    } else if (pt.x == 0 || pt.y == 0) {
                        return res;
                    } else {
                        res.push([pt.x, pt.y]);
                    }
                }
                return res;
            };

            google.maps.event.addListener(drawingManager1, 'polygoncomplete', function (polygon) {
                var intersects = findSelfIntersects(polygon.getPath());
                // console.log(intersects);
                if (intersects && intersects.length) {
                    alert('Polygon cannot intersects itself');
                    removePolygon1();
                    return false;
                }

                drawingManager1.setDrawingMode(null);

                drawingManager1.setOptions({
                    drawingControl: false,
                });
                get_polygon_cordinates_destination(polygon);

                polygon.getPaths().forEach(function (path, index) {

                    google.maps.event.addListener(path, 'insert_at', function () {
                        // New point
                        get_polygon_cordinates_destination(polygon);
                    });
                    //to enable the save shape button after the polygon has drawn
                    $('#save_shape_1').attr('disabled', false);

                    google.maps.event.addListener(path, 'remove_at', function () {
                        // Point was removed
                        get_polygon_cordinates_destination(polygon);
                    });

                    google.maps.event.addListener(path, 'set_at', function () {
                        // Point was moved
                        get_polygon_cordinates_destination(polygon);
                    });
                });

                google.maps.event.addListener(polygon, 'dragend', function () {
                    get_polygon_cordinates_destination(polygon);
                });

            });

            google.maps.event.addListener(drawingManager1, 'overlaycomplete', function (e) {
                // Add an event listener that selects the newly-drawn shape when the user
                // mouses down on it.
                var newShape = e.overlay;
                newShape.type = e.type;

                google.maps.event.addListener(newShape, 'click', function () {
                    setSelection(newShape);
                });
                setSelection(newShape);
            });

        }

        function call_initialize() {
            initialize();//call first map function
            initialize_destination();
        }

        google.maps.event.addDomListener(window, 'load', call_initialize);

    </script>
@endsection
