@extends('fleet.layout.base')

@section('title', 'Payment Transfer')

@section('content')

<div class="content-area py-1">
        <div class="container-fluid">

            <div class="box box-block bg-white">
                <h5 class="mb-1">Transfer Amount (@lang('provider.current_balance') : {{currency($wallet_balance)}})</h5>
                <div class="col-md-12 no-margin">
                    <form class="profile-form" action="{{route('fleet.userpro_payment')}}" method="POST"  role="form" id="requestform">
                    {{ csrf_field() }}
                    <div class="form-group row">
                         <div class="col-xs-3" style="padding: 0px; margin-right: 10px;">
                            <input type="hidden" name='type' value='fleet'/>
                            <input type="number" class="form-control" placeholder="@lang('provider.amount')" name="amount" value="" required />
                        </div>
                        <div class="col-xs-3" style="padding: 0px;">
                            <input type="email" class="form-control" placeholder="@lang('admin.userpro_email')" name="senderName" value="" required />
                        </div>
                        <div class="col-xs-2">
                            <select class="form-control" name="payment_mode" id="payment_mode">
                                <option value="CASH">Cash</option>
                                <option value="CHEQUE">Cheque</option>
                                <option value="WIRE BANK">Wire Bank</option>
                            </select>
                        </div>
                        <div class="col-xs-2">
                           <button type="submit" class="btn btn-block btn-primary">@lang('provider.transfer')</button>
                        </div>
                    </div>
                    </form>
               </div>
                <table class="table table-striped table-bordered dataTable" id="table-4">
                    <thead>
                        <tr>
                            <th style="width:10%">@lang('provider.sno')</th>
                            <th style="width: 15%">@lang('provider.transaction_ref')</th>
                            <th style="width:20%">@lang('admin.userpro_email')</th>
                            <th>@lang('provider.datetime')</th>
                            <th>@lang('provider.amount')</th>
                            <th>@lang('provider.payment_mode')</th>
                        </tr>
                    </thead>
                    <tbody>
                       @php($total=0)
                       @foreach($pendinglist as $index=>$pending)
                            @php($total+=$pending->amount)
                            <tr>
                                <td>{{$index+1}}</td>
                                <td>{{$pending->alias_id}}</td>
                                <td>{{ \App\User::where('id', $pending->user_id)->value('email') }}</td>
                                <td>{{appDateTime($pending->created_at)}}</td>
                                <td>{{currency($pending->amount)}}</td>
                                <td>
                                    {{$pending->via}}
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection

