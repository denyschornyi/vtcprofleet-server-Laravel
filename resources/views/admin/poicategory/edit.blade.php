@extends('admin.layout.base')

@section('title', 'Update Service Type ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <a href="{{ route('admin.poicategory.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

                <h5 style="margin-bottom: 2em;">@lang('admin.poi.Update_Poi_Category')</h5>

                <form class="form-horizontal" action="{{route('admin.poicategory.update',$poi_category->id)}}" method="POST" enctype="multipart/form-data" role="form">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="PATCH">
                    <div class="form-group row">
                        <label for="name" class="col-xs-12 col-form-label">@lang('admin.type')</label>
                        <div class="col-xs-12">
                            <input class="form-control" type="text" value="{{ $poi_category->type }}" name="type" required id="type" placeholder="Category type name">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="picture" class="col-xs-12 col-form-label">
                            @lang('admin.service.Image')</label>
                        <div class="col-xs-12">

                            @if(isset($poi_category->image))
                                <img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{img($poi_category->image)}}">
                            @endif
                                <input type="file" accept="image/*" name="image" class="dropify form-control-file" id="image" aria-describedby="fileHelp">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="calculator" class="col-xs-12 col-form-label">@lang('admin.poi.status')</label>
                        <div class="col-xs-12">
                            <select class="form-control" id="status" name="status">
                                <option value="1" @if ($poi_category->status === 1) selected @endif>@lang('admin.poi.active')</option>
                                <option value="0" @if ($poi_category->status === 0) selected @endif>@lang('admin.poi.inactive')</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-xs-6">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-3">
                                    <button type="submit" class="btn btn-primary btn-block">@lang('admin.poi.Update_Poi_Category'
                                )</button>
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-3">
                                    <a href="{{ route('admin.poicategory.index') }}" class="btn btn-danger btn-block">@lang('admin.cancel')</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
