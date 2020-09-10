@extends('fleet.layout.base')
@section('title', 'Private Pool')

@section('styles')

@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <h5 class="mb-1">@lang('admin.provides.pools')</h5>
                <a href="{{ route('admin.add.private_pool') }}" style="margin-left: 1em;" class="btn btn-primary pull-right">
                    <i class="fa fa-plus"></i> @lang('admin.include.add_new_pool')
                </a>
                    <table class="table table-striped table-bordered dataTable" id="table-6">
                        <thead>
                            <tr>
                                <th>@lang('admin.id')</th>
                                <th>@lang('admin.request.Pool_ID')</th>
                                <th>@lang('admin.request.Pool_Name')</th>
                                <th>@lang('admin.request.nbr_of_parents')</th>
                                <th>@lang('admin.request.Created_by')</th>
                                <th>@lang('admin.status')</th>
                                <th>@lang('admin.request.open')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($pool_data as $key=>$val)
                            <tr>
                                <td>{{$key + 1}}</td>
                                <td>{{$val->pool_id}}</td>
                                <td>{{$val->pool_name}}</td>
                                <td>{{ \App\PrivatePoolPartners::where(['pool_id'=>$val->id,'status'=>1])->count() + 1}}</td>
                                <td>
                                    @if($val->from_fleet_id == '0')
                                        {{ \App\Admin::where('id',1)->value('name') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($val->status == 1) <span style="color: green;">@lang('admin.poi.active')</span>
                                        @else
                                        <span style="color: red;">@lang('admin.poi.inactive')</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        //reflect the stauts between fleets likes accept, pending ,reject
                                        $status = \App\PrivatePoolPartners::where(['pool_id'=>$val->id,'fleet_id'=>$val->from_fleet_id, 'action_id'=> '0'])
                                        ->value('status');
                                        //get ride count of private pools
                                        $rideCount = \App\Pool::join('private_pool_requests','pools.request_id','=','private_pool_requests.request_id')->whereNull('pools.deleted_at')->where('private_pool_requests.private_id',$val->id)->count();
                                    @endphp
                                    @if($val->PrivatePoolID && $val->from_fleet_id == $logined_fleet_id && $rideCount > 0)
                                        <a href="{{ route('admin.open.private_pool',$val->id) }}" class="btn btn-info">
                                            <i class="fa fa-envelope-open"></i>  @lang('admin.request.open')
                                        </a>
                                    @elseif($val->PrivatePoolID && $status == '1' && $rideCount > 0)
                                        <a href="{{ route('admin.open.private_pool',$val->id) }}" class="btn btn-info">
                                            <i class="fa fa-envelope-open"></i>  @lang('admin.request.open')
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if ($val->from_fleet_id == '0' )
                                        <form action="{{ route('admin.delete.private_pool', ['id'=>$val->id]) }}" method="POST">
                                            {{ csrf_field() }}
                                            <a href="{{ route('admin.edit.private_pool', $val->id ) }}" class="btn btn-info">
                                                <i class="fa fa-pencil"></i>  @lang('admin.edit')
                                            </a>
                                            <button class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fa fa-trash"></i> @lang('admin.delete')
                                            </button>
                                        </form>
                                        @else
                                        @php
                                            $status = \App\PrivatePoolPartners::where(['pool_id'=>$val->id,'fleet_id'=>$val->from_fleet_id, 'action_id'=> \Illuminate\Support\Facades\Auth::user()->id])->value('status');
                                        @endphp
                                            @if ($status == '0')
                                                <form action="{{ route('admin.accept.private_pool', ['id'=>$val->id]) }}" method="POST" style="float: left;margin-right: 5px;">
                                                    {{ csrf_field() }}
                                                    <button class="btn btn-info">
                                                        <i class="fa fa-apple"></i> @lang('admin.fleets.accept')
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.refuse.private_pool', ['id'=>$val->id]) }}" method="POST">
                                                    {{ csrf_field() }}
                                                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="fa fa-reddit"></i> @lang('admin.fleets.reject')
                                                    </button>
                                                </form>
                                            @elseif($status == '1')
                                                <form action="{{ route('admin.refuse.private_pool', ['id'=>$val->id]) }}" method="POST">
                                                    {{ csrf_field() }}
                                                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="fa fa-reddit"></i> @lang('admin.fleets.reject')
                                                    </button>
                                                </form>
                                            @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>@lang('admin.id')</th>
                                <th>@lang('admin.request.Pool_ID')</th>
                                <th>@lang('admin.request.Pool_Name')</th>
                                <th>@lang('admin.request.nbr_of_parents')</th>
                                <th>@lang('admin.request.Created_by')</th>
                                <th>@lang('admin.status')</th>
                                <th>@lang('admin.request.open')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                        </tfoot>
                    </table>
            </div>

        </div>
    </div>

@endsection

@section('scripts')

@endsection
