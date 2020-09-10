@if(empty($trip) || empty($admin))
@else
<table width="100%" style="margin-top:30px;">
    <tr>
        <td colspan="2" width="55%">
            <br>
            <img src="{{ config('constants.site_logo', asset('logo-black.png')) }}" width=400 class="img-fluid" />
            <br>
            <br>
        </td>
        <td style="vertical-align: middle;" align="right"><span style="font-size:30px;"><b>@lang('admin.custom.invoice')</b></span></td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="" style="color: #b531ba;">@lang('admin.custom.invoice')</span><br>
            @if ($fleet_id === 0)
                <b>{{$admin->name}}</b><br>
                <div style="width:80%;">{{$admin->address}}</div>
                <div style="width:80%;">{{$admin->country}}</div>
                <div style="width:80%;">{{$admin->zip_code}}</div>
                <div style="width:80%;">{{$admin->city}}</div>
            @else
                <b>{{$admin->name}}</b><br>
                <div style="width:80%;">{{$admin->company}}</div>
{{--                <div style="width:80%;">{{$admin->country}}</div>--}}
{{--                <div style="width:80%;">{{$admin->zip_code}}</div>--}}
{{--                <div style="width:80%;">{{$admin->city}}</div>--}}
            @endif

        </td>
        <td align="right" class="">
            Date: {{appDate(now())}}<br>
            Status:
            @if($trip->paid == 1)<span style="color:#00c82a;">Paid </span> </td>
            @else <span style="color:red;">@lang('admin.custom.Unpaid') </span> </td>
            @endif
    </tr>
    <tr>
        <td colspan="2">
            <br>
            <span class="" style="color: #b531ba;">@lang('admin.custom.to')</span><br>
            @if($trip->user->user_type === 'COMPANY')
                <b class="">{{$trip->user->company_name}}</b> <br>
                <div style="width:80%;" class="">{{$trip->user->company_address}}</div>
                <div style="width:80%;" class="">{{$trip->user->company_zip_code}}</div>
                <div style="width:80%;" class="">{{$trip->user->company_city}}</div>
            @elseif($trip->user->user_type === 'FLEET_COMPANY')
                <b class="">{{$trip->user->company_name}}</b> <br>
                <div style="width:80%;" class="">{{$trip->user->company_address}}</div>
                <div style="width:80%;" class="">{{$trip->user->company_zip_code}}</div>
                <div style="width:80%;" class="">{{$trip->user->company_city}}</div>
            @elseif($trip->user->user_type === 'FLEET_NORMAL')
                <b class="">{{$trip->user->first_name}}</b> <br>
                <div style="width:80%;" class="">{{$trip->user->last_name}}</div>
            @elseif($trip->user->user_type === 'FLEET_PASSENGER')
{{--                <b class="">{{$trip->user->first_name}} {{$trip->user->last_name}}</b> <br>--}}
                <b class="">{{$trip->user->company_name}}</b> <br>
                <div style="width:80%;" class="">{{$trip->user->company_address}}</div>
                <div style="width:80%;" class="">{{$trip->user->company_zip_code}}</div>
                <div style="width:80%;" class="">{{$trip->user->company_city}}</div>
            @else
                <b class="">{{$trip->user->first_name}} {{$trip->user->last_name}}</b> <br>
            @endif
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <br><br>
            <table width="100%">
                <tr>
                    <td width="30%">@lang('admin.request.Booking_ID')</td>
                    <td width="40%" style="">@lang('admin.custom.fare_break')</td>
                    <td align="right">@lang('admin.fleets.amount')</td>
                </tr>
                <tr>
                    <td colspan="3" style="border-bottom: #e7e7e7 2px solid;"></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>

                <tr>
                    <td><span style="font-size:20px;">{{$trip->booking_id}}</span></td>
                    <td style=""></td>
                    <td align="right"></td>
                </tr>
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.base_price')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">@if($trip->payment){{currency($trip->payment->fixed)}}@endif</span></td>
                </tr>
                @if($trip->service_type->calculator=='MIN')
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.minutes_price')/span></td>
                    <td align="right"><span class="" style="font-size:20px;">@if($trip->payment){{currency($trip->payment->minute)}}@endif</span></td>
                </tr>
                @endif
                @if($trip->service_type->calculator=='HOUR')
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.hours_price')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">@if($trip->payment){{currency($trip->payment->hour)}}@endif</span></td>
                </tr>
                @endif
                @if($trip->service_type->calculator=='DISTANCE')
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.distance_price')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">@if($trip->payment){{currency($trip->payment->distance)}}@endif</span></td>
                </tr>
                @endif
                @if($trip->service_type->calculator=='DISTANCEMIN')
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.minutes_price')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">@if($trip->payment){{currency($trip->payment->minute)}}@endif</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.distance_price')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">@if($trip->payment){{currency($trip->payment->distance)}}@endif</span></td>
                </tr>
                @endif
                @if($trip->service_type->calculator=='DISTANCEHOUR')
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.hours_price')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">@if($trip->payment){{currency($trip->payment->hour)}}@endif</span></td>
                </tr>
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.distance_price')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">@if($trip->payment){{currency($trip->payment->distance)}}@endif</span></td>
                </tr>
                @endif
                @if($trip->payment)
                @if($trip->payment->wallet)
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.wallet_deduction')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">{{currency($trip->payment->wallet)}}</span></td>
                </tr>
                @endif
                @if($trip->payment->discount)
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.promotion_applied')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">{{currency($trip->payment->discount)}}</span></td>
                </tr>
                @endif
                @if($trip->payment->tips)
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.tips')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">{{currency($trip->payment->tips)}}</span></td>
                </tr>
                @endif
                @endif
                {{-- peak hours charge --}}
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.peak_hours_charge')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">@if($trip->payment){{currency($trip->payment->peak_amount)}}@endif</span></td>
                </tr>

                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.tax_price')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">@if($trip->payment){{currency($trip->payment->tax)}}@endif</span></td>
                </tr>

                @if($trip->payment->waiting_amount>0)
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.waiting_price')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">{{currency($trip->payment->waiting_amount)}}</span></td>
                </tr>
                @endif

                @if($trip->payment->round_of)
                <tr>
                    <td></td>
                    <td style=""><span style="font-size:20px;">@lang('user.ride.round_off')</span></td>
                    <td align="right"><span class="" style="font-size:20px;">{{currency($trip->payment->round_of)}}</span></td>
                </tr>
                @endif

                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3" style="border-bottom: #e7e7e7 2px solid;"></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>

                <tr>
                    <td><span style="font-size:20px;"><b class="">@lang('user.charged') - @if($trip->use_wallet == 0){{$trip->payment_mode}}@else Wallet @endif</b></span></td>
                    <td></td>
                    <td align="right"><span class="" style="font-size:20px;"><b class="">
                    @if($trip->payment)
                    @if($trip->payment_mode=='CASH' && $trip->use_wallet == 0)
                    {{currency(round($trip->payment->total-$trip->payment->discount+$trip->payment->tips))}}
                    @else
                    {{currency($trip->payment->total-$trip->payment->discount+$trip->payment->tips)}}
                    @endif
                    @endif
                    </b></span></td>
                </tr>

            </table>
            <br>
            <br>
            <br>
        </td>
    </tr>
    <tr>
        <td colspan="3">
        </td>
    </tr>
    <tr>
        <td colspan="2"><span class="" style="color: #b531ba;">Note</span></td>
        <td align="right"><span class="" style="color: #b531ba;">@lang('admin.custom.vtcpro')</span></td>
    </tr>
    <tr>
        <td colspan="2">
            <span style="font-size:12;">{{$admin->note}}</span>
        </td>
        <td align="right">
            <div style="width:80%;float: right;">
            <div><span style="font-size:12;">{{$admin->name}}</span></div>
            <div><span style="font-size:12;">{{$admin->address}}</span></div>
            <div><span style="font-size:12;">{{$admin->zip_code}}</span></div>
            <div><span style="font-size:12;">{{$admin->city}}</span></div>
            </div>
        </td>
    </tr>
</table>
@endif
