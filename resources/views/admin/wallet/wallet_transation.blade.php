@extends('admin.layout.base')

@section('title', 'Admin Transactions')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">

        <div class="box box-block bg-white">
            <h5 class="mb-1">Total Transactions (@lang('provider.current_balance') : {{currency($wallet_balance)}}) @if($from_date) From {{appDate($from_date)}} @endif @if($to_date) To {{appDate($to_date)}} @endif</h5>
            @include('common.date_filter', ['action' => 'admin.transactions'])

            <table class="table table-striped table-bordered dataTable" id="table-6">
                <thead>
                    <tr>
                        <th>@lang('admin.sno')</th>
                        <th>@lang('admin.transaction_ref')</th>
                        <th>@lang('admin.datetime')</th>
                        <th>@lang('admin.transaction_desc')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.amount')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wallet_transation as $index=>$wallet)
                    <tr>
                        <td>{{$index}}</td>
                        <td>{{$wallet->transaction_alias}}</td>
                        <td>{{appDate($wallet->created_at)}}</td>
                        <td>{{$wallet->transaction_desc}}</td>
                        <td>{{$wallet->type == 'C' ? 'Credit' : 'Debit'}}</td>
                        <td>{{currency($wallet->amount)}}
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="color:red;">{{config('constants.booking_prefix', '') }} - Ride Transactions, PSET - Provider Settlements, FSET - Fleet Settlements, URC - User Recharges</p>
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