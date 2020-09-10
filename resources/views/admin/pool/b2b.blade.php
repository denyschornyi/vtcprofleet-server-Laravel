@extends('admin.layout.base')

@section('title', $page)

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <h3>{{$page}}</h3>

                <div class="row">

                    <div class="row row-md mb-2" style="padding: 15px;">
                        <div class="col-md-12">
                            <div class="box bg-white">
                                <div class="box-block clearfix">
                                    <h5 class="float-xs-left">@lang('admin.include.b2b_histroy')</h5>
                                    <div class="float-xs-right">
                                    </div>
                                </div>

                                @if(count($full_transactions) != 0)
                                <table class="table table-striped table-bordered dataTable" id="table-6">
                                    <thead>
                                    <tr>
                                        <td>@lang('admin.fleets.name')</td>
                                        <td>@lang('admin.mobile')</td>
                                        <td>@lang('admin.fleet.fledebit')</td>
                                        <td>@lang('admin.fleet.flecredit')</td>
                                        <td>@lang('admin.fleet.balance')</td>
                                        <td>@lang('admin.poi.action')</td>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($full_transactions as $index => $full_transaction)
                                        <tr>
                                            <td>
                                                {{$full_transaction['company']}}
                                            </td>
                                            <td>
                                                {{$full_transaction['country_code']}} {{$full_transaction['mobile']}}
                                            </td>
                                            <td>
                                                {{ currency($full_transaction['credit']) }}
                                            </td>
                                            <td>
                                                {{ currency($full_transaction['debit']) }}
                                            </td>
                                            <td>
                                                {{ currency(currency_number($full_transaction['debit']) - currency_number($full_transaction['credit'])) }}
                                            </td>
                                            <td>
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn btn-info dropdown-toggle"
                                                            data-toggle="dropdown">@lang('admin.payment.acths')
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a href="{{ route('admin.b2b.history',[1, $full_transaction['id']]) }}"
                                                               class="btn btn-default"><i class="fa fa-send"></i> @lang('admin.send')</a><br>
                                                            <a href="{{ route('admin.b2b.history',[2, $full_transaction['id']]) }}"
                                                               class="btn btn-default assign_provider"><i
                                                                        class="fa fa-check"></i> @lang('admin.fleets.accept')</a><br>
                                                            {{-- <a href=""
                                                               class="btn btn-default assign_provider"><i
                                                                        class="fa fa-transgender"></i> @lang('admin.transfer')</a><br> --}}
                                                            <a href="" class="btn btn-default demand" data-toggle="modal" data-target="#transferModal" data-id="send" data-href="{{route('admin.b2b.payment', $full_transaction['id']) }}" data-rid="{{$pending->id}}"><i class="fa fa-money"></i> @lang('admin.fleets.payment')</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tfoot>
                                    <tr>
                                        <td>@lang('admin.fleets.name')</td>
                                        <td>@lang('admin.mobile')</td>
                                        <td>@lang('admin.fleet.flecredit')</td>
                                        <td>@lang('admin.fleet.fledebit')</td>
                                        <td>@lang('admin.fleet.balance')</td>
                                        <td>@lang('admin.poi.action')</td>
                                    </tr>
                                    </tfoot>
                                </table>
                                @else
                                    <h6 class="no-result">@lang('admin.custom.reds')</h6>
                                @endif

                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>

        <div id="transferModal" class="modal fade" role="dialog" data-backdrop="static" aria-hidden="true" data-keyboard="false" >
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content" style="width:90%">
                    <div class="modal-header">
                        {{-- <button type="button" class="close" data-dismiss="modal">&times;</button> --}}
                        <h3 class="modal-title" id="settitle" style="color: grey;">Payment Request</h3>
                    </div>
                    <form action="" method="Get" id="transurl">
                        <div class="modal-body">
                                <label for="send_by" class="col-form-label" style="font-weight:bold; font-size:11pt; color:grey;">Amount</label>
                                <input type="number" placeholder="0" class="form-control" value="" name="request_amount"  style="width:100%;" required/>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Confirm</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
        
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script type="text/javascript">
    $('.demand').click(function(){
        var curl = $(this).attr('data-href');
        $('#transurl').attr('action', curl);
        
    });
</script>
@endsection
