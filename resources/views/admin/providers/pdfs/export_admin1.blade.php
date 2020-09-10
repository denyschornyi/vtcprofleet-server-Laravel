<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>@lang('admin.request.Booking_ID')</th>
            <th>@lang('admin.request.date')</th>
            <th>@lang('admin.request.picked_up')</th>
            <td>@lang('admin.request.dropped')</td>
            <td>@lang('admin.request.status')</td>
            <td>@lang('admin.dashboard.total')</td>
            {{-- <th>@lang('admin.request.commission')</th>
            <th>@lang('admin.request.fleet_commission')</th>
            <th>@lang('admin.request.discount_price')</th>
            <th>@lang('admin.request.peak_amount')</th>
            <th>@lang('admin.request.peak_commission')</th>
            <th>@lang('admin.request.waiting_charge')</th>
            <th>@lang('admin.request.waiting_commission')</th>
            <th>@lang('admin.request.tax_price')</th>
            <th>@lang('admin.request.tips')</th>
            <th>@lang('use.ride.round_off')</th>
            <th>@lang('admin.request.total_amount')</th>
            <th>@lang('admin.request.wallet_deduction')</th>
            <th>@lang('admin.request.paid_amount')</th>
            <th>@lang('admin.request.payment_mode')</th>
            <th>@lang('admin.request.cash_amount')</th>
            <th>@lang('admin.request.card_amount')</th>
            <th>@lang('admin.request.provider_earnings')</th>
            <th>@lang('admin.request.status')</th> --}}
        </tr>
    </thead>
    <tbody>
    @foreach($ride as $index => $val)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{$val->booking_id}}</td>
            <td> {{date('d-m-Y',strtotime($val->created_at))}}</td>
            <td>
                @if($val->s_address != '')
                    {{$val->s_address}}
                @else
                    Not Provided
                @endif
            </td>
            <td>
                @if($val->d_address != '')
                    {{$val->d_address}}
                @else
                    Not Provided
                @endif
            </td>
            <td>{{ $val->status }}</td>
            <td>{{ $val->payment->total }}</td>
            {{-- <td>{{ currency_ohter($val->commision + $val->peak_comm_amount + $val->waiting_comm_amount) }}</td>
           
            
            <td>{{ currency_ohter($val->discount) }}</td>
            <td>{{ currency_ohter($val->peak_amount) }}</td>
            <td>{{ currency_ohter($val->peak_comm_amount) }}</td>
            <td>{{ currency_ohter($val->waiting_amount) }}</td>
            <td>{{ currency_ohter($val->waiting_comm_amount) }}</td>
            <td>{{ currency_ohter($val->tax) }}</td>
            <td>{{ currency_ohter($val->tips) }}</td>
            <td>{{ currency_ohter($val->round_of) }}</td>
            <td>{{ currency_ohter($val->total + $val->tips) }}</td>
            <td>{{ currency_ohter($val->wallet) }}</td>
            <td>{{ currency_ohter($val->payable) }}</td>
            <td>{{ $val->payment_mode }}</td>
            <td>{{ currency_ohter($val->cash) }}</td>
            <td>{{ currency_ohter($val->card) }}</td>
            <td>{{ currency_ohter($val->provider_pay) }}</td>
            <td>{{$val->status}}</td> --}}
        </tr>
    @endforeach
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{{ $revenue['overall'] }}</td>
        {{-- <td>{{currency_ohter($revenue[0]->commision)}}</td>
        <td>{{currency_ohter($revenue[0]->fleet)}}</td>
        <td>{{currency_ohter($revenue[0]->discount)}}</td>
        <td>{{currency_ohter($revenue[0]->peak_amount)}}</td>
        <td>{{currency_ohter($revenue[0]->peak_comm_amount)}}</td>
        <td>{{currency_ohter($revenue[0]->waiting_amount)}}</td>
        <td>{{currency_ohter($revenue[0]->waiting_comm_amount)}}</td>
        <td>{{currency_ohter($revenue[0]->tax)}}</td>
        <td>{{currency_ohter($revenue[0]->tips)}}</td>
        <td>{{currency_ohter($revenue[0]->round_of)}}</td>
        <td>{{currency_ohter($revenue[0]->total)}}</td>
        <td>{{currency_ohter($revenue[0]->wallet)}}</td>
        <td>{{currency_ohter($revenue[0]->payable)}}</td>
        <td></td>
        <td>{{currency_ohter($revenue[0]->cash)}}</td>
        <td>{{currency_ohter($revenue[0]->card)}}</td>
        <td>{{currency_ohter($revenue[0]->provider_pay)}}</td>
        <td></td> --}}
    </tr>
    </tbody>
</table>
