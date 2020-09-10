@extends('fleet.layout.base')

@section('title', 'Update User ')
@section('styles')
<link rel="stylesheet" href="{{asset('asset/css/intlTelInput.css')}}">
@endsection
@section('content')

<!-- edit page -->
<div class="content-area py-1">
	<div class="container-fluid">
		<div class="box box-block bg-white">
			<a href="{{ route('fleet.user-pro.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

			<h5 style="margin-bottom: 2em;">Update User</h5>

			<form class="form-horizontal" action="{{route('fleet.user-pro.update', $user->id )}}" method="POST" enctype="multipart/form-data" role="form">
				{{csrf_field()}}
				<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="company_name" class="col-xs-2 col-form-label">@lang('admin.user-pro.name')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ $user->company_name }}" name="company_name" required id="company_name" placeholder="@lang('admin.user-pro.name')">
					</div>
				</div>

				<div class="form-group row">
					<label for="reg_number" class="col-xs-2 col-form-label">@lang('admin.user-pro.reg_number')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ $user->reg_number }}" name="reg_number" required id="reg_number" placeholder="@lang('admin.user-pro.reg_number')">
					</div>
				</div>

				<div class="form-group row">
					<label for="origin-input" class="col-xs-2 col-form-label">@lang('admin.user-pro.address')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ $user->company_address }}" name="company_address" required id="origin-input" placeholder="@lang('admin.user-pro.address')">
						<input type="hidden" name="latitude" id="origin_latitude" value="{{ $user->latitude }}">
						<input type="hidden" name="longitude" id="origin_longitude" value="{{ $user->longitude }}">
					</div>
				</div>

				<div class="form-group row">
					<label for="origin-input" class="col-xs-12 col-form-label">@lang('admin.user-pro.zipcode')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ $user->company_zip_code }}" name="company_zip_code" required id="origin-input" placeholder="@lang('admin.user-pro.zipcode')">
					</div>
				</div>

				<div class="form-group row">
					<label for="origin-input" class="col-xs-12 col-form-label">@lang('admin.user-pro.city')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ $user->company_city }}" name="company_city" required id="origin-input" placeholder="@lang('admin.user-pro.city')">
					</div>
				</div>

				<div class="form-group row">

					<label for="picture" class="col-xs-2 col-form-label">Picture</label>
					<div class="col-xs-12">
					@if(isset($user->picture))
                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{img($user->picture)}}">
                    @endif
						<input type="file" accept="image/*" name="picture" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="country_code" class="col-xs-2 col-form-label">Country Code</label>
					<div class="col-xs-12">
						<input type="text" name="country_code" style="padding-bottom:5px;" class="country-name" id="country_code" value="{{ $user->country_code }}">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">Mobile</label>
					<div class="col-xs-12">
						<input class="form-control" type="number" value="{{ $user->mobile }}" name="mobile" required id="mobile" placeholder="Mobile">
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-3">
					<label for="allow_negative" style="margin-left:15px;margin-top:10px"><input type="checkbox" value="1" name="allow_negative" id="allow_negative" @if($user->allow_negative == 1) checked @endif style="opacity:1;position:relative;z-index:1">
						@lang('admin.user-pro.allow_negative')</label>
					</div>
					<div class="col-md-2"><input class="form-control" type="number" step=".01" value="{{ $user->wallet_limit }}" name="wallet_limit" required id="wallet_limit" placeholder="wallet_limit"></div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-12">
						<button type="submit" class="btn btn-primary">Update User</button>
						<a href="{{route('admin.user-pro.index')}}" class="btn btn-default">Cancel</a>
					</div>
				</div>
			</form>
			<div id="map" style="display:none;"></div>
		</div>
	</div>
</div>

@endsection
@section('scripts')
<script type="text/javascript" src="{{ asset('asset/js/intlTelInput.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('asset/js/intlTelInput-jquery.min.js') }}"></script>
<script type="text/javascript">
	//For mobile number with date
	var input = document.querySelector("#country_code");
	window.intlTelInput(input, ({
		// separateDialCode:true,
	}));
	$(".country-name").click(function() {
		var myVar = $(this).closest('.country').find(".dial-code").text();
		$('#country_code').val(myVar);
	});
	var current_latitude = 13.0574400;
    var current_longitude = 80.2482605;
</script>
<script type="text/javascript" src="{{ asset('asset/js/map.js') }}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('constants.map_key') }}&libraries=places&callback=initMap" async defer></script>
@endsection
