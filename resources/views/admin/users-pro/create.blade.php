@extends('admin.layout.base')

@section('title')

@section('styles')
<link rel="stylesheet" href="{{asset('asset/css/intlTelInput.css')}}">
@endsection

@section('content')

<div class="content-area py-1">
	<div class="container-fluid">
		<div class="box box-block bg-white">
			<a href="{{ route('admin.user-pro.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.users.Add_User')</h5>

			<form class="form-horizontal" action="{{route('admin.user-pro.store')}}" method="POST" enctype="multipart/form-data" role="form" onkeypress="return disableEnterKey(event);">
				{{csrf_field()}}
				<div class="form-group row">
					<label for="company_name" class="col-xs-12 col-form-label">@lang('admin.user-pro.name')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ old('company_name') }}" name="company_name" required id="company_name" placeholder="@lang('admin.user-pro.name')">
					</div>
				</div>

				<div class="form-group row">
					<label for="reg_number" class="col-xs-12 col-form-label">@lang('admin.user-pro.reg_number')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ old('reg_number') }}" name="reg_number" required id="reg_number" placeholder="@lang('admin.user-pro.reg_number')">
					</div>
				</div>

				<div class="form-group row">
					<label for="origin-input" class="col-xs-12 col-form-label">@lang('admin.user-pro.address')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ old('company_address') }}" name="company_address" required id="origin-input" placeholder="@lang('admin.user-pro.address')">
						<input type="hidden" name="latitude" id="origin_latitude" value="{{ old('latitude') }}">
						<input type="hidden" name="longitude" id="origin_longitude" value="{{ old('longitude') }}">
					</div>
				</div>

				<div class="form-group row">
					<label for="origin-input" class="col-xs-12 col-form-label">@lang('admin.user-pro.zipcode')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ old('company_zip_code') }}" name="company_zip_code" required id="origin-input" placeholder="@lang('admin.user-pro.zipcode')">
					</div>
				</div>

				<div class="form-group row">
					<label for="origin-input" class="col-xs-12 col-form-label">@lang('admin.user-pro.city')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ old('company_city') }}" name="company_city" required id="origin-input" placeholder="@lang('admin.user-pro.city')">
					</div>
				</div>

				<div class="form-group row">
					<label for="email" class="col-xs-12 col-form-label">@lang('admin.user-pro.email')</label>
					<div class="col-xs-12">
						<input class="form-control" type="email" required name="email" value="{{old('email')}}" id="email" placeholder="@lang('admin.user-pro.email')">
					</div>
				</div>

				<div class="form-group row">
					<label for="password" class="col-xs-12 col-form-label">@lang('admin.password')</label>
					<div class="col-xs-12">
						<input class="form-control" type="password" name="password" id="password" placeholder="@lang('admin.password')">
					</div>
				</div>

				<div class="form-group row">
					<label for="password_confirmation" class="col-xs-12 col-form-label">@lang('admin.account.password_confirmation')</label>
					<div class="col-xs-12">
						<input class="form-control" type="password" name="password_confirmation" id="password_confirmation" placeholder="@lang('admin.account.password_confirmation')">
					</div>
				</div>

				<div class="form-group row">
					<label for="picture" class="col-xs-12 col-form-label">@lang('admin.picture')</label>
					<div class="col-xs-12">
						<input type="file" accept="image/*" name="picture" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="country_code" class="col-xs-12 col-form-label">@lang('admin.provides.codds')</label>
					<div class="col-xs-12">
						<input type="text" name="country_code" style="padding-bottom:5px;" class="country-name" id="country_code">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-12 col-form-label">@lang('admin.mobile')</label>
					<div class="col-xs-12">
						<input class="form-control" type="number" value="{{ old('mobile') }}" name="mobile" required id="mobile" placeholder="@lang('admin.mobile')">
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-3">
						<label class="col-xs-12 col-form-label" for="allow_negative"><input type="checkbox" value="1" name="allow_negative" id="allow_negative" style="opacity:1;position:relative;z-index:1">
							@lang('admin.user-pro.allow_negative')</label>
					</div>
					<div class="col-md-2"><input class="form-control" type="number" step=".01" value="{{ old('wallet_limit') }}" name="wallet_limit" id="wallet_limit" placeholder="@lang('admin.include.wallfile')"></div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-12">
						<button type="submit" class="btn btn-primary">@lang('admin.users.Add_User')</button>
						<a href="{{route('admin.user-pro.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
				<div id="map" style="display:none;"></div>
			</form>
		</div>
	</div>
</div>

@endsection

@section('scripts')
<script type="text/javascript" src="{{ asset('asset/js/intlTelInput.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('asset/js/intlTelInput-jquery.min.js') }}"></script>
<script type="text/javascript">
	var input = document.querySelector("#country_code");
	window.intlTelInput(input, ({}));
	$(".country-name").click(function() {
		var myVar = $(this).closest('.country').find(".dial-code").text();
		$('#country_code').val(myVar);
	});

	function disableEnterKey(e) {
		// var key;
		// if(window.e)
		//     key = window.e.keyCode; // IE
		// else
		// 	key = e.which; // Firefox
		if (e.which == 13)
			return e.preventDefault();
	}
	var current_latitude = 13.0574400;
	var current_longitude = 80.2482605;
</script>
<script type="text/javascript" src="{{ asset('asset/js/map.js') }}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('constants.map_key') }}&libraries=places&callback=initMap" async defer></script>

@endsection