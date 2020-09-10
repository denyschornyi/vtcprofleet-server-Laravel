@extends('admin.layout.base')

@section('title')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('admin.advertisement.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;"></h5>

            <form class="form-horizontal" action="{{route('admin.advertisement.update', $advertisement->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">				
				
				<div class="form-group row">
					<label for="type" class="col-xs-2 col-form-label">@lang('admin.advertisement.type')</label>
					<div class="col-xs-10">
						<select name="type" class="form-control">
							<option value="ALL" @if($advertisement->type == 'ALL')selected="selected" @endif>@lang('admin.dashboard.Allss')</option>
							<option value="USER" @if($advertisement->type == 'USER')selected="selected" @endif>@lang('admin.dashboard.Userss')</option>
							<option value="PROVIDER" @if($advertisement->type == 'PROVIDER')selected="selected" @endif>@lang('admin.dashboard.Providerss')</option>
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="picture" class="col-xs-2 col-form-label">@lang('admin.advertisement.image')</label>
					<div class="col-xs-10">
						@if(isset($advertisement->image))
                        	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{ $advertisement->image }}">
                        @endif
						<input type="file" accept="image/*" name="image" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="notify_desc" class="col-xs-2 col-form-label">@lang('admin.advertisement.click_url')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $advertisement->click_url }}" name="click_url" required id="click_url" placeholder="@lang('admin.advertisement.click_url')">
					</div>
				</div>

				<div class="form-group row">
					<label for="notify_status" class="col-xs-2 col-form-label">@lang('admin.advertisement.status')</label>
					<div class="col-xs-10">
						<select name="status" class="form-control">
							<option value="ACTIVE" @if($advertisement->status == 'ACTIVE')selected="selected" @endif>ACTIVE</option>
							<option value="INACTIVE" @if($advertisement->status == 'INACTIVE')selected="selected" @endif>INACTIVE</option>
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.advertisement.update')</button>
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
