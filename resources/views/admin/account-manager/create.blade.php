@extends('admin.layout.base')

@section('title')
@section('styles')
	<link rel="stylesheet" href="{{asset('asset/css/intlTelInput.css')}}">
@endsection
@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('admin.account-manager.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.account-manager.add_account_manager')</h5>

            <form class="form-horizontal" action="{{route('admin.account-manager.store')}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
				<div class="form-group row">
					<label for="name" class="col-xs-12 col-form-label">@lang('admin.account-manager.full_name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name" placeholder="@lang('admin.account-manager.full_name')">
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
						<input class="form-control" type="password" name="password_confirmation" id="password_confirmation" placeholder="@lang('admin.account.Rety')">
					</div>
				</div>
				<div class="form-group row">
					<label for="country_code" class="col-xs-12 col-form-label">@lang('admin.provides.codds')</label>
					<div class="col-xs-10">
						<input type="text" name="country_code" style ="padding-bottom:5px;" class="country-name" id="country_code">
					</div>
				</div>
				<div class="form-group row">
					<label for="mobile" class="col-xs-12 col-form-label">@lang('admin.mobile')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ old('mobile') }}" name="mobile" required id="mobile" placeholder="@lang('admin.mobile')">
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.account-manager.add_account_manager')</button>
						<a href="{{route('admin.account-manager.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
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
