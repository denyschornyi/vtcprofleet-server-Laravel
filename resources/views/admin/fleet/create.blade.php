@extends('admin.layout.base')

@section('title')

@section('styles')
	<link rel="stylesheet" href="{{asset('asset/css/intlTelInput.css')}}">
@endsection
@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('admin.fleet.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.fleet.add_fleet_owner')</h5>

            <form class="form-horizontal" action="{{route('admin.fleet.store')}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
				<div class="form-group row">
					<label for="name" class="col-xs-12 col-form-label">@lang('admin.account-manager.full_name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name" placeholder="@lang('admin.account-manager.full_name')">
					</div>
				</div>

				<div class="form-group row">
					<label for="company" class="col-xs-12 col-form-label">@lang('admin.fleet.company_name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('company') }}" name="company" required id="company" placeholder="@lang('admin.fleet.company_name')">
					</div>
				</div>

				<div class="form-group row">
					<label for="email" class="col-xs-12 col-form-label">@lang('admin.email')</label>
					<div class="col-xs-10">
						<input class="form-control" type="email" required name="email" value="{{old('email')}}" id="email" placeholder="@lang('admin.email')">
					</div>
				</div>

				<div class="form-group row">
					<label for="password" class="col-xs-12 col-form-label">@lang('admin.password')</label>
					<div class="col-xs-10">
						<input class="form-control" type="password" name="password" id="password" placeholder="@lang('admin.password')">
					</div>
				</div>

				<div class="form-group row">
					<label for="password_confirmation" class="col-xs-12 col-form-label">@lang('admin.account-manager.password_confirmation')</label>
					<div class="col-xs-10">
						<input class="form-control" type="password" name="password_confirmation" id="password_confirmation" placeholder="@lang('admin.account-manager.password_confirmation')">
					</div>
				</div>

				<div class="form-group row">
					<label for="logo" class="col-xs-12 col-form-label">@lang('admin.fleet.company_logo')</label>
					<div class="col-xs-10">
						<input type="file" accept="image/*" name="logo" class="dropify form-control-file" id="logo" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="country_code" class="col-xs-12 col-form-label">@lang('admin.custom.code_pays')</label>
					<div class="col-xs-10" style="z-index:999;">
						<input class="form-control" type="" value="{{ old('country_code') }}" name="country_code" required id="country_code" placeholder="+33">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-12 col-form-label">@lang('admin.mobile')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ old('mobile') }}" name="mobile" required id="mobile" placeholder="@lang('admin.mobile')">
					</div>
				</div>

				<div class="form-group row">
					<label for="commission" class="col-xs-12 col-form-label">@lang('admin.fleet.fleet_commission')</label>
					<div class="col-xs-5">
						<input class="form-control" type="number" value="{{ old('commission') }}" name="commission" id="commission" placeholder="@lang('admin.fleet.fleet_commission')">
					</div>
					<div class="col-xs-5">
						<span style="color:red">@lang('admin.custom.commission_notice') </span>
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.fleet.add_fleet_owner')</button>
						<a href="{{route('admin.fleet.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
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
		window.intlTelInput(input,({
			// separateDialCode:true,
		}));
		$(".country-name").click(function(){
			var myVar = $(this).closest('.country').find(".dial-code").text();
			$('#country_code').val(myVar);
		});
	</script>
@endsection
