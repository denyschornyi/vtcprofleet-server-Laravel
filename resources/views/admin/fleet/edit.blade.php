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

			<h5 style="margin-bottom: 2em;">@lang('admin.fleet.update_fleet')</h5>

            <form class="form-horizontal" action="{{route('admin.fleet.update', $fleet->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="name" class="col-xs-2 col-form-label">@lang('admin.account-manager.full_name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $fleet->name }}" name="name" required id="name" placeholder="@lang('admin.account-manager.full_name')">
					</div>
				</div>

				<div class="form-group row">
					<label for="company" class="col-xs-2 col-form-label">@lang('admin.fleet.company_name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $fleet->company }}" name="company" required id="company" placeholder="@lang('admin.fleet.company_name')">
					</div>
				</div>


				<div class="form-group row">

					<label for="logo" class="col-xs-2 col-form-label">@lang('admin.fleet.company_logo')</label>
					<div class="col-xs-10">
					@if(isset($fleet->logo))
                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{img($fleet->logo)}}">
                    @endif
						<input type="file" accept="image/*" name="logo" class="dropify form-control-file" id="logo" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="country_code" class="col-xs-2 col-form-label">@lang('admin.custom.code_pays')</label>
					<div class="col-xs-10">
						<input class="form-control" type="" value="{{$fleet->country_code }}" name="country_code" required id="country_code" placeholder="+33">
					</div>
				</div>


				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">@lang('admin.mobile')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ $fleet->mobile }}" name="mobile" required id="mobile" placeholder="@lang('admin.mobile')">
					</div>
				</div>

				<div class="form-group row">
					<label for="commission" class="col-xs-2 col-form-label">@lang('admin.fleet.fleet_commission') </label>
					<div class="col-xs-5">
						<input class="form-control" type="number" value="{{ $fleet->commission }}" name="commission" id="commission" placeholder="@lang('admin.fleet.fleet_commission')">
					</div>
					<div class="col-xs-5">
						<span style="color:red">@lang('admin.custom.commission_notice') </span>
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.fleet.update_fleet_owner')</button>
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
