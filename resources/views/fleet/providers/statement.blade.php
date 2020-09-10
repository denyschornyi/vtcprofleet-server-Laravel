@extends('fleet.layout.base')

@section('title', $page)

@section('content')
<style type="text/css">
	td:first-child{

	}
	.buttons-csv{
		display: none;
	}
</style>
<div class="content-area py-1">
	<div class="container-fluid">
		@if(isset($statement_for))
			<input type="hidden" name="statement_for" id="statement_for" value="{{$statement_for}}">
		@endif
		@if(isset($statement_for))
			<input type="hidden" name="statement_id" id="statement_id" value="{{$id}}">
		@endif
		<div class="box box-block bg-white">
			<h3>{{$page}}</h3>
			<div class="datemenu">
				<span>
					<a style="cursor:pointer" id="tday" class="showdate">@lang('admin.statement_date.Today')</a>
					<a style="cursor:pointer" id="yday" class="showdate">@lang('admin.statement_date.Yesterday')</a>
					<a style="cursor:pointer" id="cweek" class="showdate">@lang('admin.statement_date.Current_Week')</a>
					<a style="cursor:pointer" id="pweek" class="showdate">@lang('admin.statement_date.Previous_Week')</a>
					<a style="cursor:pointer" id="cmonth" class="showdate">@lang('admin.statement_date.Current_Month')</a>
					<a style="cursor:pointer" id="pmonth" class="showdate">@lang('admin.statement_date.Previous_Month')</a>
					<a style="cursor:pointer" id="cyear" class="showdate">@lang('admin.statement_date.Current_Year')</a>
					<a style="cursor:pointer" id="pyear" class="showdate">@lang('admin.statement_date.Previous_Year')</a>
				</span>
			</div>
			<div class="clearfix" style="margin-top: 15px;">
				<form class="form-horizontal" action="{{route('fleet.ride.statement.range')}}" method="GET" enctype="multipart/form-data" role="form" id="set-date">

					<div class="form-group row col-md-5">
						<label for="name" class="col-xs-4 col-form-label">Date From</label>
						<div class="col-xs-8">
							@if(isset($statement_for) && $statement_for =="provider")
							<input type="hidden" name="provider_id" id="provider_id" value="{{$id}}">
							@elseif(isset($statement_for) && $statement_for =="user")
							<input type="hidden" name="user_id" id="user_id" value="{{$id}}">
							@elseif(isset($statement_for) && $statement_for =="fleet")
							<input type="hidden" name="fleet_id" id="fleet_id" value="{{$id}}">
							@endif
							<input class="form-control" type="date" name="from_date" id="from_date" required placeholder="From Date">
						</div>
					</div>

					<div class="form-group row col-md-5">
						<label for="email" class="col-xs-4 col-form-label">Date To</label>
						<div class="col-xs-8">
							<input class="form-control" type="date" required name="to_date" id="to_date" placeholder="To Date">
						</div>
					</div>
					<div class="form-group row col-md-2">
						<button type="submit" class="btn btn-primary">Submit</button>
					</div>
				</form>

				<form action="{{route('fleet.statement.pdf')}}" method="GET" id="pdf-download">
					@if(isset($statement_for) && $statement_for =="provider")
					<input type="hidden" name="provider_id" value="{{$id}}">
					@elseif(isset($statement_for) && $statement_for =="user")
					<input type="hidden" name="user_id" value="{{$id}}">
					@elseif(isset($statement_for) && $statement_for =="fleet")
					<input type="hidden" name="fleet_id" value="{{$id}}">
					@endif
					<input type="hidden" name="from_date" value="@isset($from_date){{$from_date}}@endisset">
					<input type="hidden" name="to_date" value="@isset($to_date){{$to_date}}@endisset">
					<input type="hidden" name="type" value="@isset($type){{$type}}@endisset">
				</form>
			</div>

			<div style="text-align: center;padding: 20px;color: blue;font-size: 24px;">
				@if(isset($statement_for) && $statement_for =="provider")
				<p><strong>
						{{-- <span>@lang('admin.dashboard.over_earning') : {{currency($revenue[0]->overall)}}</span> --}}
						
						<span>@lang('admin.dashboard.over_commission') : {{currency($revenue['commission'])}}</span>
						{{-- <br>
						<span>@lang('admin.dashboard.pool_commission') : {{currency($revenue[0]->poolcomm)}}</span> --}}
						{{-- <br>
						<span>@lang('admin.dashboard.admin_commission') : {{currency($revenue['admin_commission'])}}</span> --}}
					</strong></p>
				{{-- @elseif(isset($statement_for) && $statement_for == "")
				<span>@lang('admin.dashboard.over_commission') : {{currency($revenue[0]->commission)}}</span> --}}
				@endif
			</div>

			<div class="row">

				<div class="col-lg-4 col-md-6 col-xs-12">
					<div class="box box-block bg-white tile tile-1 mb-2">
						<div class="t-icon right"><span class="bg-danger"></span><i class="ti-rocket"></i></div>
						<div class="t-content">
							<h6 class="text-uppercase mb-1">@lang('admin.dashboard.Rides')</h6>
							<h1 class="mb-1">{{count($rides)}}</h1>
							<i class="fa fa-caret-up text-success mr-0-5"></i><span>has been initiated by users</span>
						</div>
					</div>
				</div>

				@if(isset($statement_for) && $statement_for !="user")
				<div class="col-lg-4 col-md-6 col-xs-12">
					<div class="box box-block bg-white tile tile-1 mb-2">
						<div class="t-icon right"><span class="bg-success"></span><i class="ti-bar-chart"></i></div>
						<div class="t-content">
							<h6 class="text-uppercase mb-1">@lang('admin.dashboard.Revenue')</h6>
							<h1 class="mb-1">{{currency($revenue['overall'])}}</h1>
							<i class="fa fa-caret-up text-success mr-0-5"></i><span>from {{count($rides)}} Rides</span>
						</div>
					</div>
				</div>
				@else
				<div class="col-lg-4 col-md-6 col-xs-12">
					<div class="box box-block bg-white tile tile-1 mb-2">
						<div class="t-icon right"><span class="bg-success"></span><i class="ti-bar-chart"></i></div>
						<div class="t-content">
							<h6 class="text-uppercase mb-1">@lang('admin.dashboard.total')</h6>
							<h1 class="mb-1">{{currency($revenue['overall'])}}</h1>
							<i class="fa fa-caret-up text-success mr-0-5"></i><span>from {{count($rides)}} Rides</span>
						</div>
					</div>
				</div>
				@endif

				<div class="col-lg-4 col-md-6 col-xs-12">
					<div class="box box-block bg-white tile tile-1 mb-2">
						<div class="t-icon right"><span class="bg-warning"></span><i class="ti-archive"></i></div>
						<div class="t-content">
							<h6 class="text-uppercase mb-1">@lang('admin.dashboard.cancel_rides')</h6>
							<h1 class="mb-1">{{$cancel_rides}}</h1>
							<i class="fa fa-caret-down text-danger mr-0-5"></i><span>for @if($cancel_rides == 0) 0.00 @else {{round($cancel_rides/count($rides),2)}}% @endif Rides</span>
						</div>
					</div>
				</div>

				<div class="row row-md mb-2" style="padding: 15px;">
					<div class="col-md-12">
						<div class="box bg-white">
							<div class="box-block clearfix">
								<h5 class="float-xs-left">{{$listname}}</h5>
								<div class="float-xs-right">
								</div>
							</div>

							@if(count($rides) != 0)
							<table class="table table-striped table-bordered dataTable" id="table-8">
								<thead>
									<tr>
										<td>ID</td>
										<td>@lang('admin.request.Booking_ID')</td>
										<td>@lang('admin.request.picked_up')</td>
										<td>@lang('admin.request.dropped')</td>
										<td>@lang('admin.request.request_details')</td>
										@if(isset($statement_for) && $statement_for !="user")
										<td>@lang('admin.request.commission')</td>
										@endif
										{{-- <td>@lang('admin.custom.pool_commission')</td> --}}
										{{-- @if(isset($statement_for) && $statement_for !="user")
											<td>@lang('admin.custom.admin_commission')</td>
										@endif --}}
										<td>@lang('admin.request.date')</td>
										<td>@lang('admin.request.status')</td>
										@if(isset($statement_for) && $statement_for !="user")
										<td>@lang('admin.request.earned')</td>
										@else
										<td>@lang('admin.dashboard.total')</td>
										@endif
									</tr>
								</thead>
								<tbody>
									<?php $diff = ['-success', '-info', '-warning', '-danger']; ?>
									@foreach($rides as $index => $ride)
									<tr>
										<td >{{$ride->id}}</td>
										<td>{{$ride->booking_id}}</td>
										<td>
											@if($ride->s_address != '')
											{{$ride->s_address}}
											@else
											Not Provided
											@endif
										</td>
										<td>
											@if($ride->d_address != '')
											{{$ride->d_address}}
											@else
											Not Provided
											@endif
										</td>
										<td>
											@if($ride->status != "CANCELLED")
											<a class="text-primary" href="{{route('fleet.requests.show',$ride->id)}}"><span class="underline">View Ride Details</span></a>
											@else
											<span>No Details Found </span>
											@endif
										</td>
										@if(isset($statement_for) && $statement_for =="provider")
											<td>{{ currency($ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount) }}</td>
										@endif
										{{-- <td>{{ currency($ride->payment->pool_commission) }}</td> --}}
										
										{{-- @if(isset($statement_for) && $statement_for =="provider")
										<td>@if(in_array($ride->user_id, $fleet_user_ids)){{ currency($ride->payment->admin_commission) }} @else{{ currency(0.00) }}@endif</td>
										@endif --}}
										<td>
											<span class="text-muted">{{appDate($ride->created_at)}}</span>
										</td>
										<td>
											@if($ride->status == "COMPLETED")
											<span class="tag tag-success">{{$ride->status}}</span>
											@elseif($ride->status == "CANCELLED")
											<span class="tag tag-danger">{{$ride->status}}</span>
											@else
											<span class="tag tag-info">{{$ride->status}}</span>
											@endif
										</td>
										@if(isset($statement_for) && $statement_for !="user")
										<td>{{currency($ride->payment->provider_pay)}}</td>
										@else
										<td>{{currency($ride->payment->total)}}</td>
										@endif
									</tr>
									@endforeach

								<tfoot>
									<tr>
										<td>ID</td>
										<td>@lang('admin.request.Booking_ID')</td>
										<td>@lang('admin.request.picked_up')</td>
										<td>@lang('admin.request.dropped')</td>
										<td>@lang('admin.request.request_details')</td>
										@if(isset($statement_for) && $statement_for !="user")
										<td>@lang('admin.request.commission')</td>
										@endif
										{{-- <td>@lang('admin.custom.pool_commission')</td> --}}
										{{-- @if(isset($statement_for) && $statement_for !="user")
											<td>@lang('admin.custom.admin_commission')</td>
										@endif --}}
										<td>@lang('admin.request.date')</td>
										<td>@lang('admin.request.status')</td>
										@if(isset($statement_for) && $statement_for !="user")
										<td>@lang('admin.request.earned')</td>
										@else
										<td>@lang('admin.dashboard.total')</td>
										@endif
									</tr>
								</tfoot>
							</table>
							@else
								<h6 class="no-result">@lang('admin.custom.reds')</h6>
							@endif

						</div>
					</div>

				</div>

			</div>

		</div>
	</div>
