@extends('admin.layout.base')

@section('title')

@section('content')
<div class="content-area py-1">
	<div class="container-fluid">
		<div class="box box-block bg-white">
			<a href="{{ route('admin.advertisement.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;"></h5>

			<form class="form-horizontal" action="{{route('admin.advertisement.store')}}" method="POST" enctype="multipart/form-data" role="form">
				{{csrf_field()}}

				<div class="form-group row">
					<label for="type" class="col-xs-2 col-form-label">@lang('admin.advertisement.type')</label>
					<div class="col-xs-10">
						<select name="type" class="form-control">
							<option value="ALL">@lang('admin.dashboard.Allss')</option>
							<option value="USER">@lang('admin.dashboard.Userss')</option>
							<option value="PROVIDER">@lang('admin.dashboard.Providerss')</option>
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="picture" class="col-xs-2 col-form-label">@lang('admin.advertisement.image')</label>
					<div class="col-xs-10">
						<input type="file" accept="image/*" name="image" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="notify_desc" class="col-xs-2 col-form-label">@lang('admin.advertisement.click_url')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('click_url') }}" name="click_url" required id="click_url" placeholder="@lang('admin.advertisement.click_url')">
					</div>
				</div>

				<div class="form-group row">
					<label for="notify_status" class="col-xs-2 col-form-label">@lang('admin.advertisement.status')</label>
					<div class="col-xs-10">
						<select name="status" class="form-control">
							<option value="ACTIVE">ACTIVE</option>
							<option value="INACTIVE">INACTIVE</option>
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.advertisement.add')</button>
						<a href="{{route('admin.advertisement.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

@endsection

@section('scripts')
<script type="text/javascript">

</script>
@endsection