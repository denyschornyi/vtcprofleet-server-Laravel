@extends('fleet.layout.base')

@section('title','Create Pool ')
@section('styles')
<style>

	tbody tr td:first-child, td:nth-child(2){
		vertical-align: middle;
	}
</style>
@endsection

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('fleet.get_private_pool') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.include.update_pool')</h5>

            <form class="form-horizontal" action="{{route('fleet.update.private_pool',['id'=>$data->id])}}" method="POST" role="form">
            	{{csrf_field()}}
				<div class="form-group row">
					<label for="first_name" class="col-xs-12 col-form-label">@lang('admin.request.Pool_Name')</label>
					<div class="col-xs-8">
						<input class="form-control" type="text" value="{{ $data->pool_name }}" name="pool_name" required id="pool_name" placeholder="@lang('admin.request.Pool_Name')">
					</div>
				</div>
				<div class="form-group row">
					<label for="calculator" class="col-xs-12 col-form-label">@lang('admin.fleet.fleet_name')</label>
					<div class="col-xs-8">
						<input class="form-control" type="text"  name="pool_email"  id="pool_email" placeholder="@lang('admin.user-pro.email')">
					</div>
					<div class="col-xs-2">
						<button class="btn btn-primary" onclick="addPartner(event);">@lang('admin.fleets.add_parnter')</button>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-xs-8">
						<table class="table table-striped table-bordered">
							<thead>
							<tr>
								<th>@lang('admin.request.partner')</th>
								<th>@lang('admin.status')</th>
								<th>@lang('admin.action')</th>
							</tr>
							</thead>
							<tbody id="partner_content">
							</tbody>
						</table>
					</div>
				</div>
				<div class="form-group row">
					<label for="calculator" class="col-xs-12 col-form-label">@lang('admin.poi.status')</label>
					<div class="col-xs-8">
						<select class="form-control" id="status" name="status" style="padding: 6px;">
							<option value="1" @if($data->status == '1') selected @endif>@lang('admin.poi.active')</option>
							<option value="0" @if($data->status == '0') selected @endif>@lang('admin.poi.inactive')</option>
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-12">
						<button type="submit" class="btn btn-primary">@lang('admin.include.update_pool')</button>
						<a href="{{route('fleet.get_private_pool')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/sweetalert2@7.8.2/dist/sweetalert2.all.js"></script>
<script>
	$(document).ready(function () {
		getPartnerList();
	});
	function addPartner(event)
	{
		event.preventDefault();
		event.stopPropagation();
		let pool_email = $('#pool_email').val();
		if(pool_email == '')
		{
			swal(
					'Error',
					'Please insert the email',
					'error'
			);
			return;
		}
		$.ajax({
			type: "get",
			url: "{{route('fleet.add.partner')}}",
			data: {
				'fleet_email' : $('#pool_email').val(),
				'pool_id' : '{{ $data->id }}'
			},
			success: function(response){
				swal(
					response.message
				);
				getPartnerList();
			}
		});
	}
	function getPartnerList()
	{
		var container = $('#partner_content');
		$.ajax({
			type:'get',
			url: " {{ route('fleet.getPartnerList') }}",
			data : {
				'id' : '{{ $data->id }}'
			},
			success : function (res) {
				container.html(res);
			}
		})
	}
	$('tbody').on('click', '.delete', function()
	{
		event.preventDefault();
		event.stopPropagation();
		let tempId = $(this).attr('id');
		let id = tempId.split('_')[1];

		if(confirm('Are you sure you want to delete this?')) {
			$.ajax({
				type:'get',
				url: " {{ route('fleet.deletePartner') }}",
				data : {
					'id' : id
				},
				success : function (res) {
					getPartnerList();
				}
			})
		}
	});


</script>
@endsection
