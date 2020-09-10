@extends('fleet.layout.base')

@section('title', 'POI Category ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode', 0) == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : @lang('admin.demomode')
                </div>
                @endif
            <h5 class="mb-1">@lang('admin.poi.poi_category')</h5>
            <a href="{{ route('fleet.poiCategory.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> @lang('admin.poi.Add_Poi_Category')</a>

            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>@lang('admin.service.peak_id')</th>
                        <th>@lang('admin.poi.type')</th>
                        <th>@lang('admin.poi.image')</th>
                        <th>@lang('admin.poi.status')</th>
                        <th>@lang('admin.poi.action')</th>
                    </tr>
                </thead>
                <tbody>

                @foreach($poi_category as $index => $poi)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $poi->type }}</td>
                        <td><img style="height: 20px; margin-bottom: 15px; border-radius:2em;" src="{{img( $poi->image )}}"></td>
                        <td>
                            @if($poi->status === 1) <span style="color: green;">Active</span>
                            @else <span style="color: red;">InActive</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('fleet.poiCategory.destroy', $poi->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                @if( Setting::get('demo_mode', 0) == 0)
                                    <a href="{{ route('fleet.poiCategory.edit', $poi->id) }}" class="btn btn-info">
                                        <i class="fa fa-pencil"></i> Edit
                                    </a>
                                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fa fa-trash"></i> Delete
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
                        <th>@lang('admin.poi.type')</th>
                        <th>@lang('admin.poi.image')</th>
                        <th>@lang('admin.poi.status')</th>
                        <th>@lang('admin.poi.action')</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
