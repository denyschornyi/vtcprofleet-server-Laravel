@extends('user.layout.base')

@section('title')
@section('styles')
    <style>
        dt {
            width: 50%;
            float: left;
        }

        dd {
            width: 50%;
            float: left;
        }

        dl {
            width: 100%;
        }
    </style>
@stop
@section('content')

    <div class="col-md-9">
        <div class="dash-content">
            <div class="row no-margin">
                <div class="col-md-12">
                    <h4 class="page-title" id="ride_status">
                        @if($response->status == 'SEARCHING') @lang('user.ride.finding_driver')
                        @elseif($response->status == 'STARTED') {{$response->provider->first_name}}  @lang('user.ride.accepted_ride')
                        @elseif($response->status == 'ARRIVED') {{$response->provider->first_name}}  @lang('user.ride.arrived_ride')
                        @elseif($response->status == 'PICKEDUP') @lang('user.ride.onride')
                        @elseif($response->status == 'DROPPED') @lang('user.ride.waiting_payment')
                        @elseif($response->status == 'COMPLETED')  @lang('user.ride.rate_and_review') {{$response->provider->first_name}}
                        @endif
                    </h4>
                </div>
            </div>

            <div class="row no-margin">
                <div class="col-md-6">
                    @if($response->status == 'SEARCHING')
                        <form action="{{url('cancel/ride')}}" method="POST">
                            {{ csrf_field() }}
                            <input type="hidden" name="request_id" value="{{$response->id}}"/>
                            <div class="status">
                                <h6>@lang('user.status')</h6>
                                <p>@lang('user.ride.finding_driver')</p>
                            </div>
                            <button type="submit"
                                    class="full-primary-btn fare-btn">@lang('user.ride.cancel_request')</button>
                        </form>
                    @elseif($response->status == 'STARTED')
                        <form action="{{url('cancel/ride')}}" method="POST">
                            {{ csrf_field() }}
                            <input type="hidden" name="request_id" value="{{$response->id}}"/>
                            <div class="status">
                                <h6>@lang('user.status')</h6>
                                <p>@lang('user.ride.accepted_ride')</p>
                            </div>
                            <CancelReason/>
                            <button type="button" class="full-primary-btn" data-toggle="modal"
                                    data-target="#cancel-reason">@lang('user.ride.cancel_request')</button>
                            <br/>
                            <h5><strong>@lang('user.ride.ride_details')</strong></h5>
                            <div class="driver-details">
                                <dl class="dl-horizontal left-right">
                                    <dt>@lang('user.booking_id')</dt>
                                    <dd>{{ $response->booking_id }}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.service_type')</dt>
                                    <dd>{{$response->service_type->name}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.driver_name')</dt>
                                    <dd>{{$response->provider->first_name}} {{$response->provider->last_name}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.service_number')</dt>
                                    <dd>{{$response->provider_service->service_number}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.service_model')</dt>
                                    <dd>{{$response->provider_service->service_model}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.driver_rating')</dt>
                                    <dd>
                                        <div class="rating-outer">
                                            <input type="hidden" value={{$response->provider->rating}} name="rating"
                                                   class="rating" disabled/>
                                        </div>
                                    </dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.payment_mode')</dt>
                                    <dd>{{$response->payment_mode}}</dd>
                                    <div class="clearfix"></div>
                                    @if($response->ride_otp == 1)
                                        <dt>@lang('user.otp')</dt>
                                        <dd>{{$response->otp}}</dd>
                                        <div class="clearfix"></div>
                                    @endif
                                </dl>
                            </div>
                        </form>
                    @elseif($response->status == 'ARRIVED')
                        <form action="{{url('cancel/ride')}}" method="POST">
                            {{ csrf_field() }}
                            <input type="hidden" name="request_id" value="{{$response->id}}"/>
                            <div class="status">
                                <h6>@lang('user.status')</h6>
                                <p>@lang('user.ride.arrived_ride')</p>
                            </div>
                            <CancelReason/>
                            <button type="button" class="full-primary-btn" data-toggle="modal"
                                    data-target="#cancel-reason">@lang('user.ride.cancel_request')</button>
                            <br/>
                            <h5><strong>@lang('user.ride.ride_details')</strong></h5>
                            <div class="driver-details">
                                <dl class="dl-horizontal left-right">
                                    <dt>@lang('user.booking_id')</dt>
                                    <dd>{{ $response->booking_id }}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.service_type')</dt>
                                    <dd>{{$request->service_type->name}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.driver_name')</dt>
                                    <dd>{{$response->provider->first_name}} {{$response->provider->last_name}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.service_number')</dt>
                                    <dd>{{$response->provider_service->service_number}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.service_model')</dt>
                                    <dd>{{$response->provider_service->service_model}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.driver_rating')</dt>
                                    <dd>
                                        <div class="rating-outer">
                                            <input type="hidden" value={{$response->provider->rating}} name="rating"
                                                   class="rating" disabled/>
                                        </div>
                                    </dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.payment_mode')</dt>
                                    <dd>{{$response->payment_mode}}</dd>
                                    @if($request->ride_otp == 1)
                                        <dt>@lang('user.otp')</dt>
                                        <dd>{{$response->otp}}</dd>
                                        <div class="clearfix"></div>
                                    @endif
                                </dl>
                            </div>
                        </form>
                    @elseif($response->status == 'PICKEDUP')
                        <div>
                            <div class="status">
                                <h6>@lang('user.status')</h6>
                                <p>@lang('user.ride.onride')</p>
                            </div>
                            <br/>
                            <h5><strong>@lang('user.ride.ride_details')</strong></h5>
                            <div class="driver-details">
                                <dl class="dl-horizontal left-right">
                                    <dt>@lang('user.booking_id')</dt>
                                    <dd>{{ $response->booking_id }}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.service_type')</dt>
                                    <dd>{{$request->service_type->name}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.driver_name')</dt>
                                    <dd>{{$response->provider->first_name}} {{$response->provider->last_name}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.service_number')</dt>
                                    <dd>{{$response->provider_service->service_number}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.service_model')</dt>
                                    <dd>{{$response->provider_service->service_model}}</dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.driver_rating')</dt>
                                    <dd>
                                        <div class="rating-outer">
                                            <input type="hidden" value={{$response->provider->rating}} name="rating"
                                                   class="rating" disabled/>
                                        </div>
                                    </dd>
                                    <div class="clearfix"></div>
                                    <dt>@lang('user.payment_mode')</dt>
                                    <dd>{{$response->payment_mode}}</dd>
                                    <div class="clearfix"></div>
                                </dl>
                            </div>
                        </div>
                        {{--                    @elseif(($response->status == 'DROPPED' || $response->status == 'COMPLETED') &&--}}
                        {{--                    $response->payment_mode == 'CASH' && $response->paid == 0 && $response->user->user_type != 'COMPANY' && $response->use_wallet != 0 )--}}

                        {{--                    @elseif(($response->status == 'DROPPED' || $response->status == 'COMPLETED') &&--}}
                        {{--                   $response->payment_mode != 'CASH' && $response->paid == 0)--}}
                    @elseif($response->status == 'COMPLETED')
                        <form method="POST" action="{{url('/rate')}}">
                            {{ csrf_field() }}
                            <div class="rate-review">
                                <label>@lang('user.ride.rating')</label>
                                <div class="rating-outer">
                                    <input type="hidden" value="1" name="rating" class="rating"/>
                                </div>
                                <input type="hidden" name="request_id" value="{{$response->id}}"/>
                                <label>@lang('user.ride.comment')</label>
                                <textarea class="form-control" name="comment" placeholder="Write Comment"></textarea>
                            </div>
                            <button type="submit" class="full-primary-btn fare-btn">SUBMIT</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div id="cancel-reason" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">@lang('user.ride.cancel_request')</h4>
                    </div>
                    <div class="modal-body">
                        <select class="form-control" name="cancel_reason" id="cancel_reason">
                            @if($response->reasons)
                                @foreach($response->reasons as $reas)
                                    <option value="{{$reas->reason}}">{{$reas->reason}}</option>
                                @endforeach
                            @endif
                            <option value="ot">@lang('admin.custom.offh')</option>
                        </select>
                        <textarea class="form-control @if($response->reasons) cancel_hide @endif" id="cancel_text"
                                  name="cancel_reason_opt" placeholder="@lang('user.ride.cancel_reason')"
                                  row="5"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit"
                                class="full-primary-btn fare-btn">@lang('user.ride.cancel_request')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style type="text/css">
        .cancel_hide {
            display: none;
        }

        .cancel_show {
            display: inline-block;
        }

        .pac-container {
            z-index: 10000 !important;
        }
    </style>
@endsection

@section('scripts')
    <script type="text/javascript">
        function disableEnterKey(e) {
            var key;
            if (window.e)
                key = window.e.keyCode; // IE
            else
                key = e.which; // Firefox

            if (key == 13)
                return e.preventDefault();
        }
    </script>
    <script type="text/javascript">

    </script>
    <script type="text/javascript" src="{{asset('asset/js/rating.js')}}"></script>
    <script type="text/javascript">
        $('.rating').rating();
        $(document).ready(function () {
            // checkRequest();
        });

        {{--function checkRequest()--}}
        {{--{--}}
        {{--    $.ajax({--}}
        {{--        url: "{{url('status')}}",--}}
        {{--        type: "GET",--}}
        {{--        success: function (response) {--}}
        {{--            console.log(response);--}}
        {{--            console.log('haha');--}}
        {{--        }--}}
        {{--    })--}}
        {{--}--}}

        // setInterval(checkRequest,9000);
        setInterval(function () {
            location.reload();
        },20000);

        $(document).on('click', '#cancel_reason', function () {
            //console.log($(this).val());
            if ($(this).val() == 'ot') {
                $("#cancel_text").removeClass('cancel_hide');
                $("#cancel_text").attr('required', true);
                //$("#cancel_text").addClass('cancel_show');
            } else {
                $("#cancel_text").attr('required', false);
                $("#cancel_text").addClass('cancel_hide');
                //$("#cancel_text").addClass('cancel_show');
            }
        });
    </script>
@endsection
