@extends('user.layout.base')

@section('title','edit')
@section('styles')
<link rel="stylesheet" href="{{asset('asset/css/intlTelInput.css')}}">
@stop
@section('content')

<!-- edit page -->
<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('user-passenger.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.service.Update_User')</h5>

            <form class="form-horizontal" action="{{route('user-passenger.update', $user->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="first_name" class="col-xs-2 col-form-label">@lang('admin.first_name')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ $user->first_name }}" name="first_name" required id="first_name" placeholder="@lang('admin.first_name')">
					</div>
				</div>

				<div class="form-group row">
					<label for="last_name" class="col-xs-2 col-form-label">@lang('admin.last_name')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ $user->last_name }}" name="last_name" required id="last_name" placeholder="@lang('admin.last_name')">
					</div>
				</div>

				<div class="form-group row">

					<label for="picture" class="col-xs-2 col-form-label">@lang('admin.picture')</label>
					<div class="col-xs-12">
					@if(isset($user->picture))
                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{img($user->picture)}}">
                    @endif
						<input type="file" accept="image/*" name="picture" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="country_code" class="col-xs-2 col-form-label">@lang('admin.provides.codds')</label>
					<div class="col-xs-12">
					<input type="text" name="country_code" style ="padding-bottom:5px;" class="country-name" id="country_code" value="{{ $user->country_code }}">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">@lang('admin.mobile')</label>
					<div class="col-xs-12">
{{--						<input class="form-control" type="number" value="{{ $user->country_code }}{{ $user->mobile }}" name="mobile" required id="mobile" placeholder="@lang('admin.mobile')">--}}
						<input class="form-control" type="number" value="{{ $user->mobile }}" name="mobile" required id="mobile" placeholder="@lang('admin.mobile')">
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-12">
						<button type="submit" class="btn btn-primary">@lang('admin.service.Update_User')</button>
						<a href="{{route('user-passenger.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
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
