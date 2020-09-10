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
				<form class="form-horizontal" action="{{route('admin.drivingzone.update', $drivingzone->id )}}" method="POST" role="form">
					{{csrf_field()}}
					<input type="hidden" name="_method" value="PATCH">
					<table class="table table-bordered">
						<tbody>
						<tr>
							<td style="vertical-align: middle">status</td>
							<td>
								<div class="onoffswitch"
									 style="margin-left: auto; margin-right: 10px; margin-bottom: 10px;">
									<input type="checkbox" class="js-switch" id="start_switch" name="start_switch"  @if($drivingzone->active == '1') checked @endif/>
								</div>
							</td>
						</tr>
						<tr>
							<td style="vertical-align: middle"><div>Restriction to country</div><span>Select countries</span></td>
							@if($drivingzone->status == "country")
								@php $country_temp_list = explode(",", $drivingzone->country_list); @endphp
							@endif

							<td>
								<select class="custom-select" multiple="multiple" id="start_country" name="start_country[]">
									<option selected>Not Set</option>
									@foreach($country_list as $key=>$val)
										<option value="{{$val->nicename}}"
												@if(in_array($val->nicename,$country_temp_list)) selected @endif
												@if($drivingzone->status != "country") disabled  @endif>
											{{$val->nicename}}
										</option>
									@endforeach
								</select>
							</td>
						</tr>
						@if($drivingzone->status != "country")
						<tr>
							<td style="vertical-align: middle"><div>Restriction to area</div><span>Address and radius in kilometers</span></td>

							<td>
								<div style="margin-bottom: 10px;">
									<input type="text" placeholder="Enter a Location" autocomplete="off" id="restict_start" name="restict_start" value="{{ $drivingzone->location_original }}">
									<input type="hidden" id="start_countryname" name="start_countryname" value="{{$drivingzone->country}}" />
									<input type="hidden" id="start_region" name="start_region" value="{{$drivingzone->location}}" />
									<input type="hidden" id="start_lat" name="start_lat" value="{{$drivingzone->latitude}}" />
									<input type="hidden" id="start_lng" name="start_lng" value="{{$drivingzone->longitude}}" />
								</div>
								<div>
									<input type="text" id="start_radius" name="start_radius" placeholder="Enter Radius" value="{{$drivingzone->radius}}">
								</div>
							</td>
						</tr>
						@endif
						</tbody>
					</table>
					<button type="submit" class="btn btn-primary">Edit Driving Zone</button>
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

		// $('document').ready(function () {
		// 	var switch_val = $('.js-switch').prop('checked');
		// })
	</script>
@endsection
