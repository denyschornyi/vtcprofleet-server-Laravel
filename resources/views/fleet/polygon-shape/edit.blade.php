@extends('fleet.layout.base')
@section('styles')
    <style>
        #floating-panel {
            margin-bottom: 15px;
        }

        .mr-15 {
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
                width: 112%;
                margin-bottom: 10px;
            }
        }
    </style>
@stop
@section('title','edit')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <a href="{{ route('fleet.polygonShape.index') }}" class="btn btn-default pull-right"><i
                            class="fa fa-angle-left"></i> @lang('admin.back')</a>

                <h5 style="margin-bottom: 2em;">@lang('admin.point.Add_Point_Interest')</h5>

                <form class="form-horizontal" action="{{route('fleet.polygonShape.update',$obj->id)}}" method="POST" role="form">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="PATCH">
                    <table class="form-table">
                        <tbody id="quickcab_geofence_ignore_surge_pricing_rule_cont">
                        <tr>
                            <th scope="row" style="padding: 20px 0px 20px 17px;width: 15%">
                                <label for="quickcab_geofence_fixed_price_amount" class="col-form-label">Name</label>
                            </th>
                            <td style="padding: 15px 30px;">
                                <input class="form-control" type="text" value="{{ $obj->title }}" name="title" required style="width: 60%;"
                                       id="title" placeholder=@lang('admin.point.geofence_rule_name')>
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
                        <input id="lat" name="lat" type="hidden" value="{{json_decode($obj->coordinate)->lat}}">
                        <input id="lng" name="lng" type="hidden" value="{{json_decode($obj->coordinate)->lng}}">

                        <input id="bound" name="bound"  type="hidden" value="({{json_decode($obj->coordinate)->lat}},{{json_decode($obj->coordinate)->lng}})">

                    </div>
                    <div class="form-group row" style="margin-left: 0.25em;">
                        <div class="col-xs-12">

                            <div class="new_input_field row" style="width: 100%">
                                <div class="col-md-12 col-xs-12">
                                    <textarea id="geofence_latlng" name="geofence_latlng"  style="display:none;width: 100%;">{{$obj->shape_origin}}</textarea>
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
                                    </div>
                                    <div id="map" class="map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="form-table">
                        <tbody id="quickcab_geofence_ignore_surge_pricing_rule_cont">
                        <tr>
                            <th scope="row" style="padding: 20px 0px 20px 17px;width: 15%">
                                <label for="quickcab_geofence_fixed_price_amount" class="col-form-label">POI Category</label>
                            </th>
                            <td style="padding: 15px 30px;">
                                <select class="form-control" id="poi_category_val" name="poi_category_val"
                                        style="padding: 6px;">
                                    @foreach ($poi_category as $val)
                                        <option value="{{$val->id}}" @if ($obj->poi_category_id === $val->id) selected  @endif>{{$val->type}}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="form-group row">
                        <div class="col-xs-6 col-sm-3 col-md-2" style="margin-left: 17px;">
                            <button type="submit"
                                    class="btn btn-primary">@lang('admin.point.Update_Point_Interest')</button>
                        </div>
                        <div class="col-xs-6 col-sm-3 col-md-2">
                            <a href="{{ route('fleet.polygonShape.index') }}"
                               class="btn btn-danger">@lang('admin.cancel')</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;key={{config('constants.map_key')}}&amp;libraries=places&amp;libraries=drawing,places&amp;language=&amp;region=JP"></script>

    <script type="text/javascript" src="{{asset('main/assets/js/jsts.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/assets/js/jquery.validate.min.js')}}"></script>


    <script type="text/javascript">

        var poly_values = [];
        var drawingManager;
        var selectedShape;
        var drawingManager1;
        var globalLoadedPoly;

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
            initialize(1);
            // $('#remove_polygon').hide();
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
            if (polygonval.type === 'FeatureCollection') {
                map.data.addGeoJson({
                    "type": "FeatureCollection", "features": [{
                        "type": "Feature",
                        "properties": {"title": "rewqwr"},
                        "geometry": {
                            "coordinates": {{$obj->shape}},
                            "type": "Polygon"
                        }
                    }]
                });

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
            else if (polygonval === 1)
            {
                map.data.addGeoJson({"type": "FeatureCollection", "features": []});

                map.data.setStyle(function (feature) {
                    return ({
                        strokeColor: 'balck',
                        fillColor: 'black',
                        strokeWeight: 2,
                        fillOpacity: 0.50,
                        strokeOpacity: 0.8,
                        draggable: false,
                        editable:true,
                    });
                });
            }
            else
            {
                map.data.addGeoJson({
                    "type": "FeatureCollection", "features": [{
                        "type": "Feature", "properties": {"title": "rewqwr"}, "geometry": {
                            "coordinates": {{$obj->shape}},
                            "type": "Polygon"
                        }
                    }]
                });

                map.data.addListener('setgeometry', function(e)
                {
                    get_polygon_cordinates_from_data_layer(e.newGeometry,1);
                });

                map.data.setStyle(function (feature) {
                    return ({
                        strokeColor: 'balck',
                        fillColor: 'black',
                        strokeWeight: 2,
                        fillOpacity: 0.50,
                        strokeOpacity: 0.8,
                        draggable: false,
                        editable:true,
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

            // polygoncomplete
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
                    //
                    google.maps.event.addListener(path, 'insert_at', function () {
                        // New point
                        get_polygon_cordinates(polygon);
                    });
                    //
                    //     //to enable the save shape button after the polygon has drawn
                    //     $('#save_shape').attr('disabled', false);
                    //
                    google.maps.event.addListener(path, 'remove_at', function () {
                        // Point was removed
                        get_polygon_cordinates(polygon);
                    });
                    //
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
                console.log(newShape);
                google.maps.event.addListener(newShape, 'click', function () {
                    setSelection(newShape);
                });
                setSelection(newShape);
            });

        }

        function get_polygon_cordinates_from_data_layer(geom,map) {
            if (map === 1)  document.getElementById('geofence_latlng').innerHTML = "";
            else if (map === 2)  document.getElementById('geofence_other_latlng').innerHTML = "";
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
                document.getElementById('geofence_latlng').innerHTML = output;
            else if(map ===2)
                document.getElementById('geofence_other_latlng').innerHTML = output;

            $('#zone_lat_long').val(JSON.stringify(aa));

            var center = bounds.getCenter();
            if(map === 1)
                document.getElementById('bound').value = center;
            else if(map === 2)
                document.getElementById('bound1').value = center;
        }

        function call_initialize() {
            initialize();//call first map function

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



        $(document).ready(function () {

            $("#zone_address").keypress(function (e) {
                return e.keyCode != 13;
            });
        });
        var poi_category_area = $("#poi_category_area");

        function poiCategory() {
            poi_category_area.css('display', 'block');
            shape_import_area.css('display', 'none');
            shape_save_area.css('display', 'none');
        }

        //when new shape is saved, get all shape
        function getShapeData(container)
        {
            var html = '';
            $.ajax({
                type:'get',
                url:'{{route('fleet.getShapeData')}}',
                success:function (response) {
                    var result = JSON.parse(response);
                    $.each(result, function (key,val) {
                        html += "<option value='"+val.id+"'>"+val.title+"</option>";
                    });
                    container.html(html);
                }
            })
        }
    </script>
@endsection
