@extends('user.layout.base')

@section('title')

@section('styles')
    <style type="text/css">
        .surge-block {
            background-color: black;
            width: 50px;
            height: 50px;
            border-radius: 25px;
            margin: 0 auto;
            padding: 10px;
            padding-top: 15px;
        }

        .surge-text {
            top: 11px;
            font-weight: bold;
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="col-md-9">
        <div class="dash-content">
            <div class="row no-margin">
                <div class="col-md-12">
                    <h4 class="page-title">@lang('user.ride.ride_now')</h4>
                </div>
            </div>
{{--            @include('common.notify')--}}
            <div class="row no-margin">
                <div class="col-md-6">
                    <form action="{{url('create/ride')}}" method="POST" id="create_ride">

                        {{ csrf_field() }}
                        <dl class="dl-horizontal left-right">
                            <dt>@lang('user.type')</dt>
                            <dd>{{$service->name}}</dd>
                            <dt>@lang('user.total_distance')</dt>
                            <dd>{{distance($fare->distance)}}</dd>
                            <dt>@lang('user.eta')</dt>
                            <dd>{{$fare->time}}</dd>
                            <dt>@lang('user.estimated_fare')</dt>
                            <dd>{{currency($fare->estimated_fare)}}</dd>
                            <dt>@lang('user.promocode')</dt>
                            <dd id="promo_amount">{{currency()}}</dd>
                            <hr>
                            <dt>@lang('user.total')</dt>
                            <dd id="total_amount">{{ currency($total) }}</dd>
                            <hr>
                            @if($usable_wallet)
                                <input type="checkbox" name="use_wallet" value="1"
                                       style="opacity:1;position:relative;z-index:1"> @lang('user.use_wallet_balance')
                                <br>
                                <br>
                                <dt>@lang('user.available_wallet_balance')</dt>
                                <dd>{{currency(Auth::user()->wallet_balance)}}</dd>
                            @endif
                        </dl>

                        @if(Config::get('constants.braintree') == 1)
                            <input type="hidden" name="braintree_nonce" value=""/>
                        @endif
                        <input type="hidden" name="s_address" value="{{Request::get('s_address')}}">
                        <input type="hidden" name="d_address" value="{{Request::get('d_address')}}">
                        <input type="hidden" name="s_latitude" value="{{Request::get('s_latitude')}}">
                        <input type="hidden" name="s_longitude" value="{{Request::get('s_longitude')}}">
                        <input type="hidden" name="d_latitude" value="{{Request::get('d_latitude')}}">
                        <input type="hidden" name="d_longitude" value="{{Request::get('d_longitude')}}">
                        <input type="hidden" name="service_type" value="{{Request::get('service_type')}}">
                        <input type="hidden" name="distance" value="{{$fare->distance}}">
                        @if(Request::get('rental_hours') != '')
                            <input type="hidden" name="rental_hours" value="{{Request::get('rental_hours')}}">
                        @endif
                        <p>@lang('user.promocode')</p>
                        <select class="form-control" name="promocode_id" id="promocode">
                            <option value="" data-percent="0" data-max="0">@lang('user.promocode_select')</option>
                            @foreach($promolist as $promo)
                                <option value="{{$promo->id}}" data-percent="{{$promo->percentage}}"
                                        data-max="{{$promo->max_amount}}">{{$promo->promo_code}}</option>
                            @endforeach
                        </select>
                        <br>
                        <p>@lang('user.payment_method')</p>
                        <select class="form-control" name="payment_mode" id="payment_mode" onchange="card(this.value);">
                            @if(Config::get('constants.cash') == 1)
                                <option value="CASH">@lang('admin.custom.cash')</option>
                            @endif
                            @if(Config::get('constants.card') == 1)
                                @if(!empty($cards))
                                    <option value="CARD">@lang('admin.payment.cabssh')</option>
                                @endif
                                @if(Config::get('constants.braintree') == 1)
                                    <option value="BRAINTREE">BRAINTREE</option>
                                @endif
                            @endif
                            @if(Config::get('constants.payumoney') == 1)
                                <option value="PAYUMONEY">PAYUMONEY</option>
                            @endif
                            @if(Config::get('constants.paypal') == 1)
                                <option value="PAYPAL">PAYPAL</option>
                            @endif
                            @if(Config::get('constants.paypal_adaptive') == 1)
                                <option value="PAYPAL-ADAPTIVE">PAYPAL-ADAPTIVE</option>
                            @endif
                            @if(Config::get('constants.paytm') == 1)
                                <option value="PAYTM">PAYTM</option>
                            @endif
                        </select>
                        <br>
                        <p>@lang('admin.custom.user_adv')</p>
                        <select class="form-control" name="traveller_type" id="advanced_option">
                            <option value="TRAVELLER">@lang('user.ride.im_the_traveller')</option>
                            <option value="PASSENGER">@lang('user.ride.i_have_a_passenger')</option>
                        </select>
                        <div class="input-group dash-form passenger_forms" style="display:none">
                            <input type="text" class="form-control" id="" name="passenger_name"
                                   placeholder="@lang('admin.custom.user_passname')">
                        </div>
                        <div class="input-group dash-form passenger_forms" style="display:none">
                            <input type="tel" class="form-control" id="" name="passenger_phone"
                                   placeholder="@lang('admin.custom.user_passnum')" pattern="[0-9]{10}">
                        </div>
                        <br>
                        <p>Comment</p>
                        <div class="input-group dash-form">
                            <textarea class="form-control" id="" name="comment"
                                      placeholder="@lang('admin.custom.user_note')"></textarea>
                        </div>
                        <br>

                        @if(Config::get('constants.card') == 1)
                            @if(!empty($cards))
                                <select class="form-control" name="card_id" style="display: none;" id="card_id">
                                    <option value="">@lang('admin.custom.user_selcad')</option>
                                    @foreach($cards as $card)
                                        <option value="{{$card->card_id}}">{{$card->brand}} **** ****
                                            **** {{$card->last_four}}</option>
                                    @endforeach
                                </select>
                            @endif
                        @endif

                        @if(Config::get('constants.braintree') == 1)
                            <div style="display: none;" id="braintree">
                                <div id="dropin-container"></div>
                            </div>
                        @endif

                        @if($fare->surge == 1)

                            <span><em>@lang('user.demand_node')</em></span>
                            <div class="surge-block"><span class="surge-text">{{$fare->surge_value}}</span>
                            </div>

                        @endif

                        <button type="submit" id="submit-button"
                                class="half-primary-btn fare-btn">@lang('user.ride.ride_now')</button>
                        <button type="button" class="half-secondary-btn fare-btn" data-toggle="modal"
                                data-target="#schedule_modal">@lang('user.schedule')</button>

                    </form>
                </div>

                <div class="col-md-6">
                    <div class="user-request-map">

                        <div class="map-static" style="background-image: url({{$staticmap}});">
                        </div>
                        <div class="from-to row no-margin">
                            <div class="from">
                                <h5>@lang('user.from')</h5>
                                <p>{{$request->s_address}}</p>
                            </div>
                            <div class="to">
                                <h5>@lang('user.to')</h5>
                                <p>{{$request->d_address}}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>



    <!-- Schedule Modal -->
    <div id="schedule_modal" class="modal fade schedule-modal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@lang('user.schedule_title')</h4>
                </div>
                <form>
                    <div class="modal-body">

                        <label>@lang('user.schedule_date')</label>
                        <input value="{{date('d/m/Y')}}" type="text" id="datepicker" placeholder="Date"
                               name="schedule_date">
                        <label>@lang('user.schedule_time')</label>
                        <input value="{{date('H:i')}}" type="text" id="timepicker"
                               placeholder="@lang('admin.push.Timesss')" name="schedule_time">

                    </div>
                    <div class="modal-footer">
                        <button type="button" id="schedule_button" class="btn btn-default"
                                data-dismiss="modal">@lang('user.schedule_ride')</button>
                    </div>

                </form>
            </div>

        </div>
    </div>


@endsection
@section('scripts')

    <script>

        $(document).ready(function (e) {

            $.ajax({
                url: "https://maps.googleapis.com/maps/api/directions/json?origin={{$request->s_latitude}},{{$request->s_longitude}}&destination={{$request->d_latitude}},{{$request->d_longitude}}&mode=driving&key={{config('constants.map_key')}}",
                type: "GET",
                dataType: 'jsonp',
                cache: false,
                success: function (response) {
                    alert("S");
                    console.log(response.routes[0].overview_polyline.points);
                }
            });
        });

    </script>

    @if(Config::get('constants.braintree') == 1)
        <script src="https://js.braintreegateway.com/web/dropin/1.14.1/js/dropin.min.js"></script>

        <script>


            var button = document.querySelector('#submit-button');
            var form = document.querySelector('#create_ride');
            braintree.dropin.create({
                authorization: '{{$clientToken}}',
                container: '#dropin-container',
                //Here you can hide paypal
                paypal: {
                    flow: 'vault'
                }
            }, function (createErr, instance) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (document.querySelector('select[name="payment_mode"]').value == "BRAINTREE") {
                        instance.requestPaymentMethod(function (requestPaymentMethodErr, payload) {
                            document.querySelector('input[name="braintree_nonce"]').value = payload.nonce;
                            console.log(payload.nonce);
                            form.submit();
                        });
                    } else {
                        form.submit();
                    }
                });
            });
        </script>

    @endif


    <script type="text/javascript">
        $(document).ready(function () {
            $('#schedule_button').click(function () {
                $("#datepicker").clone().attr('type', 'hidden').appendTo($('#create_ride'));
                $("#timepicker").clone().attr('type', 'hidden').appendTo($('#create_ride'));
                document.getElementById('create_ride').submit();
            });

            $('#advanced_option').change(function () {
                if ($(this).val() == 0) $('.passenger_forms').hide(); else $('.passenger_forms').show();
                $('.passenger_forms input').prop('required', $(this).val() != 0);
            });
        });
    </script>
    <script type="text/javascript">
        var date = new Date();
        date.setDate(date.getDate());
        $('#datepicker').datepicker({
            startDate: date
        });
        $('#timepicker').timepicker({showMeridian: false});
    </script>
    <script type="text/javascript">
        @if(Config::get('constants.cash') == 0)
        card('CARD');

        @endif
        function card(value) {
            $('#card_id, #braintree').fadeOut(300);
            if (value == 'CARD') {
                $('#card_id').fadeIn(300);
            } else if (value == 'BRAINTREE') {
                $('#braintree').fadeIn(300);
            }
        }

        $('#promocode').on('change', function () {

            var estimate = "{{$fare->estimated_fare}}";
            var percentage = $('option:selected', this).attr('data-percent');
            var max_amount = $('option:selected', this).attr('data-max');
            var percent_total = estimate * percentage / 100;
            if (percent_total > max_amount) {
                promo = parseFloat(max_amount);
            } else {
                promo = parseFloat(percent_total);
            }
            $("#promo_amount").html("{{config('constants.currency')}}" + promo.toFixed(2));
            $("#total_amount").html("{{config('constants.currency')}}" + (estimate - promo).toFixed(2));
        });
    </script>
@endsection
