@if(empty($wallet) || empty($admin))
@else
<div id="view-invoice{{$wallet->id}}" class="modal" style="overflow: auto;">
    <form method="post" class="" action="{{url('invoice-wallet-pdf')}}" style="margin:0" id="formDownloadPDF{{$wallet->id}}">
        {{ csrf_field() }}
        <input type="hidden" name="wallet_id" value="{{$wallet->id}}" />

        <div class="modal-content text-center" style="width: 880px;">
            <div class="modal-header" style="height: 70px;">
                <a type="button" class="close" data-dismiss="modal" style="top: 45px;">&times;</a>
                <span class="modal-title" style="font-size: 26px;float:left;">Receipt</span>
                <a href="#" class="prnintpage" onclick="window.print(); return false;" style="position: absolute;right: 100px;"><i class="fa fa-print fa-2x modal-icon-box" aria-hidden="true" style="color:#ccc;"></i></a>
                <a href="#" name="mile_submit" class="download_pdf" wallet_id="{{$wallet->id}}" style="position: absolute;right: 47px;"><i class="fa fa-download fa-2x modal-icon-box" aria-hidden="true" style="color:#ccc;"></i></a>
            </div>
            <div>
                <div id="invoicecont" class="invoice-box" style="max-width: initial;">
                    <div id="_editor"></div>
                    @include('user.walletinvoicepdf')
                </div>
            </div>
        </div>
    </form>
</div>
@endif