<table id="html-2-pdfwrapper" width="100%" style="margin-top:60px;">
    <tr>
        <td>
            <br>
            <img src="{{ config('constants.site_logo', asset('logo-black.png')) }}" width=400 class="img-fluid" />
            <br>
            <br>
        </td>
        <td align="right"><br><span style="font-size:30px; vertical-align:middle"><b>@lang('admin.custom.invoice')</b></span></td>
    </tr>
    <tr>
        <td>
            <span class="" style="color: #b531ba;">@lang('admin.custom.bill_from')</span><br>
            <b>{{Auth::guard('admin')->user()->name}}</b><br>
            <div style="width:230px;">{{Auth::guard('admin')->user()->address}}</div>
            <div style="width:230px;">{{Auth::guard('admin')->user()->country}}</div>
            <div style="width:230px;">{{Auth::guard('admin')->user()->zip_code}}</div>
            <div style="width:230px;">{{Auth::guard('admin')->user()->city}}</div>
        </td>
        <td align="right" class="invoice-date">
            Date: {{date("d-m-Y")}}<br>
        </td>
    </tr>
    <tr>
        <td>
            <br>
            <span class="" style="color: #b531ba;">@lang('admin.custom.to')</span><br>
            <b>{{$user->first_name}}</b> <br>
            <div style="width:230px;">{{$user->last_name}}</div>
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
                    <td width="30%">@lang('admin.reason.status')</td>
                    <td width="70%" align="right">@lang('admin.fleets.amount')</td>
                </tr>
                <tr>
                    <td colspan="3" style="border-bottom: #e7e7e7 2px solid;"></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td><span style="font-size:24px;"><b style="color:#00c82a;">@lang('admin.custom.Paid')</b></span></td>
                    <td align="right"><span class="ride_total_paid" style="font-size:24px;font-weight:bold;color:#00c82a;">@if($request->ride_total_paid) {!!html_entity_decode($request->ride_total_paid)!!} @endif</span></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td><span style="font-size:24px;"><b style="color:red">@lang('admin.custom.Unpaid')</b></span></td>
                    <td align="right"><span class="ride_total_unpaid" style="font-size:24px;font-weight:bold;color:red;">@if($request->ride_total_unpaid) {!!html_entity_decode($request->ride_total_unpaid)!!} @endif</span></td>
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
                <tr>
                    <td></td>
                    <td align="right"><span class="ride_total" style="font-size:24px;font-weight:bold;color:#b531ba;">@if($request->ride_total) {!!html_entity_decode($request->ride_total)!!} @endif</span></td>
                </tr>
            </table>
            <br>
            <br>
            <br>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <br>
            <br>
            <br>
            <br>
        </td>
    </tr>
    <tr>
        <td><span class="" style="color: #b531ba;">Note</span></td>
        <td align="right"><span class="" style="color: #b531ba;">@lang('admin.custom.vtcpro')</span></td>
    </tr>
    <tr>
        <td>
            <span style="font-size:14;">{{Auth::guard('admin')->user()->note}}</span>
        </td>
        <td align="right">
            <div style="width:230px;float: right;">
            <div><span style="font-size:14;">{{Auth::guard('admin')->user()->name}}</span></div>
            <div><span style="font-size:14;">{{Auth::guard('admin')->user()->address}}</span></div>
            <div><span style="font-size:14;">{{Auth::guard('admin')->user()->zip_code}}</span></div>
            <div><span style="font-size:14;">{{Auth::guard('admin')->user()->city}}</span></div>
            </div>
        </td>
    </tr>
</table>