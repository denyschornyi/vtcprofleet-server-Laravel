@extends('admin.layout.base')
@section('title', 'Scheduled Rides ')
@section('styles')
    <style>
        span[id*=timer]{
            color: red;
            font-size: 20px;
            font-weight: 800;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap-select.css') }}" />
@endsection

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div id="myModal" class="modal">
                <div class="modal-content text-center">
                    <span class="close">&times;</span>
                    <span class="modal_title"
                          style="font-weight:800; font-size:20px;">@lang('admin.provides.assp')</span>
                    <br><br><br>
                    <div class="provider_lists">
                    </div>
                </div>
            </div>

            <div class="box box-block bg-white">
                <h5 class="mb-1">@lang('admin.provides.schis')</h5>
                @if(count($requests) != 0)
                    <table class="table table-striped table-bordered dataTable" id="table-6">
                        <thead>
                        <tr>
                            <th>@lang('admin.id')</th>
                            <th>@lang('admin.request.Request_Id')</th>
                            <th>@lang('admin.request.User_Name')</th>
                            <th>@lang('admin.request.Provider_Name')</th>
                            <th>@lang('admin.request.Scheduled_Date_Time')</th>
                            <th>@lang('admin.status')</th>
                            <th>@lang('admin.request.Payment_Mode')</th>
                            <th>@lang('admin.request.Payment_Status')</th>
                            <th>@lang('admin.request.Remaining_Time')</th>
                            <th>@lang('admin.request.total_amount')</th>
                            <th>@lang('admin.fleet.fleet_commission')</th>
                            <th>@lang('admin.fleet.final_price')</th>
                            <th>@lang('admin.request.take_by')</th>
                            <th>@lang('admin.request.from')</th>
                            <th>@lang('admin.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($requests as $index => $request)
                            <tr>
                                <td>{{$index + 1}}</td>

                                <td>{{$request->booking_id}}</td>
                                @if($request->user->user_type == 'COMPANY' && $request->user->company_name !== '' )
                                    <td>{{ $request->user?$request->user->first_name: '' }} {{ $request->user?$request->user->last_name:'' }}</td>
                                @elseif($request->user->user_type == 'NORMAL')
                                    @if($request->user->company_name === '')
                                        <td>{{$request->user?$request->user->first_name:''}} {{$request->user?$request->user->last_name:''}}</td>
                                    @else
                                        <td>{{$request->user->company_name}}</td>
                                    @endif
                                @elseif($request->user->user_type == 'FLEET_COMPANY' || $request->user->user_type == 'FLEET_PASSENGER' )
                                    <td>{{$request->user->company_name}}</td>
                                @else
                                    N/A
                                @endif
                                <td>
                                    @if($request->provider_id)
                                        {{$request->provider?$request->provider->first_name:''}} {{$request->provider?$request->provider->last_name:''}}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{appDateTime($request->schedule_at)}}</td>
                                <td>
                                    {{$request->status}}
                                </td>

                                <td>{{$request->payment_mode}}</td>
                                <td>
                                    @if($request->paid)
                                        Paid
                                    @else
                                        Not Paid
                                    @endif
                                </td>
                                <td>
                                    <span id='timer-{{$index}}'>
                                        @if($request->manual_assigned_at == null)
                                            N/A
                                        @endif
                                    </span>
                                    @if($request->poolTransaction->fleet_id == 0 && $request->poolTransaction && $request->manual_assigned_at && $request->provider)
                                        <form action="{{route('admin.assign.provider.force')}}" method="POST"
                                              class="force-assign{{$request->id}}">
                                            <button type="submit" class="btn btn-info force-assigned"
                                                    style="margin-left:15px; background-color:red!important; border-color:red!important;"
                                                    rid="{{$request->id}}">@lang('admin.custom.force_assign')
                                            </button>
                                            @csrf
                                            <input type="hidden" name="id" value="{{$request->id}}">
                                            <input type="hidden" name="provider_id" value="{{$request->provider->id}}">
                                        </form>
                                    @elseif($request->manual_assigned_at && $request->provider && !$request->poolTransaction )
                                        <form action="{{route('admin.assign.provider.force')}}" method="POST"
                                              class="force-assign{{$request->id}}">
                                            <button type="submit" class="btn btn-info force-assigned"
                                                    style="margin-left:15px; background-color:red!important; border-color:red!important;"
                                                    rid="{{$request->id}}">@lang('admin.custom.force_assign')
                                            </button>
                                            @csrf
                                            <input type="hidden" name="id" value="{{$request->id}}">
                                            <input type="hidden" name="provider_id" value="{{$request->provider->id}}">
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    {{currency($request->total_price)}}
                                </td>
                                <td>
                                    {{ $request->pool->commission_rate }}
                                </td>
                                <td>
                                    @if($request->pool->commission_rate != null)
                                        {{ currency($request->total_price - ( $request->total_price * $request->pool->commission_rate / 100 ) ) }}
                                    @else

                                    @endif
                                </td>
                                <td>
                                    @if($request->poolTransaction)
                                        {{ getCompanyName($request->poolTransaction->fleet_id) }}
                                    @endif
                                </td>
                                <td>
                                    @if($request->poolTransaction)
                                        {{ getCompanyName($request->poolTransaction->from_id) }}
                                    @else
                                        {{ Auth::user()->name }}
                                    @endif
                                </td>
                                <td>
                                    <div class="input-group-btn">
                                        <button type="button" class="btn btn-info dropdown-toggle"
                                                data-toggle="dropdown">@lang('admin.payment.acths')
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="{{ route('admin.requests.show', $request->id) }}"
                                                   class="btn btn-default"><i
                                                            class="fa fa-search"></i> @lang('admin.payment.moredsq')</a><br>
                                                {{-- @if($request->poolTransaction)
                                                    @if(!$request->manual_assigned_at && !$request->provider && $request->poolTransaction->fleet_id == 0 ) --}}
                                                        <a href="{{ route('admin.scheduled.provider.list', $request->id) }}"
                                                           class="btn btn-default assign_provider" req-id="{{$request->id}}"><i
                                                                    class="fa fa-taxi"></i> @lang('admin.provides.assp')
                                                        </a><br>
                                                    {{-- @endif
                                                @else
                                                    @if($request->manual_assigned_at == null && !$request->provider )
                                                        <a href="{{ route('fleet.scheduled.provider.list', $request->id) }}"
                                                           class="btn btn-default assign_provider" req-id="{{$request->id}}"><i
                                                                    class="fa fa-taxi"></i> @lang('admin.provides.assp')
                                                        </a><br>
                                                    @endif
                                                @endif --}}

                                                <a href="{{ route('admin.assign.fleet', $request->id) }}"
                                                   class="btn btn-default assign_fleet" class="btn btn-default"
                                                   req-id="{{$request->id}}"><i
                                                            class="fa fa-modx"></i> @lang('admin.provides.asspff')
                                                </a><br>
                                                @if($request->manual_assigned_at && $request->provider_id == 0 && $request->pool || $request->poolTransaction)
                                                @else
                                                    <a href="#publicModal" data-toggle="modal" data-target="#publicModal"
                                                       data-request-id="{{$request->id}}"
                                                       data-pool-type="1" class="btn btn-default public_pool"
                                                       class="btn btn-default"> {{-- 1: public pool --}}
                                                        <i class="fa fa-reddit-alien"></i>@lang('admin.custom.public_pool')
                                                    </a><br>
                                                    <a href="#privateModal" data-toggle="modal"
                                                       data-target="#privateModal" data-request-id="{{$request->id}}"
                                                       data-pool-type="2" class="btn btn-default private_pool"
                                                       class="btn btn-default"> {{-- 2: private pool --}}
                                                        <i class="fa fa-reddit-square"></i> @lang('admin.custom.private_pool')
                                                    </a><br>
                                                @endif
                                                @if($request->poolTransaction)
                                                    @if($request->manual_assigned_at && $request->provider_id != 0 && $request->poolTransaction->fleet_id == \Illuminate\Support\Facades\Auth::guard('fleet')->user()->id )
                                                        <a href="{{ route('admin.assign.cancel', $request->id) }}"
                                                           class="btn btn-default assign_fleet" class="btn btn-default"
                                                           req-id="{{$request->id}}"><i
                                                                    class="fa fa-times"></i> @lang('admin.custom.cancel_assign')
                                                        </a><br>
                                                    @endif
                                                @else
                                                    @if($request->manual_assigned_at && $request->provider)
                                                        <a href="{{ route('admin.assign.cancel', $request->id) }}"
                                                           class="btn btn-default assign_fleet" class="btn btn-default"
                                                           req-id="{{$request->id}}"><i
                                                                    class="fa fa-times"></i> @lang('admin.custom.cancel_assign')
                                                        </a><br>
                                                    @endif
                                                @endif

                                                @if($request->manual_assigned_at && $request->provider_id == 0 && $request->pool)
                                                    <a @if ($request->pool->pool_type == '1')
                                                           href="#editPoolModal"
                                                           data-target="#editPoolModal"
                                                       @elseif ($request->pool->pool_type == '2')
                                                           href="#editPrivatePoolModal"
                                                           data-target="#editPrivatePoolModal"
                                                       @endif
                                                           data-toggle="modal"
                                                           data-request-id="{{ $request->id }}"
                                                           data-commission-rate="{{ $request->pool->commission_rate }}"
                                                           data-timeout="{{ $request->pool->timeout }}"
                                                       @if ($request->pool->pool_type == '2')
                                                           data-private-pool-id = "{{ $request->privatePoolRequests->private_id }}"
                                                           data-private-pool-name = "{{ \App\PrivatePools::where('id',$request->privatePoolRequests->private_id)->value('pool_name') }}"
                                                       @endif
                                                       class="btn btn-default assign_fleet" class="btn btn-default">
                                                        <i class="fa fa-edit"></i> @lang('admin.custom.edit_pool')
                                                    </a><br>
                                                @endif
                                                @if($request->manual_assigned_at && $request->provider_id == 0 && $request->pool)
                                                    <a href="{{ route('admin.pool.cancel', ['id'=>$request->id, 'pool_type'=>$request->pool->pool_type]) }}"
                                                       class="btn btn-default assign_fleet" class="btn btn-default"
                                                       req-id="{{$request->id}}"><i
                                                                class="fa fa-times"></i> @lang('admin.custom.cancel_pool')
                                                    </a>
                                                @elseif ($request->poolTransaction && $request->fleet_id == \Illuminate\Support\Facades\Auth::user()->id)
                                                    <a href="{{ route('admin.pool.cancel', ['id'=>$request->id, 'pool_type'=>$request->pool->pool_type]) }}"
                                                       class="btn btn-default assign_fleet" class="btn btn-default"
                                                       req-id="{{$request->id}}"><i
                                                                class="fa fa-times"></i> @lang('admin.custom.cancel_pool')
                                                    </a>
                                                @endif

                                            </li>
                                        </ul>
                                        <button type="button" class="btn btn-info vocher"
                                                style="margin-left:15px; background-color:#b531ba!important; border-color:#b531ba!important;"
                                                rid="{{$request->id}}">Voucher
                                        </button>

                                    </div>
                                    <form action="{{route('admin.requests.scheduled.pdf')}}" method="GET"
                                          class="pdf-download{{$request->id}}">
                                        <input type="hidden" name="id" value="{{$request->id}}">
                                    </form>
                                    @if($request->manual_assigned_at && $request->provider)
                                        <form action="{{route('admin.assign.provider.force')}}" method="GET"
                                              class="force-assign{{$request->id}}">
                                            <input type="hidden" name="id" value="{{$request->id}}">
                                            <input type="hidden" name="provider_id" value="{{$request->provider->id}}">
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>@lang('admin.id')</th>
                            <th>@lang('admin.request.Request_Id')</th>
                            <th>@lang('admin.request.User_Name')</th>
                            <th>@lang('admin.request.Provider_Name')</th>
                            <th>@lang('admin.request.Scheduled_Date_Time')</th>
                            <th>@lang('admin.status')</th>
                            <th>@lang('admin.request.Payment_Mode')</th>
                            <th>@lang('admin.request.Payment_Status')</th>
                            <th>@lang('admin.request.Remaining_Time')</th>
                            <th>@lang('admin.request.total_amount')</th>
                            <th>@lang('admin.fleet.fleet_commission')</th>
                            <th>@lang('admin.fleet.final_price')</th>
                            <th>@lang('admin.request.take_by')</th>
                            <th>@lang('admin.request.from')</th>
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
    <div id="publicModal" class="modal fade" role="dialog" data-backdrop="static" aria-hidden="true"
         data-keyboard="false">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content" style="width: 80%;">
                <div class="modal-header">
                    <h4 class="modal-title" id="settitle">@lang('admin.custom.pool_data')</h4>
                </div>
                <form action="{{route('admin.send_pool')}}" method="get" id="transurl">
                    <div class="modal-body">
                        <div id="sendbody" style="">
                            <div class="alert alert-warning alert-dismissible" style="display:none">
                            </div>
                            <div class="mb-1">@lang('admin.service.peak_time')</div>
                            <input type="number" required name="service_time" id="service_time" class="form-control"
                                   placeholder="24" value="24" min="1">
                            <div class="mt-1">@lang('admin.payment.commission_percentage')</div>
                            <input type="number" required name="commission" id="commission" class="form-control"
                                   placeholder="20" value="20" max="100" min="1">
                            <input type="hidden" id="request_id" name="request_id" value="">
                            <input type="hidden" id="pool_type" name="pool_type" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"
                                id="publicBtn">@lang('admin.payment.coor')</button>
                        <button type="button" class="btn btn-danger"
                                data-dismiss="modal">@lang('admin.fleets.cancel')</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <div id="privateModal" class="modal fade" role="dialog" data-backdrop="static" aria-hidden="true"
         data-keyboard="false">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content" style="width: 80%;">
                <div class="modal-header">
                    <h4 class="modal-title" id="settitle">@lang('admin.custom.pool_data')</h4>
                </div>
                <form action="{{route('admin.send_pool')}}" method="get" id="transurl">
                    <div class="modal-body">
                        <div id="sendbody" style="">
                            <div class="alert alert-warning alert-dismissible" style="display:none">
                            </div>
                            <div class="mb-1">@lang('admin.request.Pool_Name')</div>
                            <select class="form-control form-control-xs selectpicker" name="PrivatePoolName" data-size="7" data-live-search="true" data-title="@lang('admin.request.Pool_Name')" id="state_list" data-width="100%">
                                @foreach($private_pool_list as $key=>$val)
                                    <option value="{{ $val->id }}">
                                        {{ $val->pool_name }} -
                                        @if ($val->from_fleet_id == 0)  {{ \App\Admin::where('id',1)->value('name') }}
                                        @else {{ \App\Fleet::where('id',$val->from_fleet_id)->value('company') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="mb-1" style="margin-top: 1rem !important;">@lang('admin.service.peak_time')</div>
                            <input type="number" required name="service_time" id="service_time" class="form-control"
                                   placeholder="24" value="24" max="24" min="1">
                            <div class="mt-1">@lang('admin.payment.commission_percentage')</div>
                            <input type="number" required name="commission" id="commission" class="form-control"
                                   placeholder="20" value="20" max="100" min="1">
                            <input type="hidden" id="request_id" name="request_id" value="">
                            <input type="hidden" id="pool_type" name="pool_type" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"
                                id="publicBtn">@lang('admin.payment.coor')</button>
                        <button type="button" class="btn btn-danger"
                                data-dismiss="modal">@lang('admin.fleets.cancel')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editPoolModal" class="modal fade" role="dialog" data-backdrop="static" aria-hidden="true"
         data-keyboard="false">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content" style="width: 80%;">
                <div class="modal-header">
                    <h4 class="modal-title" id="settitle">@lang('admin.custom.edit_pool')</h4>
                </div>
                <form action="{{route('admin.pool.edit')}}" method="POST">
                    <div class="modal-body">
                        @csrf
                        <div style="">
                            <div class="alert alert-warning alert-dismissible" style="display:none">
                            </div>
                            <div class="mb-1">@lang('admin.service.peak_time')</div>
                            <input type="number" required name="service_time_edit" id="service_time_edit" class="form-control"
                                   placeholder="24" min="1">
                            <div class="mt-1">@lang('admin.payment.commission_percentage')</div>
                            <input type="number" required name="commission_edit" id="commission_edit" class="form-control"
                                   placeholder="20" max="100" min="1">
                            <input type="hidden" id="request_id_edit" name="request_id_edit" value="">
                            <input type="hidden" id="pool_type" name="pool_type" value="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">@lang('admin.update')</button>
                        <button type="button" class="btn btn-danger"
                                data-dismiss="modal">@lang('admin.fleets.cancel')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editPrivatePoolModal" class="modal fade" role="dialog" data-backdrop="static" aria-hidden="true"
         data-keyboard="false">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content" style="width: 80%;">
                <div class="modal-header">
                    <h4 class="modal-title" id="settitle">@lang('admin.custom.edit_pool')</h4>
                </div>
                <form action="{{route('admin.pool.edit')}}" method="POST">
                    <div class="modal-body">
                        @csrf
                        <div style="">
                            <div class="alert alert-warning alert-dismissible" style="display:none">
                            </div>
                            <div class="row">
                                <div class="mb-1 col-md-4">@lang('admin.request.selected_pool_name')</div>
                                <div class="col-md-8"><span id="selectedPoolName" name="selectedPoolName"></span></div>
                            </div>
                            <div class="mb-1">@lang('admin.request.Pool_Name')</div>
                            <select class="form-control form-control-xs selectpicker" name="PrivatePoolName" id="PrivatePoolName" data-size="7" data-live-search="true" data-title="@lang('admin.request.Pool_Name')" id="state_list" data-width="100%">
                                @foreach($private_pool_list as $key=>$val)
                                    <option value="{{ $val->id }}">{{ $val->pool_name }}</option>
                                @endforeach
                            </select>
                            <div class="mb-1" style="margin-top: 1rem;">@lang('admin.service.peak_time')</div>
                            <input type="number" required name="service_time_edit" id="service_time_edit"
                                   class="form-control"
                                   placeholder="24" min="1">
                            <div class="mt-1">@lang('admin.payment.commission_percentage')</div>
                            <input type="number" required name="commission_edit" id="commission_edit"
                                   class="form-control"
                                   placeholder="20" max="100" min="1">
                            <input type="hidden" id="request_id_edit" name="request_id_edit" value="">
                            <input type="hidden" id="selectedPoolID" name="selectedPoolID" value="">
                            <input type="hidden" id="pool_type" name="pool_type" value="2">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">@lang('admin.update')</button>
                        <button type="button" class="btn btn-danger"
                                data-dismiss="modal">@lang('admin.fleets.cancel')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{asset('asset/js/countdowntimer/timeDownCounter.js')}}"></script>
    <script type="text/javascript" src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script>
    <script>
        $('#publicModal').on('show.bs.modal', function (e) {
            var requestId = $(e.relatedTarget).data('request-id'); //request_id
            var pool_type = $(e.relatedTarget).data('pool-type'); //pool_type 1:// public pool, 2: private pool
            $(e.currentTarget).find('input[name="request_id"]').val(requestId);
            $(e.currentTarget).find('input[name="pool_type"]').val(pool_type);
        });

        $('#privateModal').on('show.bs.modal', function (e) {
            var requestId = $(e.relatedTarget).data('request-id'); //request_id
            var pool_type = $(e.relatedTarget).data('pool-type'); //pool_type 1:// public pool, 2: private pool
            $(e.currentTarget).find('input[name="request_id"]').val(requestId);
            $(e.currentTarget).find('input[name="pool_type"]').val(pool_type);
        });

        @foreach($requests as $index => $request)
                @if($request->manual_assigned_at)
                    @if($request->poolTransaction)
                        @if( $request->poolTransaction->fleet_id == \Illuminate\Support\Facades\Auth::guard('fleet')->user()->id)
                            try {
                                var timezone = '{{config('constants.timezone', 'UTC')}}';

                                var serverDate = moment.tz('{{$request->manual_assigned_at}}', timezone);
                                var dd = serverDate.clone().tz(moment.tz.guess());
                                var date = new Date(dd.format('YYYY-MM-DD HH:mm:ss'));
                                console.log(date);
                                date.setHours(date.getHours() + {{$request->timeout}});
                                timeDownCounter({
                                    'countDownDate': date.getTime(), // Direct Use like: new Date("Sep 5, 2018 15:37:25").getTime();
                                    'addSpanForResult': true,
                                    'countDownIdSelector': 'timer-{{$index}}',
                                    'contDownOver': 'N/A',
                                    'countDownReturnData': 'from-hours'
                                }).startCountDown();
                        } catch (error) {
                            console.log(error);
                        }
                    @endif
                @else
                        try {
                        var timezone = '{{config('constants.timezone', 'UTC')}}';

                        var serverDate = moment.tz('{{$request->manual_assigned_at}}', timezone);
                        var dd = serverDate.clone().tz(moment.tz.guess());
                        var date = new Date(dd.format('YYYY-MM-DD HH:mm:ss'));
                        console.log(date);
                        date.setHours(date.getHours() + {{$request->timeout}});
                        timeDownCounter({
                            'countDownDate': date.getTime(), // Direct Use like: new Date("Sep 5, 2018 15:37:25").getTime();
                            'addSpanForResult': true,
                            'countDownIdSelector': 'timer-{{$index}}',
                            'contDownOver': 'N/A',
                            'countDownReturnData': 'from-hours'
                        }).startCountDown();
                    } catch (error) {
                        console.log(error);
                    }
                @endif
            @endif
        @endforeach
        $('#table-6').on('click', '.force-assigned', function () {
            var id = $(this).attr('rid');
            if (confirm('Are you sure to force assign?')) {
                $('#table-6 .force-assign' + id).submit();
            }
        });

        $('#editPoolModal').on('show.bs.modal', function (e) {
            var requestID = $(e.relatedTarget).data('request-id');
            var commissionRate = $(e.relatedTarget).data('commission-rate');
            var timeout = $(e.relatedTarget).data('timeout');

            $(e.currentTarget).find('input[name="request_id_edit"]').val(requestID);
            $(e.currentTarget).find('input[name="service_time_edit"]').val(timeout);
            $(e.currentTarget).find('input[name="commission_edit"]').val(commissionRate);
        });

        $('#editPrivatePoolModal').on('show.bs.modal', function (e) {
            var requestID = $(e.relatedTarget).data('request-id');
            var commissionRate = $(e.relatedTarget).data('commission-rate');
            var timeout = $(e.relatedTarget).data('timeout');
            var privatePoolID = $(e.relatedTarget).data('private-pool-id');
            var privatePoolName = $(e.relatedTarget).data('private-pool-name');
            console.log(privatePoolName);
            console.log(privatePoolID);
            $(e.currentTarget).find('input[name="request_id_edit"]').val(requestID);
            $(e.currentTarget).find('input[name="service_time_edit"]').val(timeout);
            $(e.currentTarget).find('input[name="commission_edit"]').val(commissionRate);
            $(e.currentTarget).find('span[name="selectedPoolName"]').text(privatePoolName);
            $(e.currentTarget).find('input[name="selectedPoolID"]').val(privatePoolID);
        });
    </script>
    <script type="text/javascript" src="{{ asset('asset/js/bootstrap-select.js') }}"></script>
@endsection