</div>

@endsection

@section('scripts')
<script type="text/javascript">
	$(".showdate").on('click', function() {
		var ddattr = $(this).attr('id');
		//console.log(ddattr);
		if (ddattr == 'tday') {
			$("#from_date").val('{{$dates["today"]}}');
			$("#to_date").val('{{$dates["today"]}}');
		} else if (ddattr == 'yday') {
			$("#from_date").val('{{$dates["yesterday"]}}');
			$("#to_date").val('{{$dates["yesterday"]}}');
		} else if (ddattr == 'cweek') {
			$("#from_date").val('{{$dates["cur_week_start"]}}');
			$("#to_date").val('{{$dates["cur_week_end"]}}');
		} else if (ddattr == 'pweek') {
			$("#from_date").val('{{$dates["pre_week_start"]}}');
			$("#to_date").val('{{$dates["pre_week_end"]}}');
		} else if (ddattr == 'cmonth') {
			$("#from_date").val('{{$dates["cur_month_start"]}}');
			$("#to_date").val('{{$dates["cur_month_end"]}}');
		} else if (ddattr == 'pmonth') {
			$("#from_date").val('{{$dates["pre_month_start"]}}');
			$("#to_date").val('{{$dates["pre_month_end"]}}');
		} else if (ddattr == 'pyear') {
			$("#from_date").val('{{$dates["pre_year_start"]}}');
			$("#to_date").val('{{$dates["pre_year_end"]}}');
		} else if (ddattr == 'cyear') {
			$("#from_date").val('{{$dates["cur_year_start"]}}');
			$("#to_date").val('{{$dates["cur_year_end"]}}');
		} else {
			alert('invalid dates');
		}
	});

	$('#table-8').DataTable({
		responsive: true,
		paging: true,
		info: false,
		dom: 'Bfrtip',
		aaSorting: [[ 0, "desc" ]],
		buttons: [{
			extend: 'csv',
			text: 'CSV',
			charset: 'utf-8',
			extension: '.csv',
			fieldSeparator: ';',
			fieldBoundary: '',
			bom: true,
			exportOptions: {
				columns: ':not(:eq(3))'
			}
		},
			{
				text: 'CSV',
				action: function ( e, dt, node, config ) {
					var statement_for = $('#statement_for').val();
					var statement_id = $('#statement_id').val();
					var searchVal = $('.dataTables_filter input').val();
					var base = '{!! route('fleet.downloadExcel') !!}';
					var url = base + '?st=' + statement_for + '&id=' + statement_id + '&searchVal='+searchVal ;
					document.location.href = url;
				}
			}
		]
	});

	// function downloadPdf() {
	// 	// $("form#set-date :input").each(function() {
	// 	// 	var input = $(this);
	// 	// });
	// 	$("form#pdf-download").submit();
	// }
	// $('.dataTables_filter input').keyup(function() {
	// 	var value = $(this).val();
	// 	var csrf_token = $('meta[name="csrf_token"]').attr('content');
	// 	console.log(value);
	// 	$.ajax({
	// 		url: '/updateSearchValueSession',
	// 		type:'POST',
	// 		data : {searchval:value, _token:csrf_token},
	// 		success : function (response) {
	// 			console.log(response);
	// 		}
	// 	})
	// });
</script>
@endsection
