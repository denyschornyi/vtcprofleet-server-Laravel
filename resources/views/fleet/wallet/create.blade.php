@extends('fleet.layout.base')

@section('title', 'Create Settlement')

@section('content')

<?php
	if($type==1){
		$title=Lang::get('admin.prd_settle');
		$back_route="fleet.providertransfer";
	}
	else{
		$title=Lang::get('admin.flt_settle');
		$back_route="fleet.fleettransfer";
	}

?>
<style>
.input-group{
	width: none;
}
.input-group .fa-search{
  display: table-cell;
}
</style>
<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{route($back_route)}}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">{{$title}}</h5>

            <form class="form-horizontal" action="{{route('fleet.transferstore')}}" method="POST" enctype="multipart/form-data" role="form" autocomplete="off">
            	{{csrf_field()}}
				<div class="form-group row">
					@if($type==1)
						<label for="namesearch" class="col-xs-2 col-form-label">@lang('admin.service.Provider_Name')</label>
					@else
						<label for="namesearch" class="col-xs-2 col-form-label">@lang('admin.fleet.fleet_name')</label>
					@endif
					<div class="col-xs-5">
						<div class="input-group">
							<input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="namesearch" placeholder="@lang('admin.fleet.flesearchs')" required="" aria-describedby="basic-addon2">
						 	<span class="input-group-addon fa fa-search"  id="basic-addon2"></span>
						</div>
						<input type="hidden" name="stype" value="{{$type}}">
						<input type="hidden" name="from_id" id="from_id" value="">
					</div>
				</div>

				<div class="form-group row">
					<label for="amount" class="col-xs-2 col-form-label">@lang('admin.amount')</label>
					<div class="col-xs-5">
						<input class="form-control" type="number" value="{{ old('amount') }}" name="amount" required id="amount" placeholder="@lang('admin.fleet.fleamot')" required="" min="1">
					</div>
					<div class="col-xs-5">

						<span class="showcal">
						<i><b>@lang('admin.fleet.flebalance')
						<span id="wallet_balance"></span>
						</b></i>
						</span>
						<input type="hidden" name="wallet_balance" id="balance_of_wallet" value="" />
					</div>
				</div>

				<div class="form-group row">
					<label for="type" class="col-xs-2 col-form-label">@lang('admin.type')</label>
					<div class="col-xs-5">
						<select class="form-control" name="type">
							<option value="C">@lang('admin.fleet.flecredit')</option>
							<option value="D">@lang('admin.fleet.fledebit')</option>
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="send_by" class="col-xs-2 col-form-label">@lang('admin.fleet.payment_mode')</label>
					<div class="col-xs-5">
						<select class="form-control" name="send_by">
							<option value="online">@lang('admin.fleet.flonline')</option>
							<option value="offline">@lang('admin.fleet.floffli')</option>	
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-5">
						<button type="submit" class="btn btn-primary">@lang('admin.fleet.ttppco')</button>
						<a href="{{route($back_route)}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

<link href="{{ asset('asset/css/jquery-ui.css') }}" rel="stylesheet">

@endsection

@section('scripts')

<script type="text/javascript" src="{{ asset('asset/js/jquery-ui.js') }}"></script>

<script type="text/javascript">
var sflag='{{$type}}';
$('#namesearch').autocomplete({
    source: function(request, response) {
	    $.ajax
	    ({
	        type: "GET",
	        url: '{{ route("fleet.transfersearch") }}',
	        data: {stext:request.term,sflag:sflag},
	        dataType: "json",
	        success: function(responsedata, status, xhr)
	        {
	            if (!responsedata.data.length) {
	                var data=[];
	                data.push({
	                        id: 0,
	                        label:"@lang('admin.fleet.noreco')"
	                });
	                response(data);
	            }
	            else{
	             response( $.map(responsedata.data, function( item ) {
	                    if(sflag==1)
	                        var name_alias=item.first_name+" - "+item.id;
	                    else
	                        var name_alias=item.name+" - "+item.id;

	                    return {
	                        value: name_alias,
	                        id: item.id,
	                        bal: item.wallet_balance
	                    }
	                }));
	            }
	        }
	    });
	},
	minLength: 2,
	change:function(event,ui)
	{
	    if (ui.item==null){
	        $("#namesearch").val('');
	        $("#namesearch").focus();
	        $("#wallet_balance").text("-");
	    }
	    else{
	        if(ui.item.id==0){
	            $("#namesearch").val('');
	            $("#namesearch").focus();
	            $("#wallet_balance").text("-");
	        }
	    }
	},
	select: function (event, ui) {
	    $("#from_id").val(ui.item.id);
		$("#balance_of_wallet").val(ui.item.bal);
	    $("#wallet_balance").text(ui.item.bal);
	}
});

</script>
@endsection
