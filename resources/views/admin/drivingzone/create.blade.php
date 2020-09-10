@extends('admin.layout.base')

@section('title')
@section('styles')
    <style>
        td{
            padding:10px;
        }
        table{
            font-size: 15px;
        }
        .t1{
            font-weight: bold;
            font-size: 16px;
            padding-bottom: 10px;
        }
        .custom-select {
            border-radius: 0;
            width: 100%;
            height: 200px;
        }
        input[type="text"], .to input[type="password"] {
            height: auto;
            width: 400px;
            padding: 10px;
            box-shadow: none;
            border-width: 1px;
            border-style: solid;
            border-color: #E4E4E4;
        }
        input{
            width: 100% !important;
            max-width: 100%;
        }
    </style>
@endsection
@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('admin.drivingzone.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>
			<h3 style="margin-bottom: 15px;">@lang('admin.include.add_driverzone')</h3>
            <p>@lang('admin.include.driverzonenote')</p>
{{--            <p style="color: red;"> * Please choose only one option in country or location.</p>--}}
            <form class="form-horizontal" action="{{route('admin.drivingzone.store')}}" method="POST" enctype="multipart/form-data" role="form" style="margin-top: 20px;">
            	{{csrf_field()}}
                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <td style="vertical-align: middle">status</td>
                        <td>
                            <div class="onoffswitch"
                                 style="margin-left: auto; margin-right: 10px; margin-bottom: 10px;">
                                <input type="checkbox" class="js-switch" id="start_switch" name="start_switch" checked />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle"><div>Restriction to country</div><span>Select countries</span></td>
                        <td>
                            <select class="custom-select" multiple="multiple" id="start_country" name="start_country[]">
                                <option disabled="" selected>Not Set</option>
                                @foreach($country_list as $key=>$val)
                                    <option value="{{$val->nicename}}">{{$val->nicename}}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle"><div>Restriction to area</div><span>Address and radius in kilometers</span></td>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <input type="text" placeholder="Enter a Location" autocomplete="off" id="restict_start" name="restict_start" >
                                <input type="hidden" id="start_countryname" name="start_countryname" />
                                <input type="hidden" id="start_region" name="start_region" />
                                <input type="hidden" id="start_lat" name="start_lat" />
                                <input type="hidden" id="start_lng" name="start_lng" />
                            </div>
                            <div>
                                <input type="text" id="start_radius" name="start_radius" placeholder="Enter Radius" value="50">
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary">Add Driving Zone</button>
			</form>
		</div>
    </div>
</div>

@endsection
@section('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('constants.map_key') }}&libraries=places"></script>

    <script>
        function init() {
            var restict_start = document.getElementById("restict_start");
            var restict_dest = document.getElementById("restict_dest");

            var autocomplete_start = new google.maps.places.Autocomplete(restict_start);
            // for start location
            google.maps.event.addListener(autocomplete_start, 'place_changed', function () {
                var place = autocomplete_start.getPlace();
                console.log(place);
                console.log(place.types.length);
                document.getElementById('start_lat').value = place.geometry.location.lat();
                document.getElementById('start_lng').value = place.geometry.location.lng();
                document.getElementById('start_region').value = place.name;

                for (var i = 0; i < place.address_components.length; i++) {
                    for (var j = 0; j < place.address_components[i].types.length; j++) {
                        // if (place.address_components[i].types[j] === "locality") {
                        //     document.getElementById('start_city').value = place.address_components[i].long_name;
                        // }
                        if (place.address_components[i].types[j] === "country") {
                            document.getElementById('start_countryname').value = place.address_components[i].long_name;
                        }
                        // if (place.address_components[i].types[j] === "administrative_area_level_1") {
                        //     document.getElementById('start_region').value = place.address_components[i].long_name;
                        // }
                    }
                }
            });
        }

        google.maps.event.addDomListener(window,'load',init);
      // var start_switch_val = $('#start_switch').prop('checked');
      // var dest_switch_val = $('#dest_switch').prop('checked');
        $(window).keydown(function(event){
            if(event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });

        function validateDrivingZone()
        {
            var start_type = $("#start_type").val();
            var dest_type  = $("#dest_type").val();
            if(start_type == 3){
                alert("You have to insert radius");
                return false;
            }
            if(dest_type == 3) {
                alert("You have to insert radius");
                return false;
            }
            return true;
        }
    </script>
@endsection
