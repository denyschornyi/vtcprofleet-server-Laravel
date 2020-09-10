<div id="view-invoice" class="modal" style="overflow: auto;">
    <form method="post" class="" action="{{url('admin/download-pdf')}}" style="margin:0" id="formDownloadPDF">
        {{ csrf_field() }}
        <input type="hidden" name="bill_from_first_name" value="" id="bill_from_first_name"/>
        <input type="hidden" name="bill_from_last_name" value="" id="bill_from_last_name"/>
        <input type="hidden" name="ride_total" value="" id="ride_total"/>
        <input type="hidden" name="ride_total_paid" value="" id="ride_total_paid"/>
        <input type="hidden" name="ride_total_unpaid" value="" id="ride_total_unpaid"/>
        <input type="hidden" name="user_id" value="" id="user_id"/>

        <div class="modal-content text-center" style="width: 880px;">
            <div class="modal-header" style="height: 70px;">
                <a type="button" class="close" data-dismiss="modal"
                   style="top: 45px; -webkit-appearance:none;">&times;</a>
                <span class="modal-title" style="font-size: 26px;float:left;">@lang('admin.provides.inv')</span>
                <a href="#" class="prnintpage" onclick="window.print(); return false;"
                   style="position: absolute;right: 100px;"><i class="fa fa-print fa-2x modal-icon-box"
                                                               aria-hidden="true" style="color:#ccc;"></i></a>
                <a href="#" name="mile_submit" id="download_pdf" style="position: absolute;right: 47px;"><i
                            class="fa fa-download fa-2x modal-icon-box" aria-hidden="true" style="color:#ccc;"></i></a>
            </div>
            <div>
                <div id="invoicecont" class="invoice-box" style="max-width: initial;">
                    <div id="_editor"></div>
                    <table id="html-2-pdfwrapper" width="100%">
                        <tr>
                            <td>
                                <br>
                                <img src="{{ config('constants.site_logo', asset('logo-black.png')) }}" width=400
                                     class="img-fluid"/>
                                <br>
                                <br>
                            </td>
                            <td align="right" style="vertical-align:middle;"><br><span
                                        style="font-size:30px;"><b>@lang('admin.custom.invoice')</b></span></td>
                        </tr>
                        <tr>
                            <td>
                                <span class="" style="color: #b531ba;">@lang('admin.custom.bill_from')</span><br>
                                <b>{{Auth::guard('admin')->user()->name}}</b><br>
                                <div style="width:80%;">{{Auth::guard('admin')->user()->address}}</div>
                                <div style="width:80%;">{{Auth::guard('admin')->user()->country}}</div>
                                <div style="width:80%;">{{Auth::guard('admin')->user()->zip_code}}</div>
                                <div style="width:80%;">{{Auth::guard('admin')->user()->city}}</div>
                            </td>
                            <td align="right" class="invoice-date">
                                Date: {{date("d-m-Y")}}<br>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <br>
                                <span class="" style="color: #b531ba;">@lang('admin.custom.to')</span><br>
                                <b class="user_first_name"></b> <br>
                                <div style="width:80%;" class="user_last_name"></div>
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
                                        <td align="right">@lang('admin.fleets.amount')</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="border-bottom: #e7e7e7 2px solid;"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td><span style="font-size:24px;"><b
                                                        style="color:#00c82a;">@lang('admin.custom.Paid')</b></span>
                                        </td>
                                        <td align="right"><span class="ride_total_paid"
                                                                style="font-size:24px;font-weight:bold;color:#00c82a;">2.00€</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td><span style="font-size:24px;"><b
                                                        style="color:red">@lang('admin.custom.Unpaid')</b></span></td>
                                        <td align="right"><span class="ride_total_unpaid"
                                                                style="font-size:24px;font-weight:bold;color:red;">2.00€</span>
                                        </td>
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
                                        <td align="right"><span class="ride_total"
                                                                style="font-size:24px;font-weight:bold;color:#b531ba;">2.00€</span>
                                        </td>
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
                            </td>
                        </tr>
                        <tr>
                            <td><span class="" style="color: #b531ba;">Note</span></td>
                            <td align="right"><span class="" style="color: #b531ba;">@lang('admin.custom.vtcpro')</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span style="font-size:16;">{{Auth::guard('admin')->user()->note}}</span>
                            </td>
                            <td align="right">
                                <div style="width:230px;float: right;">
                                    <div><span style="font-size:16;">{{Auth::guard('admin')->user()->name}}</span></div>
                                    <div><span style="font-size:16;">{{Auth::guard('admin')->user()->address}}</span>
                                    </div>
                                    <div><span style="font-size:16;">{{Auth::guard('admin')->user()->zip_code}}</span>
                                    </div>
                                    <div><span style="font-size:16;">{{Auth::guard('admin')->user()->city}}</span></div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>
