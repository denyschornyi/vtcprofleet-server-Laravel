@extends('admin.layout.base')

@section('title', 'Payment History ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <h5 class="mb-1">@lang('admin.payment.payment_history') @if($from_date) From {{appDate($from_date)}} @endif @if($to_date) To {{appDate($to_date)}} @endif</h5>
                @include('common.date_filter', ['action' => 'admin.payment_provider'])

                <table class="table table-striped table-bordered dataTable" id="table-6">
                    <thead>
                        <tr>
                            <th id="top_td"></th>
                            <th>ID</th>
                            <th>@lang('admin.payment.transaction_id')</th>
                            <!-- <th>@lang('admin.payment.from')</th>
                            <th>@lang('admin.payment.to')</th> -->
                            <th>@lang('admin.payment.transaction_amount')</th>
                            <th>@lang('admin.payment.payment_type')</th>
                            
                            <th>@lang('admin.payment.provider_fleet_name')</th>
                            <th>@lang('admin.payment.date_time')</th>
                            <th>@lang('admin.payment.mode')</th>
                            {{-- <th>@lang('admin.action')</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($payments as $index => $payment)
                        <tr>
                            <td class="middle_td"></td>
                            <td>{{$index + 1}}</td>
                            <td>@if(!empty($payment->alias_id)){{$payment->alias_id}}@else NA @endif</td>
                            <!-- <td>{{$payment->user?$payment->user->first_name:''}} {{$payment->user?$payment->user->last_name:''}}</td>
                            <td>{{$payment->provider?$payment->provider->first_name:''}} {{$payment->provider?$payment->provider->last_name:''}}</td> -->
                            <td>{{currency($payment->amount)}}</td>
                            <td>{{$payment->type == 'C'?'Credit':'Debit'}}</td>
                            <td>{{$payment->provider?$payment->provider->first_name." ". $payment->provider->last_name : $payment->fleet->company}}</td>
                            <td>
                                {{ date_format($payment->updated_at,"d/m/Y - H:i:s") }}
                            </td>
                            @if($payment->mode == 'online')
                            <td>Card</td>
                            @elseif($payment->mode == 'offline')
                            <td>Cash</td>
                            @elseif($payment->mode == 'wire')
                            <td>Wire Bank</td>
                            @elseif($payment->mode == 'cheque')
                            <td>Cheque</td>
                            @else
                            <td>N/A</td>
                            @endif
                            {{-- <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary waves-effect dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    Action
                                </button>
                                <div class="dropdown-menu">
                                    <a href="{{ route('admin.requests.show', $payment->id) }}" class="dropdown-item">
                                        <i class="fa fa-search"></i> More Details
                                    </a>
                                </div>
                            </div>
                        </td> --}}
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th id="bottom_td"></th>
                            <th>ID</th>
                            <th>@lang('admin.payment.transaction_id')</th>
                            <!-- <th>@lang('admin.payment.from')</th>
                            <th>@lang('admin.payment.to')</th> -->
                            <th>@lang('admin.payment.transaction_amount')</th>
                            <th>@lang('admin.payment.payment_type')</th>
                            <th>@lang('admin.payment.provider_fleet_name')</th>
                            <th>@lang('admin.payment.date_time')</th>
                            <th>@lang('admin.payment.mode')</th>
                            {{-- <th>@lang('admin.action')</th> --}}
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script type="text/javascript">
    $(".showdate").on('click', function() {
        var ddattr = $(this).attr('id');
        // console.log(ddattr);
        if (ddattr == 'tday') {
            $("#from_date").val('{{$dates["today"]}}');
            $("#to_date").val('{{$dates["today"]}}');
            $('#date_filter').val('tday');
        } else if (ddattr == 'yday') {
            $("#from_date").val('{{$dates["yesterday"]}}');
            $("#to_date").val('{{$dates["yesterday"]}}');
            $('#date_filter').val('yday');
        } else if (ddattr == 'cweek') {
            $("#from_date").val('{{$dates["cur_week_start"]}}');
            $("#to_date").val('{{$dates["cur_week_end"]}}');
            $('#date_filter').val('cweek');
        } else if (ddattr == 'pweek') {
            $("#from_date").val('{{$dates["pre_week_start"]}}');
            $("#to_date").val('{{$dates["pre_week_end"]}}');
            $('#date_filter').val('pweek');
        } else if (ddattr == 'cmonth') {
            $("#from_date").val('{{$dates["cur_month_start"]}}');
            $("#to_date").val('{{$dates["cur_month_end"]}}');
            $('#date_filter').val('cmonth');
        } else if (ddattr == 'pmonth') {
            $("#from_date").val('{{$dates["pre_month_start"]}}');
            $("#to_date").val('{{$dates["pre_month_end"]}}');
            $('#date_filter').val('pmonth');
        } else if (ddattr == 'pyear') {
            $("#from_date").val('{{$dates["pre_year_start"]}}');
            $("#to_date").val('{{$dates["pre_year_end"]}}');
            $('#date_filter').val('pyear');
        } else if (ddattr == 'cyear') {
            $("#from_date").val('{{$dates["cur_year_start"]}}');
            $("#to_date").val('{{$dates["cur_year_end"]}}');
            $('#date_filter').val('cyear');
        } else {
            alert('invalid dates');
            $('#date_filter').val('');
        }
    });
</script>
@endsection
