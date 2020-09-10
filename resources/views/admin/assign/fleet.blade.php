@extends('admin.layout.base')

@section('title')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
        @include('common.notify')
            <h5 class="mb-1">@lang('admin.provides.asspff')</h5>
            @if(count($fleets) != 0)
            <table class="table table-striped table-bordered dataTable" id="table-6">
                <thead>
                    <tr>
                        <th>@lang('admin.back')</th>
                        <th>@lang('admin.custom.fullname')</th>
                        <th>@lang('admin.user-pro.email')</th>
                        <th>@lang('admin.custom.code_pays')</th>
                        <th>@lang('admin.fleets.mobile')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($fleets as $index => $fleet)
                    <tr>
                        <td>{{ $fleet->id }}</td>
                        <td>{{ $fleet->name }}</td>
                        <td>{{ $fleet->email }}</td>
                        <td>{{ $fleet->country_code }}</td>
                        <td>{{ $fleet->mobile }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary waves-effect dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    @lang('admin.action')
                                </button>
                                <div class="dropdown-menu">
                                    <form action="{{ url('/admin/assign/fleet') }}" method="POST" class="form_{{$index}}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="id" value="{{$req->id}}" />
                                        <input type="hidden" name="fleet_id" value="{{$fleet->id}}">
                                        <input type="hidden" name="timeout" value="24" class="timeout_{{$index}}">
                                        <button type="submit" class="dropdown-item" data-toggle="modal" data-target="#transferModal" req-id="{{$index}}">
                                            <i class="fa fa-pencil"></i> @lang('admin.custom.assign')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>                        
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>@lang('admin.back')</th>
                        <th>@lang('admin.custom.fullname')</th>
                        <th>@lang('admin.user-pro.email')</th>
                        <th>@lang('admin.fleets.mobile')</th>
                        <th>@lang('admin.fleets.mobile')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table>
            @else
            <h6 class="no-result">@lang('admin.custom.reds')</h6>
            @endif 
        </div>
    </div>
</div>
<!-- Modal -->
<div id="transferModal" class="modal fade" role="dialog" data-backdrop="static" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="settitle">@lang('admin.custom.add_time')</h4>
            </div>
            <!-- <form action="" method="Get" id="transurl"> -->
                <div class="modal-body">
                    <div id="sendbody" style="">
                        <div class="alert alert-warning alert-dismissible" style="display:none">
                        </div>
                        <input type="number" required name="service_model" id="timemout" class="form-control" placeholder="24" value="24">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" data-dismiss="modal">@lang('admin.payment.coor')</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">@lang('admin.fleets.cancel')</button>
                </div>
            <!-- </form> -->
        </div>

    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    $(function () {
        req_id = -1;
        $("#table-6").on('click', '.dropdown-item', function (e) {
            e.preventDefault();
            req_id = $(this).attr('req-id');
        });
        $('.btn.btn-success').on('click', function(e) {
            e.preventDefault();
            if (req_id >= 0) {
                $('.timeout_' + req_id ).val($('#timemout').val());
                $('.form_' + req_id).submit();
            }
        });
    });
</script>
@endsection