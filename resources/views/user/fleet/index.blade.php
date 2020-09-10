<style>
    /* The Modal (background) */
    .modal,
    .modal1 {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        /* padding-top: 100px; */
        /* Location of the box */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content,
    .modal-content1 {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }


    /* The Close Button */
    .close,
    .close1 {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close1:hover,
    .close:focus,
    .close1:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-icon-box {
        border: 2px solid #ccc;
        padding: 5px 10px;
        border-radius: 5px;
        background: 0 0;
    }

    #view-invoice {
        margin-top: 0 !important;
        top: 0 !important;
    }

    .ui.dimmer {
        background-color: rgba(0, 0, 0, 0.4) !important;
    }

    table.dataTable > tbody > tr.child .open > ul.dropdown-menu {
        display: block !important;
    }

    table.dataTable > tbody > tr.child ul.dropdown-menu {
        display: none !important;
    }

    .input-group-btn, a.btn.downloadpdf {
        /* vertical-align: inherit !important; */
    }

    th {
        color: rgba(0, 0, 0, 0.87);
        font: 16px Lato, sans-serif;
        padding: 10.5px 30px 10.5px 10.5px;
        font-weight: bold;
    }

    td {
        color: rgba(0, 0, 0, 0.87);
        font: 16px Lato, sans-serif;
        padding: 10.5px;
    }
</style>
@extends('user.layout.base')

@section('title', 'Users ')

@section('content')
    <div class="content-area py-1" style="padding-top: 1rem!important;padding-bottom: 1rem!important;">
        @include('admin.invoice.user-invoice')
        <div class="container-fluid">
            <div class="box box-block bg-white">
                @if(Setting::get('demo_mode', 0) == 1)
                    <div class="col-md-12" style="height:50px;color:red;">
                        ** Demo Mode : @lang('admin.demomode')
                    </div>
                @endif
                <h5 class="mb-1">
                    @lang('admin.users.Users')
                    @if(Setting::get('demo_mode', 0) == 1)
                        <span class="pull-right">(*personal information hidden in demo)</span>
                    @endif
                </h5>
                <a href="{{ route('user-passenger.create') }}" style="margin-left: 1em;"
                   class="btn btn-primary pull-right"><i class="fa fa-plus"
                                                         style="color: white;"></i> @lang('admin.include.add_new_user')
                </a>

                <table class="table table-striped table-bordered dataTable" id="table-5">
                    <thead>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.first_name')</th>
                        <th>@lang('admin.last_name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.users.Rating')</th>
                        <th>@lang('admin.users.Wallet_Amount')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    <tfoot>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.first_name')</th>
                        <th>@lang('admin.last_name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.users.Rating')</th>
                        <th>@lang('admin.users.Wallet_Amount')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="transferModal" class="modal fade" role="dialog" data-backdrop="static" aria-hidden="true"
         data-keyboard="false">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content" style="width: 50%;">
                <div class="modal-header" style="padding: 15px 15px 10px 0px;">
                    <h4 class="modal-title" id="settitle">@lang('admin.custom.add_wallet')</h4>
                </div>
                <!-- <form action="" method="Get" id="transurl"> -->
                <div class="modal-body" style="padding: 15px 0px 15px 0px;">
                    <div id="sendbody" style="">
                        <div class="alert alert-warning alert-dismissible" style="display: none;">
                        </div>
                        <input type="number" required name="wallet_amount" id="wallet_amount" class="form-control"
                               placeholder="" value="1" max="100" min="1">
                        <input type="hidden" id="req_val" name="req_val" value="">
                    </div>
                </div>
                <div class="modal-footers">
                    <button type="submit" class="btn btn-success">@lang('admin.payment.coor')</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"
                            style="float: right;">@lang('admin.fleets.cancel')</button>
                </div>
                <!-- </form> -->
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        jQuery.fn.DataTable.Api.register('buttons.exportData()', function (options) {
            if (this.context.length) {
                var jsonResult = $.ajax({
                    url: "{{url('user-passenger')}}?download=all",
                    data: {},
                    success: function (result) {
                        p = [];
                        $.each(result.data, function (i, d) {
                            var item = [d.id, d.first_name, d.last_name, d.email, d.country_code, d.mobile, d.rating, d.wallet_balance];
                            p.push(item);
                        });
                    },
                    async: false
                });
                var head = [];
                head.push("ID", "First Name", "Last Name", "Email", "Country Code", "Mobile", "Rating", "Wallet Amount");
                return {
                    body: p,
                    header: head
                };
            }
        });

        var dataTable = $('#table-5').DataTable({
            responsive: true,
            paging: true,
            info: false,
            dom: 'Bfrtip',
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('user-passenger.index')}}",
                type: 'GET',
                // dataSrc: function(data) {
                //     console.log(JSON.stringify(data));

                //     return true;
                // },
            },
            columns: [
                {"data": "id"},
                {"data": "first_name"},
                {"data": "last_name"},
                {"data": "email"},
                {"data": "country_code"},
                {"data": "mobile"},
                {"data": "rating"},
                {"data": "wallet_balance"},
                {"data": ""}
            ],
            columnDefs: [
                {
                    render: function (data, type, row) {
                        return actionField(data, type, row);
                    },
                    orderable: false,
                    targets: 8
                },
            ]
        });
        var url = "{{url('user-passenger/')}}/";
        {{--var url1 = "{{url('admin/user/invoice_info/')}}/";--}}
            actionField = function (data, type, row) {

            var html = '<div class="input-group-btn">';
            html += '<button type="button" ';
            html += 'class="btn btn-info btn-block dropdown-toggle"';
            html += 'data-toggle="dropdown">@lang('admin.action')';
            html += '<span class="caret" style="color: white;margin-left: 5px;"></span>';
            html += '</button>';
            html += '<ul class="dropdown-menu" style="padding:4px 0">';
            html += '<li><a class="btn btn-default" style="display:inline-block;text-decoration:none;border:0px;padding:3px 9px;color:blue;" data-toggle="modal" data-target="#transferModal" data-id="' + row['id'] + '" id="wallet_btn" ><i class="fa fa-money" style="color: #4183c4;"></i> Wallet</a></li>';
            html += '<li><a href="' + url + row['id'] + '/request" class="btn btn-default" style="display:inline-block;text-decoration:none;border:0px;padding:3px 9px;color:blue;" ><i class="fa fa-search" style="color: #4183c4;"></i> @lang('admin.History')</a></li>';
            @if( Setting::get('demo_mode', 0)    == 0)
                html += '<li>';
            html += '    <a href="' + url + row['id'] + '/edit" class="btn btn-default" style="display:inline-block;text-decoration:none;border:0px;padding:3px 9px;color:blue;"><i class="fa fa-pencil" style="color:blue;"></i> @lang('admin.edit')</a>';
            html += '</li>';

            html += '<li>';
            html += '    <form action="' + url + row['id'] + '" method="POST" style="margin:0">';
            html += '        {{ csrf_field() }}';
            html += '        <input type="hidden" name="_method" value="DELETE">';
            html += '        <button class="btn btn-default look-a-like" style="display:inline-block;text-decoration:none;border:0px;" onclick="return confirm(\'Are you sure?\')"><i class="fa fa-trash" style="color:red;padding-right:5px;"></i>@lang('admin.delete')</button>';
            html += '    </form>';
            html += '</li>';
            @endif
                html += '</ul>';
            html += '</div>';
            return html;
        };

        $("#table-5").on('click', '#wallet_btn', function (e) {
            e.preventDefault();
            let req_id = $(this).data('id');
            // let wallet_ballance = $(this).data('wallet_limit');
            $("#req_val").val(req_id);
        });

        $('.btn.btn-success').on('click', function (e) {
            let id = $("#req_val").val();
            let wallet_amount = $("#wallet_amount").val();
            console.log(id);
            if (wallet_amount < 0 || wallet_amount >= 101) {
                $(".alert-warning").css('display', 'block');
                $(".alert-warning").text('Please insert correct amount');
            } else {
                $.ajax({
                    type: 'get',
                    url: "{{ route('passenger.wallet_transfer') }}",
                    data: {wallet_amount: wallet_amount, id: id},
                    success: function (response) {
                        if (!response.success)
                        {
                            if(response.limit === 1)
                            {
                                $(".alert-warning").css('display', 'block');
                                $(".alert-warning").text("Wallet limit is excess.");
                            }else{
                                $(".alert-warning").css('display', 'block');
                                $(".alert-warning").text("Wallet amount is " + response.amount);
                            }

                        } else {
                            window.location.reload();
                        }
                    }
                });

            }
        });


        $('#table-5').on('click', '.downloadpdf', function (e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr('href'),
                data: {},
                success: function (result) {
                    if (result && !result.error) {
                        $('#view-invoice').css("margin-top", "0px");
                        var id = $(this).attr('idx');
                        if ('COMPANY' == result.user.user_type) {
                            $('.bill_from_first_name').html(result.user.company_name);
                            $('.bill_from_last_name').html('');
                            $('#bill_from_first_name').val(result.user.company_name);
                            $('#bill_from_last_name').val('');
                        } else {
                            $('.bill_from_first_name').html(result.user.first_name);
                            $('.bill_from_last_name').html(result.user.last_name);
                            $('#bill_from_first_name').val(result.user.first_name);
                            $('#bill_from_last_name').val(result.user.last_name);
                        }
                        $('.ride_total').html(result.total);
                        $('.ride_total_paid').html(result.total_paid);
                        $('.ride_total_unpaid').html(result.unpaid);
                        $('#ride_total').val(result.total);
                        $('#ride_total_paid').val(result.total_paid);
                        $('#ride_total_unpaid').val(result.unpaid);

                        $('.user_first_name').html(result.user.first_name);
                        $('.user_last_name').html(result.user.last_name);
                        $('#user_id').val(result.user.id);

                        $('#view-invoice').modal('show');
                        $("#view-invoice").scrollTop(0)
                    }
                },
                error: function (error) {
                    console.log(error);
                },
                async: false
            });
            e.returnValue = false;
            return false;
        });
        // $('#view-invoice').modal('show');

        $('a#download_pdf').on('click', function () {
            $('#formDownloadPDF').submit();
        });
    </script>
@endsection
