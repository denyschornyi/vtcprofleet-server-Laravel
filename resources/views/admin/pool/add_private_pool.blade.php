@extends('fleet.layout.base')

@section('title','Create Pool ')
@section('styles')

@endsection

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('admin.get_private_pool') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.include.add_new_pool')</h5>

            <form class="form-horizontal" action="{{route('admin.add.private_pool')}}" method="POST" role="form">
            	{{csrf_field()}}
				<div class="form-group row">
					<label for="first_name" class="col-xs-12 col-form-label">@lang('admin.request.Pool_Name')</label>
					<div class="col-xs-12">
						<input class="form-control" type="text" value="{{ old('pool_name') }}" name="pool_name" required id="pool_name" placeholder="@lang('admin.request.Pool_Name')">
					</div>
				</div>

				<div class="form-group row">
					<label for="calculator" class="col-xs-12 col-form-label">@lang('admin.poi.status')</label>
					<div class="col-xs-12">
						<select class="form-control" id="status" name="status" style="padding: 6px;">
							<option value="1">@lang('admin.poi.active')</option>
							<option value="0">@lang('admin.poi.inactive')</option>
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-12">
						<button type="submit" class="btn btn-primary">@lang('admin.include.add_new_pool')</button>
						<a href="{{route('admin.get_private_pool')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection

@section('scripts')

@endsection
