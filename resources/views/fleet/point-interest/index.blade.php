@extends('fleet.layout.base')

@section('title', 'Point Interest ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode', 0) == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : @lang('admin.demomode')
                </div>
                @endif
            <h5 class="mb-1">@lang('admin.point.point_interest')</h5>
{{--            @can('service-types-create')--}}
            <a href="{{ route('fleet.pointInterest.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i>@lang('admin.point.Add_Point_Interest')</a>
{{--            @endcan--}}
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>@lang('admin.service.peak_id')</th>
                        <th>@lang('admin.point.name')</th>
                        <th>@lang('admin.point.price')</th>
                        <th>@lang('admin.poi.status')</th>
                        <th>@lang('admin.poi.action')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($point_interest as $index => $point)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $point->rule_name }}</td>
                        <td>{{ $point->price }}</td>
                        <td>
                            @if($point->status === 1) <span style="color: green;">@lang('admin.poi.active')</span>
                            @elseif($point->status === 0)<span style="color: red;">@lang('admin.poi.inactive')</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('fleet.pointInterest.destroy', $point->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                @if( Setting::get('demo_mode', 0) == 0)
                                <a href="{{ route('fleet.pointInterest.show', $point->id) }}" class="btn btn-success">
                                    <i class="fa fa-book"></i> @lang('admin.poi.show')
                                </a>

                                <a href="{{ route('fleet.pointInterest.edit', $point->id) }}" class="btn btn-info">
                                    <i class="fa fa-pencil"></i> @lang('admin.poi.edit')
                                </a>

                                <button class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fa fa-trash"></i> @lang('admin.poi.delete')
                                </button>

                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>@lang('admin.service.peak_id')</th>
                        <th>@lang('admin.point.name')</th>
                        <th>@lang('admin.point.price')</th>
                        <th>@lang('admin.poi.status')</th>
                        <th>@lang('admin.poi.action')</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
