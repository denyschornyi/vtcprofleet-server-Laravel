@extends('fleet.layout.base')

@section('title', 'Shape')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode', 0) == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : @lang('admin.demomode')
                </div>
                @endif
            <h5 class="mb-1">POI Category</h5>

            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>@lang('admin.service.peak_id')</th>
                        <th>@lang('admin.point.name')</th>
                        <th>@lang('admin.poi.poi_category')</th>
                        <th>@lang('admin.poi.action')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($polygon_shape as $index => $poi)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $poi->title }}</td>
                        <td>{{ $poi->type }}</td>
                        <td>
                            <form action="{{ route('fleet.polygonShape.destroy', $poi->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                @if( Setting::get('demo_mode', 0) == 0)
                                    <a href="{{ route('fleet.polygonShape.show', $poi->id) }}" class="btn btn-success">
                                        <i class="fa fa-book"></i> @lang('admin.poi.show')
                                    </a>
                                    <a href="{{ route('fleet.polygonShape.edit', $poi->id) }}" class="btn btn-info">
                                        <i class="fa fa-pencil"></i> @lang('admin.promocode.ediit')
                                    </a>
                                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fa fa-trash"></i> @lang('admin.reason.delle')
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
                        <th>@lang('admin.poi.poi_category')</th>
                        <th>@lang('admin.poi.action')</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
