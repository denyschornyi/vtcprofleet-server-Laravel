@extends('fleet.layout.base')

@section('title', 'Service Types ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode', 0) == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : @lang('admin.demomode')
                </div>
                @endif
            <h5 class="mb-1">Service Types</h5>

            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <!-- <th>Provider Name</th> -->
                        <th>Capacity</th>
                        <th>Base Price</th>
                        <th>Base Distance</th>
                        <th>Distance Price</th>
                        <th>Time Price</th>
                        <th>Hour Price</th>
                        <th>Price Calculation</th>
                        <th>Service Image</th>
                        <th>Service Marker</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($services as $index => $service)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $service->name }}</td>
                        <td>{{ $service->fleet_service_type->capacity }}</td>
                        <td>{{ currency($service->fleet_service_type->fixed) }}</td>
                        <td>{{ distance($service->fleet_service_type->distance) }}</td>
                        <td>{{ currency($service->fleet_service_type->price) }}</td>
                        <td>{{ currency($service->fleet_service_type->minute) }}</td>
                        @if($service->fleet_service_type->calculator == 'DISTANCEHOUR' || $service->fleet_service_type->calculator == 'HOUR')
                        <td>{{ currency($service->fleet_service->hour) }}</td>
                        @else
                        <td>No Hour Price</td>
                        @endif
                        <td>@lang('servicetypes.'.$service->fleet_service_type->calculator)</td>
                        <td>
                            @if($service->image)
                                <img src="{{$service->image}}" style="height: 50px" >
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($service->marker)
                                <img src="{{$service->marker}}" style="height: 50px" >
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('fleet.service.edit', $service->fleet_service_type->id) }}" class="btn btn-info btn-block">
                                <i class="fa fa-pencil"></i> Edit
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <!-- <th>Provider Name</th> -->
                        <th>Capacity</th>
                        <th>Base Price</th>
                        <th>Base Distance</th>
                        <th>Distance Price</th>
                        <th>Time Price</th>
                        <th>Hour Price</th>
                        <th>Price Calculation</th>
                        <th>Service Image</th>
                        <th>Service Marker</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
