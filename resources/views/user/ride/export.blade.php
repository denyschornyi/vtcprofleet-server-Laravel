<table>
    <thead>
        <tr>
            <th>Booking ID</th>
            <th>Date</th>
            <th>Passenger</th>
            <th>Total Amount</th>
            <th>Total without Tax</th>
            <th>Tax Amount</th>
        </tr>
    </thead>
    <tbody>
    @foreach($trip as $val)
        <tr>
            <td>{{$val->booking_id}}</td>
            <td> {{date('d-m-Y',strtotime($val->assigned_at))}}</td>
            <td>
                {{$val->user?$val->user->first_name:''}} {{$val->user?$val->user->last_name:''}}
            </td>
            <td>
                @if($val->payment_mode==='CASH' && $val->use_wallet === 0)
                    {{currency(round($val->payment->total-$val->payment->discount+$val->payment->tips))}}
                @else
                    {{currency($val->payment->total-$val->payment->discount+$val->payment->tips)}}
                @endif
            </td>
            <td>{{currency($val->payment->fixed + $val->payment->round_of + $val->payment->distance)}}</td>
            <td>{{currency($val->payment->tax)}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
