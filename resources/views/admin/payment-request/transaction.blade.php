@extends('admin.layout.base')

@section('title', 'Payment Request')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">User Payment Request @if($from_date) From {{appDate($from_date)}} @endif @if($to_date) To {{appDate($to_date)}} @endif</h5>
            @include('common.date_filter', ['action' => 'admin.payment.transactions'])

            <table class="table table-striped table-bordered dataTable" id="table-6">
                <thead>
                    <tr>
                        <th>@lang('admin.sno')</th>
                        <th>@lang('admin.transaction_ref')</th>
                        <th>@lang('admin.datetime')</th>
                        <th>@lang('admin.user-pro.name')</th>
                        <th>@lang('admin.amount')</th>
                        <th>@lang('admin.reason.status')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $index=>$pending)
                    <tr>
                        <td>{{$index+1}}</td>
                        <td>{{$pending->alias_id}}</td>
                        <td>{{appDate($pending->created_at)}}</td>
                        <td>{{$pending->user->company_name}} </td>
                        <td>{{currency($pending->amount)}}</td>
                        <td><span style="color:white; padding:6px 10px; border-radius:3px; @if($pending->status == 'Pending') background-color:orange @elseif($pending->status == 'Accepted') background-color:green @elseif($pending->status == 'Refused') background-color:red @endif">{{$pending->status}}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal -->
<div id="transferModal" class="modal fade" role="dialog" data-backdrop="static" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->
                <h4 class="modal-title" id="settitle"></h4>
            </div>
            <form action="" method="Get" id="transurl">
                <div class="modal-body">
                    <div id="sendbody" style="display:none">
                        <div class="alert alert-warning alert-dismissible">
                            <strong>Warning!</strong> <span id="setbody">Are you sure want to complete this transaction</span>
                        </div>
                    </div>
                    <div id="cancelbody" style="display:none">
                        <div class="alert alert-warning alert-dismissible">
                            <strong>Warning!</strong> <span id="setbody">Are you sure want to cancel this transaction.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirm</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">close</button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    // $(function () {
    //     $(".transferClass").click(function () {
    //         var curl = $(this).attr('data-href');
    //         var page = $(this).attr('data-id');
    //         $("#transurl").attr('action',curl);
    //         if(page=='send'){
    //             $("#settitle").text('Confirm Settlement');
    //             $("#cancelbody").hide();
    //             $("#sendbody").show();
    //         }
    //         else{
    //             $("#settitle").text('Cancel Settlement');
    //             $("#sendbody").hide();
    //             $("#cancelbody").show();
    //         }
            
    //     })
    // });
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