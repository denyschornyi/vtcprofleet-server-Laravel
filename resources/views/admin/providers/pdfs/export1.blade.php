<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>@lang('admin.request.Booking_ID')</th>
            
            @if($statement_for == 'user')
            <th>@lang('admin.request.picked_up')</th>
            @endif

            @if($statement_for == 'user')
            <th>@lang('admin.request.dropped')</th>
            @endif
            
            <th>@lang('admin.request.date')</th>
            
            @if($statement_for != 'user')
            <th>@lang('admin.request.commission')</th>
            @endif

            @if($statement_for == 'fleet')
            <th>@lang('admin.custom.pool_commission')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.custom.admin_commission')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.discount_price')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.peak_amount')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.peak_commission')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.waiting_charge')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.waiting_commission')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.tax_price')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.tips')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('user.ride.round_off')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.total_amount')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.wallet_deduction')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.paid_amount')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.payment_mode')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.cash_amount')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.card_amount')</th>
            @endif

            @if($statement_for != 'user')
            <th>@lang('admin.request.provider_earnings')</th>
            @endif
            
            <th>@lang('admin.request.status')</th>

            @if($statement_for == 'user')
            <th>@lang('admin.dashboard.total')</th>
            @endif
        </tr>
    </thead>
    <tbody>
    @foreach($ride as $index => $val)
        <tr>
            <td>{{$index + 1}}</td>
            <td>{{$val->booking_id}}</td>
            @if($statement_for == 'user')
            <td>
                @if($val->s_address != '')
                    {{$val->s_address}}
                @else
                    Not Provided
                @endif
            </td>
            @endif

            @if($statement_for == 'user')
            <td>
                @if($val->d_address != '')
                    {{$val->d_address}}
                @else
                    Not Provided
                @endif
            </td>
            @endif
            
            <td> {{date('d-m-Y',strtotime($val->created_at))}}</td>
            @if($statement_for == 'fleet')
            <td>
                @if((in_array($val->user_id, $userIds) && in_array($val->provider_id, $providerIds)) || !in_array($val->user_id, $userIds))
                    {{ currency_ohter($val->payment->commision + $val->payment->peak_comm_amount + $val->payment->waiting_comm_amount) }}
                @else
                    {{ currency_ohter(0.00) }}
                @endif
            </td>
            @elseif($statement_for == 'provider')
            <td>{{ currency_ohter($val->payment->commision + $val->payment->peak_comm_amount + $val->payment->waiting_comm_amount) }}</td>
            @endif

            @if($statement_for == 'fleet')
            <td>
                @if(in_array($val->user_id, $userIds) && !in_array($val->provider_id, $providerIds))
                    {{ currency_ohter($val->payment->pool_commission) }}
                @else
                    {{ currency_ohter(0.00) }}
                @endif
            </td>
            @endif

            @if($statement_for == 'fleet')
            <td>
                @if(in_array($val->user_id, $userIds))
                    {{ currency_ohter($val->payment->admin_commission) }}
                @else
                    {{ currency_ohter(0.00) }}
                @endif
            </td>
            @elseif($statement_for == 'provider')
            <td>@if(in_array($val->user_id, $userIds)){{ currency_ohter($val->payment->admin_commission) }}@else{{ currency_ohter(0.00) }}@endif</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->discount) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->peak_amount) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->peak_comm_amount) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->waiting_amount) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->waiting_comm_amount) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->tax) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->tips) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->round_of) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->tax + $val->payment->tips) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->wallet) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->payable) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ $val->payment_mode }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->cash) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->card) }}</td>
            @endif
            @if($statement_for != 'user')
            <td>{{ currency_ohter($val->payment->provider_pay) }}</td>
            @endif
            <td>{{$val->status}}</td>
            @if($statement_for == 'user')
            <td>{{ $val->payment->total }}</td>
            @endif
        </tr>
    @endforeach
    <tr>
        <td></td>
        <td></td>
        @if($statement_for == 'user')
        <td></td>
        @endif
        @if($statement_for == 'user')
        <td></td>
        @endif
        <td></td>
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['commission'])}}</td>
        @endif
        @if($statement_for == 'fleet')
        <td>{{currency_ohter($revenue['pool_commission'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['admin_commission'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['discount'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['peak_amount'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['peak_comm_amount'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['waiting_amount'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['waiting_comm_amount'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['tax'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['tips'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['round_of'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['total'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['wallet'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['payable'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td></td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['cash'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['card'])}}</td>
        @endif
        @if($statement_for != 'user')
        <td>{{currency_ohter($revenue['provider_pay'])}}</td>
        @endif
        <td></td>
        @if($statement_for == 'user')
        <td>{{ $revenue['overall'] }}</td>
        @endif
    </tr>
    </tbody>
</table>
