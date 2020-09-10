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
                <h5 class="mb-1">@lang('admin.advertisement.title')</h5>
                <a href="{{ route('admin.advertisement.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> @lang('admin.advertisement.add')</a>

                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>@lang('admin.id')</th>
                            <th>@lang('admin.advertisement.type') </th>
                            <th>@lang('admin.advertisement.image') </th>                           
                            <th>@lang('admin.advertisement.click_url') </th>
                            <th>@lang('admin.advertisement.status') </th>                           
                            <th>@lang('admin.advertisement.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($advertisements as $index => $advertisement)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $advertisement->type }}</td>
                            <td>
                                @if($advertisement->image) 
                                    <img src="{{$advertisement->image}}" style="height: 50px" >
                                @else
                                    N/A
                                @endif
                            </td>    
                            <td>{{ $advertisement->click_url }} </td>

                            <td>
                                @if($advertisement->status=='ACTIVE')
                                    <span class="tag tag-success">ACTIVE</span>
                                @else
                                    <span class="tag tag-danger">INACTIVE</span>
                                @endif
                            </td>
                           
                            <td>
                                <form action="{{ route('admin.advertisement.destroy', $advertisement->id) }}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="DELETE">
                                    <a href="{{ route('admin.advertisement.edit', $advertisement->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> @lang('admin.custom.edit')</a>
                                    <button class="btn btn-danger" onclick="return confirm('@lang('admin.custom.are_you_sure')')"><i class="fa fa-trash"></i> @lang('admin.custom.delete')</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>@lang('admin.id')</th>
                            <th>@lang('admin.advertisement.type') </th>
                            <th>@lang('admin.advertisement.image') </th>                           
                            <th>@lang('admin.advertisement.click_url') </th>
                            <th>@lang('admin.advertisement.status') </th>                           
                            <th>@lang('admin.advertisement.action')</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        </div>
    </div>
@endsection