@extends('admin.layout.base')

@section('title')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">

            <div class="box box-block bg-white">
                @if(Setting::get('demo_mode', 0) == 1)
                    <div class="col-md-12" style="height:50px;color:red;">
                        ** Demo Mode : @lang('admin.demomode')
                    </div>
                @endif
                <h5 class="mb-1">@lang('admin.dispute.title')</h5>
                @can('dispute-create')
                <a href="{{ route('admin.drivingzone.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> @lang('admin.driving_zone.add_driving_zone')</a>
                @endcan

                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>@lang('admin.id')</th>
                            <th>@lang('admin.driving_zone.country') </th>
                            <th>@lang('admin.driving_zone.location')</th>
                            <th>@lang('admin.driving_zone.radius')</th>
                            <th>@lang('admin.status')</th>
                            <th>@lang('admin.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $index => $dist)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            @if($dist->status == "country")
                                <td>{{ ucfirst($dist->country_list) }}</td>
                            @else
                                <td>{{ ucfirst($dist->country) }}</td>
                            @endif
                            <td>{{ ucfirst($dist->location) }} </td>
                            <td>{{ ucfirst($dist->radius) }} </td>
                            <td>
                                @if($dist->active=='1')
                                    <span class="tag tag-success">@lang('admin.reason.acht') </span>
                                @else
                                    <span class="tag tag-danger">@lang('admin.reason.inacts') </span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.drivingzone.destroy', $dist->id) }}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="DELETE">
                                    @if( Setting::get('demo_mode', 0) == 0)
                                    @can('dispute-edit')
                                    <a href="{{ route('admin.drivingzone.edit', $dist->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> @lang('admin.reason.ediis')</a>
                                    @endcan
                                    @can('dispute-delete')
                                    <button class="btn btn-danger" onclick="return confirm('@lang('admin.custom.are_you_sure')')"><i class="fa fa-trash"></i> @lang('admin.reason.delle')</button>
                                    @endcan
                                    <a href="{{ route('admin.drivingzone.active', $dist->id) }}" class="btn btn-info"><i class="fa fa-car"></i> @lang('admin.poi.active')</a>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>@lang('admin.id')</th>
                            <th>@lang('admin.driving_zone.country') </th>
                            <th>@lang('admin.driving_zone.location')</th>
                            <th>@lang('admin.driving_zone.radius')</th>
                            <th>@lang('admin.status')</th>
                            <th>@lang('admin.action')</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
@endsection
