@extends('admin.layout.base')

@section('title', 'Provider Request')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
        @if($utype=='provider') @php($flag=1) @else @php($flag=2) @endif
        <div class="box box-block bg-white">
            
            <h5 class="mb-1">Fleet Pending Request @if($from_date) From {{appDate($from_date)}} @endif @if($to_date) To {{appDate($to_date)}} @endif</h5>
            
            @include('common.date_filter', ['action' => 'admin.payment_demand'])

            <table class="table table-striped table-bordered dataTable" id="table-6">
                <thead>
                    <tr>
                        <th>@lang('admin.sno')</th>
                        <th>@lang('admin.transaction_ref')</th>
                        <th>@lang('admin.datetime')</th>
                        <th>@lang('admin.fleet.fleet_name')</th>
                        <th>@lang('admin.fleet.number')</th>
                        <th>@lang('admin.amount')</th>
                        {{-- <th>@lang('admin.action')</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @php($total=0)
                    @foreach($pendinglist as $index=>$pending)
                    @php($total+=$pending->amount)
                    <tr>
                        <td>{{$index+1}}</td>
                        <td>{{$pending->alias_id}}</td>
                        <td>{{appDate($pending->created_at)}}</td>
                        
                        <td>{{$pending->company}} </td>
                       
                        <td>{{ $pending->country_code." ".$pending->mobile }}</td>
                       
                        <td>{{currency($pending->amount)}}</td>
                        {{-- <td>
                            @if( Setting::get('demo_mode', 0) == 0)
                            <!-- <a class="btn btn-success btn-block" href="{{ route('admin.approve', $pending->id) }}">@lang('admin.approve')</a> -->
                            <button type="button" class="btn btn-success btn-block transferClass" data-toggle="modal" data-target="#transferModal" data-id="send" data-href="{{route('admin.approve', $pending->id) }}" data-rid="{{$pending->id}}">@lang('admin.approve')</button>
                            <!-- <a class="btn btn-danger btn-block" href="{{ route('admin.cancel') }}?id={{$pending->id}}">@lang('admin.cancel')</a> -->

                            <button type="button" class="btn btn-danger btn-block transferClass" data-toggle="modal" data-target="#transferModal" data-id="cancel" data-href="{{ route('admin.cancel') }}?id={{$pending->id}}" data-rid="{{$pending->id}}">@lang('admin.cancel')</button>
                            @endif
                        </td> --}}

                    </tr>
                    @endforeach
                    
                </tbody>
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
