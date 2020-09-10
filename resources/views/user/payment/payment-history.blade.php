@extends('user.layout.base')

@section('title')

@section('content')

<div class="row">
    <div class="col-md-12 margin-top-10 clients">
        <div class="row project-dash">
            <div class="col-sm-6">
                <div class="pm-heading" style="width:100%;">
                    <h2>@lang('admin.payment.payment_history')</h2><span>@lang('admin.custom.user_pay')</span>
                </div>
            </div>
            <div class="col-sm-6 creative-right text-right">
                <div class="pm-form" style="width: 100%;">
                    <form class="form-inline md-form form-sm">
                        <input class="form-control form-control" type="text" placeholder="Search Payment..." id="protbl-input">
                        <!-- <button type="submit"><i class="fa fa-search" aria-hidden="true"></i></button> -->
                    </form>
                </div>
                <!-- <a href="add-new-project.php" class="btn orange"> Add new project</a></div>-->
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="table-responsive">
                <table class="table table-new table-invoice" data-pagination="true" data-page-size="5">
                    <thead>
                        <tr>
                            <th>@lang('admin.payment.request_id')</th>
                            <th>@lang('admin.payment.transaction_id')</th>
                            <!-- <th>@lang('admin.payment.from')</th>
                                <th>@lang('admin.payment.to')</th> -->
                            <th>@lang('admin.payment.total_amount')</th>
                            <th>@lang('admin.payment.provider_amount')</th>
                            <th>@lang('admin.payment.payment_mode')</th>
                            <th>@lang('admin.payment.payment_status')</th>
                            <th style="text-align: left;">@lang('admin.provides.inv')</th>
                        </tr>
                    </thead>
                    <tbody id="projects-tbl">
                    
                    @foreach($payments as $index => $payment)

                        <tr style="display: table-row;">
                            <td class="countertd">{{$payment->id}}</td>
                            <td class="tbl-ttl"><span class="onmobile">@lang('admin.payment.transaction_id') </span>
                            @if(!empty($payment->payment->payment_id)){{$payment->payment->payment_id}}@else NA @endif
                            </td>
                            <td class="tbl-ttl"><span class="onmobile">@lang('admin.payment.total_amount') </span>{{currency($payment->payment->total)}}</td>
    						                        <td><span class="onmobile centera">@lang('admin.payment.provider_amount') </span>{{currency($payment->payment->provider_pay)}}</td>
    						                        <td><span class="onmobile centera">@lang('admin.payment.payment_mode') </span>{{$payment->payment_mode}}</td>
                            <td class="text-center"><span class="onmobile centera">@lang('admin.payment.payment_status')</span><span class="paidstatus @if($payment->paid == 1) paid @else unpaid @endif">@if($payment->paid == 1) @lang('admin.custom.Paid') @else @lang('admin.custom.Unpaid') @endif<span></td>
                            <td class="viewinvoicebt">
                                <form method="get" action="#">
                                    <input type="hidden" value="5000" name="edit_id2">
                                    <input type="hidden" value="55" name="edit_id1">
                                    <button type="submit" class="btn outine" name="edit-mile1"> @lang('admin.custom.user_invoi')</button>
                                </form>
                            </td>
                        </tr>

                        @endforeach

                    </tbody>
                </table>
                @include('common.pagination')
            </div>
        </div>
    </div>
</div>
@endsection 