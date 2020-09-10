
@if(empty($wallet) || empty($admin))
@else
@php $user = Auth::user(); @endphp
<table width="100%" style="margin-top:30px;">
    <tr>
        <td colspan="2" width="55%">
            <br>
            <img src="{{ config('constants.site_logo', asset('logo-black.png')) }}" width=400 class="img-fluid" />
            <br>
            <br>
        </td>
        <td style="vertical-align: middle;" align="right"><span style="font-size:30px;"><b>@lang('admin.custom.user_recipt')</b></span></td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="" style="color: #b531ba;">@lang('admin.custom.bill_from')</span><br>
            <b>{{$admin->name}}</b><br>
            <div style="width:80%;">{{$admin->address}}</div>
            <div style="width:80%;">{{$admin->country}}</div>
            <div style="width:80%;">{{$admin->zip_code}}</div>
            <div style="width:80%;">{{$admin->city}}</div>
        </td>
        <td align="right" class="">
            Date: {{appDate(now())}}<br>
            @lang('admin.reason.status'):
            @if($wallet->status == 'Accepted')<span style="color:#00c82a;">{{$wallet->status}}</span> </td>
            @else <span style="color:red;">{{$wallet->status}}</span></td>
            @endif
    </tr>
    <tr>
        <td colspan="2">
            <br>
            <span class="" style="color: #b531ba;">@lang('admin.custom.to')</span><br>
            @if($user->user_type == 'COMPANY')
            <b class="">{{$user->company_name}}</b> <br>
            <div style="width:80%;" class="">{{$user->company_address}}</div>
            <div style="width:80%;" class="">{{$user->company_zip_code}}</div>
            <div style="width:80%;" class="">{{$user->company_city}}</div>
            @else
            <b class="">{{$user->first_name}}</b> <br>
            <div style="width:80%;" class="">{{$user->last_name}}</div>
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
                    <td width="30%">@lang('admin.payment.transaction_id')</td>
                    <td width="40%" style="">@lang('admin.service.type')</td>
                    <td align="right">@lang('admin.fleets.amount')</td>
                </tr>
                <tr>
                    <td colspan="3" style="border-bottom: #e7e7e7 2px solid;"></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>

                <tr>
                    <td><span style="font-size:20px;">{{$wallet->alias}}</span></td>
                    <td style=""><span style="font-size:20px;">{{$wallet->type}}</span></td>
                    <td align="right"><span class="" style="font-size:20px;">{{currency($wallet->amount)}}</span></td>
                </tr>

                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3" style="border-bottom: #e7e7e7 2px solid;"></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>

            </table>
            <br>
            <br>
            <br>
            <br>
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