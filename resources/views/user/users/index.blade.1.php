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

    table.dataTable>tbody>tr.child .open>ul.dropdown-menu {
        display: block !important;
    }

    table.dataTable>tbody>tr.child ul.dropdown-menu {
        display: none !important;
    }

    .input-group-btn, a.btn.downloadpdf {
        vertical-align: inherit !important;
    }

</style>
@extends('admin.layout.base')

@section('title', 'Users ')

@section('content')
<div class="content-area py-1">
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
            @can('user-create')
            <a href="{{ route('admin.user.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> @lang('admin.include.add_new_user')</a>
            @endcan
            <table class="table table-striped table-bordered dataTable" id="table-5">
                <thead>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.first_name')</th>
                        <th>@lang('admin.last_name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.users.Rating')</th>
                        <th>@lang('admin.users.Wallet_Amount')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @php($page = ($pagination->currentPage-1)*$pagination->perPage)
                    @foreach($users as $index => $user)
                    @php($page++)
                    <tr>
						                        <td>{{ $page }}</td>
						                        <td>{{ $user->first_name }}</td>
						                        <td>{{ $user->last_name }}</td>
                        @if(Setting::get('demo_mode', 0) == 1)
						                        <td>{{ substr($user->email, 0, 3).'****'.substr($user->email, strpos($user->email, "@")) }}</td>
                        @else
						                        <td>{{ $user->email }}</td>
                        @endif
                        @if(Setting::get('demo_mode', 0) == 1)
						                        <td>+33612345678</td>
                        @else
						                        <td>{{ $user->country_code }}{{ $user->mobile }}</td>
                        @endif
						                        <td>{{ $user->rating }}</td>
						                        <td>{{currency($user->wallet_balance)}}</td>
						                        <td>
                            <div class="input-group-btn">
                                <button type="button" 
                                    class="btn btn-info btn-block dropdown-toggle"
                                    data-toggle="dropdown">@lang('admin.action')
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" style="padding:4px 0">
                                    @can('user-history')
                                    <li><a href="{{ route('admin.user.request', $user->id) }}" class="btn btn-default"><i class="fa fa-search"></i> @lang('admin.History')</a></li>
                                    @endcan
                                    @if( Setting::get('demo_mode', 0) == 0)

                                    @can('user-create')
                                    <li>
                                        <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-default"><i class="fa fa-pencil"></i> @lang('admin.edit')</a>
                                    </li>
                                    @endcan

                                    <li>
                                        <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" style="margin:0">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button class="btn btn-default look-a-like" onclick="return confirm('@lang('admin.custom.are_you_sure')')"><i class="fa fa-trash"></i>@lang('admin.delete')</button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            <div class="input-group-btn" style="left:5px">
                                <a href="{{ route('admin.user.invoice.info', $user->id) }}" class="btn btn-info downloadpdf" style="background-color:#b531ba;border-color:#b531ba;"><i class=""></i>@lang('admin.provides.inv')</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.first_name')</th>
                        <th>@lang('admin.last_name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.users.Rating')</th>
                        <th>@lang('admin.users.Wallet_Amount')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table>
            @include('common.pagination')
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    jQuery.fn.DataTable.Api.register('buttons.exportData()', function(options) {
        if (this.context.length) {
            var jsonResult = $.ajax({
                url: "{{url('admin/user')}}?page=all",
                data: {},
                success: function(result) {
                    p = new Array();
                    $.each(result.data, function(i, d) {
                        var item = [d.id, d.first_name, d.last_name, d.email, d.mobile, d.rating, d.wallet_balance];
                        p.push(item);
                    });
                },
                async: false
            });
            var head = new Array();
            head.push("ID", "First Name", "Last Name", "Email", "Mobile", "Rating", "Wallet Amount");
            return {
                body: p,
                header: head
            };
        }
    });

    var dataTable = $('#table-5').DataTable({
        responsive: true,
        paging: false,
        info: false,
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ]
    });

    $('#table-5').on('click', '.downloadpdf', function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('href'),
            data: {},
            success: function(result) {
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

                    $('#view-invoice').modal('show');
                    $("#view-invoice").scrollTop(0)
                }
            },
            error: function(error) {
                console.log(error);
            },
            async: false
        });
        e.returnValue = false;
        return false;
    });
    // $('#view-invoice').modal('show');

    $('a#download_pdf').on('click', function() {
        $('#formDownloadPDF').submit();
    });
</script>
@endsection