<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>@lang('admin.request.Booking_ID')</th>
            <th>@lang('admin.request.date')</th>
            <th>@lang('admin.request.commission')</th>
            <th>@lang('admin.custom.admin_commission')</th>
            @if($statement_for == 'admin' || $statement_for == 'provider')
            <th>@lang('admin.custom.pool_commission')</th>
            @endif
            <th>@lang('admin.request.discount_price')</th>
            <th>@lang('admin.request.peak_amount')</th>
            <th>@lang('admin.request.peak_commission')</th>
            <th>@lang('admin.request.waiting_charge')</th>
            <th>@lang('admin.request.waiting_commission')</th>
            <th>@lang('admin.request.tax_price')</th>
            <th>@lang('admin.request.tips')</th>
            <th>@lang('user.ride.round_off')</th>
            <th>@lang('admin.request.total_amount')</th>
            <th>@lang('admin.request.wallet_deduction')</th>
            <th>@lang('admin.request.paid_amount')</th>
            <th>@lang('admin.request.payment_mode')</th>
            <th>@lang('admin.request.cash_amount')</th>
            <th>@lang('admin.request.card_amount')</th>
            <th>@lang('admin.request.provider_earnings')</th>
            <th>@lang('admin.request.status')</th>
        </tr>
    </thead>
    <tbody>
    @foreach($ride as $index=> $val)
        <tr>
            <td>{{$index + 1}}</td>
            <td>{{$val->booking_id}}</td>
            <td> {{date('d-m-Y',strtotime($val->created_at))}}</td>

            @if($statement_for=='admin')
            <td>@if(in_array($val->provider_id, $admin_provider_ids)){{ currency($val->payment->commision + $val->payment->peak_comm_amount + $val->payment->waiting_comm_amount) }}@else{{ currency(0.00) }}@endif</td>
            @elseif($statement_for == 'fleet')
            <td>@if((in_array($val->user_id, $user_ids) && in_array($val->provider_id, $admin_provider_ids))){{currency($val->payment->commision + $val->payment->peak_comm_amount + $ride->payment->waiting_comm_amount)}}@else{{ currency(0.00) }}@endif</td>
            @elseif($statement_for == 'provider')
            <td>{{currency($val->payment->commision + $val->payment->peak_comm_amount + $val->payment->waiting_comm_amount)}}</td>
            @endif
            
            @if($statement_for == 'admin')
            <td>@if(!in_array($val->user_id, $admin_user_ids)){{ currency($val->payment->admin_commission) }}@else{{ currency(0.00) }}@endif</td>
            @elseif($statement_for == 'fleet')
            <td>{{ currency($val->payment->admin_commission) }}</td>
            @elseif($statement_for == 'provider')
            <td>@if(in_array($val->user_id, $admin_user_ids)){{ currency($val->payment->admin_commission) }}@else{{ currency(0.00) }}@endif</td>
            @endif
           
            @if($statement_for == 'admin')
            <td>@if((in_array($val->user_id, $admin_user_ids) && !in_array($val->provider_id, $admin_provider_ids))){{ currency($val->payment->pool_commission) }}@else{{ currency(0.00) }}@endif</td>
            @elseif($statement_for == 'provider')
            <td>@if(in_array($val->user_id, $admin_user_ids)){{ currency(0.00) }}@else{{ currency($val->payment->pool_commission) }}@endif</td>
            @endif
            
            <td>{{ currency_ohter($val->payment->discount) }}</td>
            <td>{{ currency_ohter($val->payment->peak_amount) }}</td>
            <td>{{ currency_ohter($val->payment->peak_comm_amount) }}</td>
            <td>{{ currency_ohter($val->payment->waiting_amount) }}</td>
            <td>{{ currency_ohter($val->payment->waiting_comm_amount) }}</td>
            <td>{{ currency_ohter($val->payment->tax) }}</td>
            <td>{{ currency_ohter($val->payment->tips) }}</td>
            <td>{{ currency_ohter($val->payment->round_of) }}</td>
            <td>{{ currency_ohter($val->payment->total + $val->tips) }}</td>
            <td>{{ currency_ohter($val->payment->wallet) }}</td>
            <td>{{ currency_ohter($val->payment->payable) }}</td>
            <td>{{ $val->payment_mode }}</td>
            <td>{{ currency_ohter($val->payment->cash) }}</td>
            <td>{{ currency_ohter($val->payment->card) }}</td>
            <td>{{ currency_ohter($val->payment->provider_pay) }}</td>
            <td>{{$val->status}}</td>
        </tr>
    @endforeach
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>{{currency_ohter($revenue['commission'])}}</td>
        <td>{{currency_ohter($revenue['admin_commission'])}}</td>
        @if($statement_for == 'admin' || $statement_for == 'provider')
        <td>{{currency_ohter($revenue['pool_commission'])}}</td>
        @endif
        <td>{{currency_ohter($revenue['discount'])}}</td>
        <td>{{currency_ohter($revenue['peak_amount'])}}</td>
        <td>{{currency_ohter($revenue['peak_comm_amount'])}}</td>
        <td>{{currency_ohter($revenue['waiting_amount'])}}</td>
        <td>{{currency_ohter($revenue['waiting_comm_amount'])}}</td>
        <td>{{currency_ohter($revenue['tax'])}}</td>
        <td>{{currency_ohter($revenue['tips'])}}</td>
        <td>{{currency_ohter($revenue['round_of'])}}</td>
        <td>{{currency_ohter($revenue['total'])}}</td>
        <td>{{currency_ohter($revenue['wallet'])}}</td>
        <td>{{currency_ohter($revenue['payable'])}}</td>
        <td></td>
        <td>{{currency_ohter($revenue['cash'])}}</td>
        <td>{{currency_ohter($revenue['card'])}}</td>
        <td>{{currency_ohter($revenue['provider_pay'])}}</td>
        <td></td>
    </tr>
    </tbody>
</table>
