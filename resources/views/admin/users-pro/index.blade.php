@extends('admin.layout.base')

@section('title', 'Users ')

@section('content')
<div class="content-area py-1">
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
            <a href="{{ route('admin.user-pro.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New User</a>
            @endcan
            <table class="table table-striped table-bordered dataTable" id="table-5">
                <thead>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.user-pro.name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.country_code')</th>
                        <th>@lang('admin.mobile')</th>
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
                        <td>{{ $user->company_name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->country_code }}</td>
                        <td>{{ $user->mobile }}</td>
                        <td>{{currency($user->wallet_balance)}}</td>
                        <td>
                            <form action="{{ route('admin.user-pro.destroy', $user->id) }}" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="DELETE">
                                @can('user-history')
                                <a href="{{ route('admin.user.request', $user->id) }}" class="btn btn-info"><i class="fa fa-search"></i> @lang('admin.History')</a>
                                @endcan
                                @if( Setting::get('demo_mode', 0) == 0)

                                @can('user-create')
                                <a href="{{ route('admin.user-pro.edit', $user->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> @lang('admin.edit')</a>
                                @endcan

                                @can('user-delete')
                                <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> @lang('admin.delete')</button>
                                @endcan

                                @endif
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.user-pro.name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.mobile')</th>
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
    $('#table-5').DataTable( {
        responsive: true,
        paging:false,
            info:false,
            dom: 'Bfrtip',
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
            ]
    } );
</script>
@endsection
