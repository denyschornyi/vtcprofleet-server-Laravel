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

                <a href="{{ route('fleet.polygonShape.index') }}" class="btn btn-default pull-right"><i
                            class="fa fa-angle-left"></i> @lang('admin.back')</a>
                <a href="{{ route('fleet.polygonShape.edit', $obj->id) }}" class="btn btn-default pull-right">
                    <i class="fa fa-pencil"></i> @lang('admin.poi.edit')
                </a>
                <h5 style="margin-bottom: 2em;">@lang('admin.poi.show')</h5>

                <form class="form-horizontal" action="{{route('fleet.polygonShape.store')}}" method="POST" role="form">
                    {{ csrf_field() }}
                    <table class="form-table">
                        <tbody id="quickcab_geofence_ignore_surge_pricing_rule_cont">
                        <tr>
                            <th scope="row" style="padding: 20px 0px 20px 17px;width: 15%">
                                <label for="quickcab_geofence_fixed_price_amount" class="col-form-label">Name</label>
                            </th>
                            <td style="padding: 15px 30px;">
                                {{$obj->title}}
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <input id="location_in_airport" name="location_in_airport" type="hidden"  value="0">
                    <input id="zonecordinates" name="zonecordinates[]" type="hidden">
                    <input id="methododel_id" name="model_id" type="hidden">
                    <input id="added_zone_details" name="added_zone_details" type="hidden" value="">
                    <div class="form-group row">
                        <input id="lat" name="lat" type="hidden" value="{{json_decode($obj->coordinate)->lat}}">
                        <input id="lng" name="lng" type="hidden" value="{{json_decode($obj->coordinate)->lng}}">
                        <input id="bound" name="bound" style="display: block;" type="hidden" value="">
                    </div>
                    <div class="form-group row" style="margin-left: 0.25em;">
                        <div class="col-xs-12">
                            <input type="text" name="bound" id="bound" style="display: none;">
                            <div class="new_input_field row" style="width: 100%">
                                <div class="col-md-12 col-xs-12">
                                    <textarea id="geofence_latlng" name="geofence_latlng"
                                              style="display:none;width: 100%;"></textarea>
                                    <h2>Location</h2>
                                    <div id="map" class="map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                 {{--   <div class="form-group row">
                        <div class="col-md-2">
                            <label for="calculator"  class="col-xs-10 col-form-label">@lang('admin.poi.status')</label>
                        </div>
                        <div class="col-md-10" style="margin-top: 9px;">
                            @if($obj->poi_category_id === 1) <span style="color: green;">@lang('admin.poi.active')</span>
                            @elseif($obj->poi_category_id === 0)<span style="color: red;">@lang('admin.poi.inactive')</span>
                            @endif
                        </div>
                    </div>--}}
                    {{--<div class="form-group row">
                        <div class="col-xs-12">
                            <div class="row">
                                <div class="col-xs-6 col-sm-3 col-md-2">
                                    <a href="{{ route('fleet.polygonShape.index') }}" class="btn btn-danger">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>--}}
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
                        "coordinates": {{$obj->shape}},
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

        function call_initialize() {
            initialize();//call first map function
        }

        google.maps.event.addDomListener(window, 'load', call_initialize);
    </script>
@endsection
