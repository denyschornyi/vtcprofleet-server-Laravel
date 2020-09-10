@extends('admin.layout.base')
@section('styles')
    <style>
        #floating-panel {
            margin-bottom: 15px;
        }

        .mr-15{
            margin-right: 15px;
            margin-bottom: 10px;
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
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
@stop
@section('title', 'Add Point Interest ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <a href="{{ route('admin.pointinterest.index') }}" class="btn btn-default pull-right"><i
                            class="fa fa-angle-left"></i> @lang('admin.back')</a>

                <h5 style="margin-bottom: 2em;">@lang('admin.point.Add_Point_Interest')</h5>

                <form class="form-horizontal" action="{{route('admin.pointinterest.store')}}" method="POST" role="form" onsubmit="return validateGeofence();">
                    {{ csrf_field() }}
                    <table class="form-table">
                        <tbody id="quickcab_geofence_ignore_surge_pricing_rule_cont">
                            <tr>
                                <th scope="row" style="padding: 20px 0px 20px 17px;width: 15%">
                                    <label for="quickcab_geofence_fixed_price_amount" class="col-form-label">Name</label>
                                </th>
                                <td style="padding: 15px 30px;">
                                    <input class="form-control" type="text" value="{{ old('type') }}" name="type" required style="width: 60%;"
                                           id="type" placeholder=@lang('admin.point.geofence_rule_name')>
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
                        <input id="lat" name="lat" type="hidden" value="48.85661400000001">
                        <input id="lng" name="lng" type="hidden" value="2.3522219000000177">

                        <input id="lat_dest" name="lat_dest" type="hidden" value="48.85661400000001">
                        <input id="lng_dest" name="lng_dest" type="hidden" value="2.3522219000000177">
                        <input id="bound" name="bound"  type="hidden" value="">
                        <input id="bound1" name="bound1"  type="hidden" value="">
                    </div>
                    <div class="form-group row" style="margin-left: 0.25em;">
                        <div class="col-xs-12">

                            <div class="new_input_field row" style="width: 100%">
                                <div class="col-md-6 col-xs-12">
                                    <textarea id="geofence_latlng" name="geofence_latlng"  style="display:none;width: 100%;"></textarea>
                                    <textarea id="geofence_latlng_dispatcher" name="geofence_latlng_dispatcher"  style="display:none;width: 100%;"></textarea>
                                    <h2>Location 1</h2>
                                    <div class="form-group">
                                        <input class="form-control" type="text" title="Enter the zone address"
                                               value="{{ old('zone_address') }}" name="zone_address"  id="zone_address"
                                               placeholder="Enter a location" autocomplete="off">
                                    </div>
                                    <div id="floating-panel">
                                        <button type="button" class="btn btn-danger mr-15 col-lg-3 col-md-5 col-sm-5 col-xs-12" onclick="removePolygon();"
                                                title="Remove polygon" data-toggle="tooltip"
                                                name="remove_polygon" id="remove_polygon">Remove polygon
                                        </button>
                                        <button type="button" class="btn btn-success mr-15 col-lg-2 col-md-5 col-sm-5 col-xs-12" title="Save Shape"
                                                data-toggle="tooltip"
                                                name="save_shape" id="save_shape" onclick="shapeSave();"
                                                style="margin-right: 15px;">Save Shape
                                        </button>
                                        <button type="button" class="btn btn-primary mr-15 col-lg-3 col-md-5 col-sm-5 col-xs-12" onclick="shapeImport();"
                                                title="Import Shape" data-toggle="tooltip"
                                                name="import_shape" id="import_shape">Import Shape
                                        </button>
                                        <button type="button" class="btn btn-primary mr-15 col-lg-2 col-md-5 col-sm-5 col-xs-12" onclick="poiCategory()"
                                                title="POI Category" data-toggle="tooltip"
                                                name="poi_category"
                                                id="poi_category">@lang('admin.poi.poi_category')</button>
                                    </div>
                                    <div class="row" id="shape_save_area" style="display: none;">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="shape_name"
                                                                     id="shape_name" placeholder="Shape Name">
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-primary mr-15" title="Save"
                                                    data-toggle="tooltip" name="shape_save" id="shape_save" onclick="shapeSaveVal(1);">Save
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row" id="shape_import_area" style="display: none;">
                                        <div class="col-md-4 col-xs-8">
                                            <select class="form-control" id="import_shape_val" name="import_shape_val"
                                                    style="padding: 6px;">
                                                {{--  <option disabled="" selected="" value="0">Select a shape...</option>--}}
                                                @foreach ($shape_data as $val)
                                                    <option value="{{$val->id}}">{{$val->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 col-xs-4">
                                            <button type="button" class="btn btn-primary mr-15" title="Import"
                                                    data-toggle="tooltip" name="shape_import" id="shape_import"
                                                    onclick="import_shape_area(1);">Import
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row" id="poi_category_area" style="display: none;">
                                        <div class="col-md-4">
                                            <select class="form-control" id="poi_category_val" name="poi_category_val"
                                                    style="padding: 6px;">
                                                @foreach ($poi_category as $val)
                                                    <option value="{{$val->id}}">{{$val->type}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div id="map" class="map"></div>
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    <textarea id="geofence_other_latlng" name="geofence_other_latlng" style="display:none;width: 100%;"></textarea>
                                    <textarea id="geofence_other_latlng_dispatcher" name="geofence_other_latlng_dispatcher" style="display:none;width: 100%;"></textarea>
                                    <h2>Location 2</h2>
                                    <div class="form-group">
                                        <input class="form-control" type="text" title="Enter the zone address"
                                               value="{{ old('zone_address1') }}" name="zone_address1"
                                               id="zone_address1"
                                               placeholder="Enter a location" autocomplete="off">
                                    </div>
                                    <div id="floating-panel">
                                        <button type="button" class="btn btn-danger mr-15 col-lg-3 col-md-5 col-sm-5 col-xs-12 col-sm-5" onclick="removePolygon1();"
                                                title="Remove polygon" data-toggle="tooltip"
                                                name="remove_polygon" id="remove_polygon">Remove polygon
                                        </button>
                                        <button type="button" class="btn btn-success mr-15 col-lg-2 col-md-5 col-sm-5 col-xs-12 col-sm-5" title="Save Shape"
                                                data-toggle="tooltip"
                                                name="save_shape_1" id="save_shape_1" onclick="shapeSave1();">Save Shape
                                        </button>
                                        <button type="button" class="btn btn-primary mr-15 col-lg-3 col-md-5 col-sm-5 col-xs-12 col-sm-5" onclick="shapeImport1();"
                                                title="Import Shape" data-toggle="tooltip"
                                                name="import_shape" id="import_shape">Import Shape
                                        </button>
                                        <button type="button" class="btn btn-primary mr-15 col-lg-2 col-md-5 col-sm-5 col-xs-12 col-sm-5" onclick="poiCategory1();"
                                                title="POI Category" data-toggle="tooltip"
                                                name="poi_category"
                                                id="poi_category">@lang('admin.poi.poi_category')</button>
                                    </div>
                                    <div class="row" id="shape_save_area_1" style="display: none;">
                                        <div class="col-md-4 col-xs-8">
                                            <input type="text" class="form-control" name="shape_name1"
                                                                     id="shape_name1" placeholder="Shape Name">
                                        </div>
                                        <div class="col-md-4 col-xs-4">
                                            <button type="button" class="btn btn-primary mr-15" title="Save"
                                                    data-toggle="tooltip" name="shape_save" id="shape_save" onclick="shapeSaveVal(2);">Save
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row" id="shape_import_area_1" style="display: none;">
                                        <div class="col-md-4 col-xs-8">
                                            <select class="form-control" id="import_shape_val_1" name="import_shape_val_1"
                                                    style="padding: 6px;">
{{--                                                <option disabled="" selected="">Select a shape...</option>--}}
                                                @foreach ($shape_data as $val)
                                                    <option value="{{$val->id}}">{{$val->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 col-xs-4">
                                            <button type="button" class="btn btn-primary mr-15" title="Import" onclick="import_shape_area(2);"
                                                    data-toggle="tooltip" name="shape_import1" id="shape_import1">Import
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row" id="poi_category_area_1" style="display: none;">
                                        <div class="col-md-4">
                                            <select class="form-control" id="poi_category_val1" name="poi_category_val1"
                                                    style="padding: 6px;">
                                                @foreach ($poi_category as $val)
                                                    <option value="{{$val->id}}">{{$val->type}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
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
                                           id="ignore_surge_pricing_rule" value="1"
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
                                    <input min="0" step="0.01" value="15.00"   class="medium-text" id="quickcab_geofence_fixed_price_amount"  name="quickcab_geofence_fixed_price_amount" type="number">
                                    <span class="quickcab-input-prefix">€</span>
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
                                        <li><label><input type="checkbox" id="select_all" style="margin-right: 5px;"/>
                                                All Vehicles</label></li>
                                        @foreach ($service_type as $val)
                                            <li><label><input class="checkbox" type="checkbox" name="vehicle[]"
                                                              value="{{$val->id}}"
                                                              style="margin-right: 5px;">{{$val->name}}</label></li>
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
                                    <select class="form-control" name="quickcab_geofence_direction"
                                            id="quickcab_geofence_direction" style="padding: 6px;">
                                        <option value="1" selected="">Origin =&gt; Destination (One Direction)
                                        </option>
                                        <option value="2">Origin &lt;=&gt; Destination (Both Directions)
                                        </option>
                                    </select>
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
                                    <select class="form-control" id="status" name="status" style="padding: 6px;">
                                        <option value="1">@lang('admin.poi.active')</option>
                                        <option value="0">@lang('admin.poi.inactive')</option>
                                    </select>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group row">
                        <div class="col-xs-12">
                            <div class="row">
                                <div class="col-xs-6 col-sm-3 col-md-2">
                                    <button type="submit"
                                            class="btn btn-primary">@lang('admin.point.Add_Point_Interest')</button>
                                </div>
                                <div class="col-xs-6 col-sm-3 col-md-2">
                                    <a href="{{ route('admin.pointinterest.index') }}"
                                       class="btn btn-danger">@lang('admin.cancel')</a>
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
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;key={{config('constants.map_key')}}&amp;libraries=places&amp;libraries=drawing,places&amp;language=&amp;region=JP" ></script>

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
            document.getElementById('geofence_latlng_dispatcher').innerHTML = "";

            if (selectedShape) {
                selectedShape.setMap(null);

                drawingManager.setOptions({
                    drawingControl: true
                });
            }
        }

        function removePolygon() {
            document.getElementById('geofence_latlng').innerHTML = "";
            document.getElementById('geofence_latlng_dispatcher').innerHTML = "";
            initialize();
            // $('#remove_polygon').hide();
        }

        function removePolygon1() {
            document.getElementById('geofence_other_latlng').innerHTML = "";
            document.getElementById('geofence_other_latlng_dispatcher').innerHTML = "";
            initialize_destination();
        }

        function get_polygon_cordinates(polygon)
        {
            document.getElementById('geofence_latlng').innerHTML = "";
            document.getElementById('geofence_latlng_dispatcher').innerHTML = "";
            var bounds = new google.maps.LatLngBounds();

            var aa = [];
            for (var i = 0; i < polygon.getPath().getLength(); i++)
            {
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
                geo_val_latlng_dispatcher =  arr[0] + ':' + arr[1];

                document.getElementById('geofence_latlng').innerHTML += geo_val + ",";
                document.getElementById('geofence_latlng_dispatcher').innerHTML += geo_val_latlng_dispatcher + ",";
            }
            $('#zone_lat_long').val(JSON.stringify(aa));

            var center = bounds.getCenter();
            document.getElementById('bound').value = center;
        }

        function get_polygon_cordinates_destination(polygon)
        {
            document.getElementById('geofence_other_latlng').innerHTML = "";
            document.getElementById('geofence_other_latlng_dispatcher').innerHTML = "";
            var bounds = new google.maps.LatLngBounds();

            var aa = [];
            var geo_val = "" , geo_val_latlng_dispatcher = "";
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
                geo_val_latlng_dispatcher = arr[0] + ':' + arr[1];

                document.getElementById('geofence_other_latlng').innerHTML += geo_val + ",";
                document.getElementById('geofence_other_latlng_dispatcher').innerHTML += geo_val_latlng_dispatcher + ",";
            }
            $('#zone_lat_long').val(JSON.stringify(aa));

            var center = bounds.getCenter();
            document.getElementById('bound1').value = center;
        }

        //initialize original map
        function initialize(polygonval = '') {
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

// console.log({"type":"FeatureCollection","features":[]});
            if (polygonval.type === 'undefined') {
                map.data.addGeoJson({"type": "FeatureCollection", "features": []});
                map.data.setStyle(function (feature) {
                    return ({
                        strokeColor: 'balck',
                        fillColor: 'black',
                        strokeWeight: 2,
                        fillOpacity: 0.50,
                        strokeOpacity: 0.8,
                        draggable: false,
                    });
                });
            } else if (polygonval.type === 'FeatureCollection') {
                map.data.addGeoJson(polygonval);

                map.data.addListener('setgeometry', function(e)
                {
                    get_polygon_cordinates_from_data_layer(e.newGeometry,1);
                });

                map.data.setStyle(function (feature) {
                    return ({
                        strokeColor: 'black',
                        fillColor: 'black',
                        strokeWeight: 2,
                        fillOpacity: 0.50,
                        strokeOpacity: 0.8,
                        draggable: false,
                        editable: true,
                    });
                });
            }
            drawingManager = new google.maps.drawing.DrawingManager({
                drawingMode: google.maps.drawing.OverlayType.POLYGON,
                draggable: true,
                drawingControlOptions: {
                    position: google.maps.ControlPosition.TOP_CENTER,
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
                map: map
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

        function get_polygon_cordinates_from_data_layer(geom,map) {
            if (map === 1)  {
                document.getElementById('geofence_latlng').innerHTML = "";
                document.getElementById('geofence_latlng_dispatcher').innerHTML = "";
            }
            else if (map === 2)  {
                document.getElementById('geofence_other_latlng').innerHTML = "";
                document.getElementById('geofence_other_latlng_dispatcher').innerHTML = "";
            }
            var aa = [];
            var output = "";
            var bounds = new google.maps.LatLngBounds();
            geom.forEachLatLng(function(l)
            {
                coordinates = [];
                lat = l.lat();
                lng = l.lng();
                output += lng+":"+lat+",";
                bounds.extend(new google.maps.LatLng(lat, lng));

                coordinates.push(lng);
                coordinates.push(lat);
                aa.push(coordinates);

            });
            if(map === 1)
            {
                document.getElementById('geofence_latlng').innerHTML = output;
                setLatLngDispatcher(1,output);
            }
            else if(map === 2)
            {
                document.getElementById('geofence_other_latlng').innerHTML = output;
                setLatLngDispatcher(2,output);
            }

            $('#zone_lat_long').val(JSON.stringify(aa));

            var center = bounds.getCenter();
            if(map === 1)
                document.getElementById('bound').value = center;
            else if(map === 2)
                document.getElementById('bound1').value = center;
        }

        function initialize_destination(polygonval = '') {
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

            if (polygonval.type === 'undefined') {
                map1.data.addGeoJson({"type": "FeatureCollection", "features": []});
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
            } else if (polygonval.type === 'FeatureCollection') {
                console.log(polygonval);
                map1.data.addGeoJson(polygonval);

                map1.data.addListener('setgeometry', function(e)
                {
                    get_polygon_cordinates_from_data_layer(e.newGeometry,2);
                });

                map1.data.setStyle(function (feature) {
                    return ({
                        strokeColor: 'black',
                        fillColor: 'black',
                        strokeWeight: 2,
                        fillOpacity: 0.50,
                        strokeOpacity: 0.8,
                        draggable: false,
                        editable: true,
                    });
                });
            }

            drawingManager1 = new google.maps.drawing.DrawingManager({
                drawingMode: google.maps.drawing.OverlayType.POLYGON,
                draggable: true,
                drawingControlOptions: {
                    position: google.maps.ControlPosition.TOP_CENTER,
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
                map: map1
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

        var autocomplete = new google.maps.places.Autocomplete(document.getElementById('zone_address'));

        google.maps.event.addListener(autocomplete, "place_changed", function () {
            $('#location_in_airport').val(0);

            var place = autocomplete.getPlace();

            $("#lat").val(place.geometry.location.lat());
            $("#lng").val(place.geometry.location.lng());
            //set_marker_position(place.geometry.location.lat(),place.geometry.location.lng())
            removePolygon();

            var zone_address_val = ($('#zone_address').val()).toLowerCase();

            if (Object.values(place.types).indexOf('airport') > -1 || zone_address_val.indexOf("airport") != -1) {
                var result = confirm("Do you want to mark this address as airport?");
                if (result) {
                    $('#location_in_airport').val(1);
                    drawingManager.setDrawingMode(null);
                    drawingManager.setOptions({
                        drawingControl: false,
                    });
                }
            }
        });

        var autocomplete1 = new google.maps.places.Autocomplete(document.getElementById('zone_address1'));

        google.maps.event.addListener(autocomplete1, "place_changed", function () {
            var place = autocomplete1.getPlace();

            $("#lat_dest").val(place.geometry.location.lat());
            $("#lng_dest").val(place.geometry.location.lng());
            //set_marker_position(place.geometry.location.lat(),place.geometry.location.lng())
            removePolygon1();

            var zone_address_val = ($('#zone_address1').val()).toLowerCase();

            if (Object.values(place.types).indexOf('airport') > -1 || zone_address_val.indexOf("airport") != -1) {
                var result = confirm("Do you want to mark this address as airport?");
                if (result) {
                    $('#location_in_airport').val(1);
                    drawingManager.setDrawingMode(null);
                    drawingManager.setOptions({
                        drawingControl: false,
                    });
                }
            }
        });

        $(document).ready(function () {
            $('#save_shape').attr('disabled', true);
            $('#save_shape_1').attr('disabled', true);

            $("#zone_address").keypress(function (e) {
                return e.keyCode != 13;
            });
        });
        var shape_save_area = $("#shape_save_area");
        var shape_save_area1 = $("#shape_save_area_1");
        var shape_import_area = $("#shape_import_area");
        var shape_import_area1 = $("#shape_import_area_1");
        var poi_category_area = $("#poi_category_area");
        var poi_category_area1 = $("#poi_category_area_1");

        function shapeSave() {
            shape_save_area.css('display', 'block');
            shape_import_area.css('display', 'none');
            poi_category_area.css('display', 'none');
        }

        function shapeSave1() {
            shape_save_area1.css('display', 'block');
            shape_import_area1.css('display', 'none');
            poi_category_area1.css('display', 'none');
        }

        function shapeImport() {
            shape_import_area.css('display', 'block');
            shape_save_area.css('display', 'none');
            poi_category_area.css('display', 'none');
        }

        function shapeImport1() {
            shape_import_area1.css('display', 'block');
            shape_save_area1.css('display', 'none');
            poi_category_area1.css('display', 'none');
        }

        function poiCategory() {
            poi_category_area.css('display', 'block');
            shape_import_area.css('display', 'none');
            shape_save_area.css('display', 'none');
        }

        function poiCategory1() {
            poi_category_area1.css('display', 'block');
            shape_import_area1.css('display', 'none');
            shape_save_area1.css('display', 'none');
        }

        var select_all = document.getElementById("select_all");

        //checkbox items
        var checkboxes = document.getElementsByClassName("checkbox");

        //select all checkboxes
        select_all.addEventListener("change", function (e) {
            for (i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = select_all.checked;
            }
        });


        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].addEventListener('change', function (e) {
                //".checkbox" change
                //uncheck "select all", if one of the listed checkbox item is unchecked
                if (this.checked == false) {
                    select_all.checked = false;
                }

                //check "select all" if all checkbox items are checked
                if (document.querySelectorAll('.checkbox:checked').length == checkboxes.length) {
                    select_all.checked = true;
                }
            });
        }

        function import_shape_area(param) {
            var shape_id;
            if(param === 1) {
                shape_id = $("#import_shape_val").val();
                removePolygon();
            }
            else if (param === 2) {
                shape_id = $("#import_shape_val_1").val();
                removePolygon1();
            }
            // if(shape_id === '0') alert('Please select the any shape');
            // else{
            $.ajax({
                type: 'get',
                data: {id: shape_id},
                url: "{{route('admin.getShape')}}",
                success: function (response) {
                    let responses = JSON.parse(response);
                    //get polygon val
                    let polygon_val = JSON.parse(responses.shape);
                    let coordinate_val = responses.coordinate;
                    let latitude = JSON.parse(coordinate_val);
                    let lat = latitude.lat;
                    let lng = latitude.lng;
                    if(param === 1)
                    {
                        $("#lat").val(lat);
                        $("#lng").val(lng);
                        $("#geofence_latlng").text(responses.shape_origin);
                        $("#bound").val('('+lat+','+lng+')');
                        setLatLngDispatcher(1,responses.shape_origin);
                    }else if (param === 2)
                    {
                        $("#lat_dest").val(lat);
                        $("#lng_dest").val(lng);
                        $("#geofence_other_latlng").text(responses.shape_origin);
                        $("#bound1").val('('+lat+','+lng+')');
                        setLatLngDispatcher(2,responses.shape_origin);
                    }

                    let polygon_vals = {
                        "type": "FeatureCollection",
                        "features": [{
                            "type": "Feature",
                            "properties": {"title": "rewqwr"},
                            "geometry": {
                                "coordinates": polygon_val,
                                "type": "Polygon"
                            }
                        }]
                    };

                    if(param == 1)  initialize(polygon_vals);
                    else if(param == 2) initialize_destination(polygon_vals);
                }
            });
        }

        function shapeSaveVal(param)
        {
            if(param === 1) var shape_title = $("#shape_name").val();
            else if (param === 2)  shape_title = $("#shape_name1").val();
            if(shape_title === ''){
                alert('please insert shape name');
            }else{
                var csrf = $('meta[name="csrf_token"]').attr('content');
                if(param === 1)
                {
                    var bound = $("#bound").val();
                    var result = bound.slice(1,-1);
                    var lat = result.split(',')[0];
                    var lng = result.split(',')[1];
                    var geofence_val = $("#geofence_latlng").val();
                    var poi_category_val = $("#poi_category_val").val();

                }else if (param === 2)
                {
                    var bound = $("#bound1").val();
                    var result = bound.slice(1,-1);
                    var lat = result.split(',')[0];
                    var lng = result.split(',')[1];
                    var geofence_val = $("#geofence_other_latlng").val();
                    var poi_category_val = $("#poi_category_val1").val();
                }
                // console.log(poi_category_val);
                // return false;
                $.ajax({
                    type: 'post',
                    data: {_token:csrf,lat:lat,lng:lng,geofence_val:geofence_val,title:shape_title,poi_category_val:poi_category_val},
                    url: "{{route('admin.saveShape')}}",
                    success: function (response) {
                        if(response.success){
                            alert('succesfully saved');
                            getShapeData( $('#import_shape_val'));
                            getShapeData( $('#import_shape_val_1'));
                        }
                    }
                });
            }
        }
        //when new shape is saved, get all shape
        function getShapeData(container)
        {
            var html = '';
            $.ajax({
                type:'get',
                url:'{{route('admin.getShapeData')}}',
                success:function (response) {
                    var result = JSON.parse(response);
                    $.each(result, function (key,val) {
                        html += "<option value='"+val.id+"'>"+val.title+"</option>";
                    });
                    container.html(html);
                }
            })
        }

        function validateGeofence(e)
        {
            e.preventDefault();
            if($("#type").val() == ""){
                alert('Please insert geofence Name');
                return false;
            }else if($("#bound").val() == ""){
                alert('Please insert Area 1');
                return false;
            }else if($("#bound1").val() == ""){
                alert('Please insert Area 2');
                return false;
            }
            return true;
        }

        function setLatLngDispatcher(param,coordinate)
        {
            var temp = coordinate.substring(0,coordinate.length-1).split(',');
            for(i=0;i<temp.length;i++)
            {
                poly_val = temp[i].split(':');
                geo_val = poly_val[1] + ":" + poly_val[0];
                if(param == 1)
                {
                    document.getElementById('geofence_latlng_dispatcher').innerHTML += geo_val + ",";
                }else if (param == 2){
                    document.getElementById('geofence_other_latlng_dispatcher').innerHTML += geo_val + ",";
                }
            }
        }

        $('form').bind("keypress", function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });
    </script>
@endsection
