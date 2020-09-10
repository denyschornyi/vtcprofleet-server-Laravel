@extends('user.layout.base')

@section('title')

@section('styles')
<style>
    /* .cliproject-box .cliproj-body .cliproj-bodybox .cliproj-bcont {
        line-height: 4;
    } */
    a.btn-make-payment {
        background: #b531ba;
        background: -moz-linear-gradient(left, #b531ba 0, #e65feb 100%);
        background: -webkit-linear-gradient(left, #b531ba 0, #e65feb 100%);
        background: linear-gradient(to right, #b531ba 0, #e65feb 100%);
        color: #fff;
        padding: 10px;
        border-radius: 5px;
        display: inline-block;
        text-align: center;
        margin-top: 6px;
        font-size: 16px;
        font-weight: 700;
    }
</style>
@endsection

@section('content')

<div class="row db-container cont-top-main">

    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="clidashright" style="padding:0">
            <div class="clidashr-ac" style="margin-top:0">
                <div class="clidashr-achead">@lang('admin.include.accounting')</div>
                <div class="clidash-acmile">
                    <div class="row">
                        <div class="col-md-6 clidash-acmiletm">
                            <div class="clidash-acmiletmhead">@lang('admin.custom.user_totopr')</div>
                            <div class="clidash-acmilebody">{{number_format($total, 2, '.', '').config('constants.currency')}}</div>
                        </div>
                        <div class="col-md-6 clidash-acmilepm">
                            <div class="clidash-acmiletmhead">@lang('admin.custom.user_totorid')</div>
                            <div class="clidash-acmilebody">{{number_format($total_paid, 2, '.', '').config('constants.currency')}}</div>
                        </div>
                    </div>
                </div>
                <div class="clidash-acmileb">
                    <div class="row">
                        <div class="col-md-12 clidash-acmiletmb">
                            <div class="clidash-acmiletmheadb" style="color:red">@lang('admin.custom.user_due')</div>
                            <div class="clidash-acmilebodyb" style="@if($unpaid < 0) color:red;@endif">{{number_format($unpaid, 2, '.', '').config('constants.currency')}}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="admin-dash-rt blue-shadow ">
            <div class="container" style="max-width:100% !important;">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-8 dash-lftb" style="height:40px;"><span style="color:white;">@lang('admin.custom.Unpaid')</span></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-4 dash-rttb" style="color:white">{{$unpaid_rides}}</div>
                </div>
            </div>
        </div>
        <div class="admin-dash-rt white-admin-box admin-mid-box" style="margin:22px 0">
            <div class="container" style="max-width:100% !important;">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-8 dash-lftb"><span>@lang('admin.provides.Total_Rides')</span><a href="{{url('trips')}}">@lang('admin.fleets.view')</a></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-4 dash-rttb">{{$trips}}</div>
                </div>
            </div>
        </div>
        <div class="admin-dash-rt white-admin-box">
            <div class="container" style="max-width:100% !important;">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-8 dash-lftb"><span>@lang('admin.dashboard.scheduled')</span><a href="{{url('upcoming/trips')}}">@lang('admin.fleets.view')</a></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-4 dash-rttb">{{$upcoming_trips}}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12" style="height:30px"></div>
    @foreach($pending_unpaid_rides as $ride)
    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="cliproject-box" style="margin-top:0">
            <div class="cliproj-head">
                <div class="row">
                    <div class="col-md-6 cliproj-heading">@lang('admin.request.Booking_ID')</div>
                    <div class="col-md-6 cliproj-staff">
                        <div class="cliproj-shead" style="font-weight:800">
                            {{$ride->booking_id}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="cliproj-body">
                <div class="row">
                    <div class="col-md-3 cliproj-bodybox">
                        <div class="cliproj-bhead">@lang('admin.fleets.amount')</div>
                        <div class="cliproj-bcont">{{number_format($ride->payment->total, 2, '.', '').config('constants.currency')}}</div>
                    </div>
                    <div class="col-md-3 cliproj-bodybox">
                        <div class="cliproj-bhead">Date</div>
                        <div class="cliproj-bcont">{{appDate($ride->created_at)}}</div>
                    </div>
                    <div class="col-md-3 cliproj-bodybox">
                        <div class="cliproj-bhead">@lang('admin.reason.status')</div>
                        <div class="cliproj-bcont"><span class="label label-danger" style="padding:10px 20px">@if($ride->status == 'SCHEDULED') Pending @else Unpaid @endif</span></div>
                    </div>
                    <div class="col-md-3 cliproj-bodybox btnaligncls">
                        @if($ride->paid == 0)<a href="{{url('/wallet')}}" class="btn-make-payment"> @lang('admin.custom.user_make') </a>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection