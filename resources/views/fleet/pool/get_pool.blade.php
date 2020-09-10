@extends('fleet.layout.base')
@section('title', 'Public Pool')

@section('styles')
<style>
    span[id*=timer]{
        color: red;
        font-size: 20px;
        font-weight: 800;
    }
</style>
@endsection

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div id="myModal" class="modal">
                <div class="modal-content text-center">
                    <span class="close">&times;</span>
                    <span class="modal_title" style="font-weight:800; font-size:20px;">@lang('admin.provides.assp')</span>
                    <br><br><br>
                    <div class="provider_lists">
                    </div>
                </div>
            </div>

            <div class="box box-block bg-white">
                <h5 class="mb-1">@lang('admin.provides.pools')</h5>
                @if(count($pool_data) != 0)
                    <table class="table table-striped table-bordered dataTable" id="table-6">
                        <thead>
                        <tr>
                            <th>@lang('admin.id')</th>
                            <th>@lang('admin.request.Request_Id')</th>
                            <th>@lang('admin.request.from')</th>
                            <th>@lang('admin.request.Scheduled_Date_Time')</th>
                            <th>@lang('admin.request.Payment_Mode')</th>
                            <th>@lang('admin.request.total_amount')</th>
                            <th>@lang('admin.fleet.fleet_commission')</th>
                            <th>@lang('admin.fleet.final_price')</th>
                            <th>@lang('admin.request.Remaining_Time')</th>
                            <th>@lang('admin.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($pool_data as $key=>$val)
                            <tr>
                                <td>{{$key + 1}}</td>
                                <td>{{$val->request->booking_id}}</td>
                                <td>
                                    {{$val->from}}
                                </td>
                                <td>
                                    {{appDateTime($val->request->schedule_at)}}
                                </td>
                                <td>
                                    {{$val->request->payment_mode}}
                                </td>
                                <td>
                                    @if($val->request->total_price == null)
                                        N/A
                                    @else
                                        {{currency($val->request->total_price)}}
                                    @endif
                                </td>
                                <td>{{$val->commission_rate}}%</td>
                                <td>
                                    {{ currency($val->request->total_price - ( $val->request->total_price * $val->commission_rate / 100 ) ) }}
                                </td>
                                <td>
                                        <span id='timer-{{$key}}'>@if($val->manual_assigned_at == null)
                                                N/A @endif
                                        </span>
                                </td>
                                <td>
                                    <div class="input-group-btn">
                                        <button type="button" class="btn btn-info dropdown-toggle"
                                                data-toggle="dropdown">@lang('admin.payment.acths')
                                            <span class="caret"></span>
                                        </button>

                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="{{ route('fleet.requests.show', $val->request_id) }}"
                                                   class="btn btn-default"><i class="fa fa-search"></i> @lang('admin.payment.moredsq')</a><br>
                                                @if($val->fleet_id != \Illuminate\Support\Facades\Auth::guard('fleet')->id())
                                                    <a href="{{ route('fleet.poolride.accept', ['request_id'=>$val->request_id,'fleet'=>$fleet_id]) }}"
                                                       class="btn btn-default assign_provider"><i
                                                                class="fa fa-check"></i> @lang('admin.fleets.accept')</a><br>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>@lang('admin.id')</th>
                            <th>@lang('admin.request.Request_Id')</th>
                            <th>@lang('admin.request.from')</th>
                            <th>@lang('admin.request.Scheduled_Date_Time')</th>
                            <th>@lang('admin.request.Payment_Mode')</th>
                            <th>@lang('admin.request.total_amount')</th>
                            <th>@lang('admin.fleet.fleet_commission')</th>
                            <th>@lang('admin.fleet.final_price')</th>
                            <th>@lang('admin.request.Remaining_Time')</th>
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

@endsection

@section('scripts')
    <script type="text/javascript" src="{{asset('asset/js/countdowntimer/timeDownCounter.js')}}"></script>
    <script type="text/javascript" src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script>
    <script type="text/javascript">
        @foreach($pool_data as $index => $request)
                @if($request->manual_assigned_at)
                    try {
                        var timezone = '{{config('constants.timezone', 'UTC')}}';
                        // console.log('{{$request->manual_assigned_at}} '+ timezone);
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
        @endforeach

    </script>
@endsection
