<style>
    /* Modal Content */
    .modal-content,
    .modal-content1 {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }


    /* The Close Button */
    .close,
    .close1 {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close1:hover,
    .close:focus,
    .close1:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-icon-box {
        border: 2px solid #ccc;
        padding: 5px 10px;
        border-radius: 5px;
        background: 0 0;
    }

    #view-invoice {
        margin-top: 0 !important;
        top: 0 !important;
    }

    .ui.dimmer {
        background-color: rgba(0, 0, 0, 0.4) !important;
    }

    .earning-table.table>tbody>tr>td,
    .earning--table.table>tbody>tr>th,
    .earning--table.table>tfoot>tr>td,
    .earning--table.table>tfoot>tr>th,
    .earning--table.table>thead>tr>td,
    .earning--table.table>thead>tr>th {
        line-height: 35px;
    }
</style>
@extends('user.layout.base')

@section('title')

@section('content')

<!-- Invoice Modal -->
@foreach($wallet_transation as $wallet)
@if($wallet->status == 'Accepted')
@include('user.invoice.wallet-invoice', ['wallet' => $wallet, 'admin' => $admin])
@endif
@endforeach
<!-- Invoice Modal -->

<div class="col-md-12">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">@lang('user.my_wallet')</h4>
            </div>
        </div>
{{--        @include('common.notify')--}}
        <div class="row no-margin">
            <form action="{{url('add/money')}}" id="add_money" method="POST">
                {{ csrf_field() }}
                <div class="col-md-6">

                    <div class="wallet">
                        <h4 class="amount">
                            <span class="price" @if(Auth::user()->wallet_balance >= 0) style="color:green" @else style="color:red"@endif>{{currency(Auth::user()->wallet_balance)}}</span>
                            <span class="txt">@lang('user.in_your_wallet')</span>
                        </h4>
                    </div>

                </div>
                <div class="col-md-6">

                    <h6><strong>@lang('user.add_money')</strong></h6>

                    <select class="form-control" autocomplete="off" name="payment_mode" onchange="card(this.value);">
                        @if(Config::get('constants.card') == 1)
                        @if(!empty($cards))
                        <option value="CARD">@lang('admin.payment.cabssh')</option>
                        @endif
                        @if(Config::get('constants.braintree') == 1)
                        <option value="BRAINTREE">BRAINTREE</option>
                        @endif
                        @endif
                        @if(Config::get('constants.payumoney') == 1)
                        <option value="PAYUMONEY">PAYUMONEY</option>
                        @endif
                        @if(Config::get('constants.paypal') == 1)
                        <option value="PAYPAL">PAYPAL</option>
                        @endif
                        @if(Config::get('constants.paytm') == 1)
                        <option value="PAYTM">PAYTM</option>
                        @endif
                        @if($is_company)
                        <option value="CHEQUE">@lang('admin.custom.user_cheq')</option>
                        <option value="WIREBANK">@lang('admin.custom.user_wire')</option>
                        @endif
                    </select>
                    <br>

                    @if(Config::get('constants.card') == 1 && count($cards) > 0)
                    <select style="display: none;" class="form-control" name="card_id" id="card_id">
                        @foreach($cards as $card)
                        <option @if($card->is_default == 1) selected @endif value="{{$card->card_id}}">{{$card->brand}} **** **** **** {{$card->last_four}}</option>
                        @endforeach
                    </select>
                    @endif

                    @if(Config::get('constants.braintree') == 1)
                    <div style="display: none;" id="braintree">
                        <div id="dropin-container"></div>
                    </div>
                    @endif

                    <br>
                    @if(Config::get('constants.braintree') == 1)
                    <input type="hidden" name="braintree_nonce" value="" />
                    @endif
                    <input type="hidden" name="user_type" value="user" />
                    <div class="input-group full-input">
                        <input type="number" class="form-control" name="amount" placeholder="@lang('user.enter_amount')" required>
                    </div>


                    <button type="submit" id="submit-button" class="full-primary-btn fare-btn">@lang('user.add_money')</button>

                </div>
            </form>

        </div>

        <div class="manage-doc-section-content border-top">
            <div class="tab-content list-content">
                <div class="list-view pad30 ">
                    <table class="earning-table table table-responsive" style="display: table;">
                        <thead>
                            <tr>
                                <th>@lang('provider.sno')</th>
                                <th>@lang('provider.transaction_ref')</th>
                                <th>@lang('provider.status')</th>
                                <th>@lang('provider.type')</th>
                                <th>@lang('provider.amount')</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($page = ($pagination->currentPage-1)*$pagination->perPage)
                            @foreach($wallet_transation as $i=>$wallet)
                            @php($page++)
                            <tr>
        						                        <td>{{$page}}</td>
        						                        <td>{{$wallet->alias}}</td>
        						                        <td><span style="color:white; padding:6px 10px; border-radius:3px; @if($wallet->status == 'Pending') background-color:orange @elseif($wallet->status == 'Accepted') background-color:green @elseif($wallet->status == 'Refused') background-color:red @endif">{{$wallet->status}}</span></td>
        						                        <td>{{$wallet->type}}</td>
        						                        <td>{{currency($wallet->amount)}}</td>
                                <td class="viewinvoicebt text-center">
                                    @if ($wallet->status == 'Accepted')
                                    <form method="get" class="invoiceForm" req_id="{{$wallet->id}}" action="#" style="margin:0">
                                        <button type="submit" class="btn outine" name="edit-mile1">@lang('admin.custom.user_recei')</button>
                                    </form>
                                    @endif
                                </td>

                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                    {{ $wallet_transation->links() }}
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
@if(Config::get('constants.braintree') == 1)
<script src="https://js.braintreegateway.com/web/dropin/1.14.1/js/dropin.min.js"></script>

<script>
    var button = document.querySelector('#submit-button');
    var form = document.querySelector('#add_money');
    braintree.dropin.create({
        authorization: '{{$clientToken}}',
        container: '#dropin-container',
        //Here you can hide paypal
        paypal: {
            flow: 'vault'
        }
    }, function(createErr, instance) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (document.querySelector('select[name="payment_mode"]').value == "BRAINTREE") {
                instance.requestPaymentMethod(function(requestPaymentMethodErr, payload) {
                    document.querySelector('input[name="braintree_nonce"]').value = payload.nonce;
                    console.log(payload.nonce);
                    form.submit();
                });
            } else {
                form.submit();
            }

        });
    });
</script>
@endif

<script type="text/javascript">
    @if(Config::get('constants.card') == 1)
    card('CARD');
    @endif

    function card(value) {
        $('#card_id, #braintree').fadeOut(300);
        if (value == 'CARD') {
            $('#card_id').fadeIn(300);
        } else if (value == 'BRAINTREE') {
            $('#braintree').fadeIn(300);
        }
    }

    $('.invoiceForm').on('submit', function(e) {
        var id = $(this).attr('req_id');

        $('#view-invoice' + id).modal('show');
        $("#view-invoice" + id).css('top', 0);
        $("#view-invoice" + id).css('margin-top', 0);
        $("#view-invoice" + id).scrollTop(0);

        e.preventDefault();
        return false;
    });

    $('a.download_pdf').on('click', function() {
        $('#formDownloadPDF' + $(this).attr('wallet_id')).submit();
    });
</script>
@endsection
