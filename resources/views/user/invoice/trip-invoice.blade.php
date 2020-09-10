@if(empty($trip) || empty($admin))
@else
<div id="view-invoice{{$trip->id}}" class="modal" style="overflow: auto;">
    <form method="post" class="" action="{{url('invoice-trip-pdf')}}" style="margin:0" id="formDownloadPDF{{$trip->id}}">
        {{ csrf_field() }}
        <input type="hidden" name="trip_id" value="{{$trip->id}}" />

        <div class="modal-content text-center" style="width: 880px;">
            <div class="modal-header" style="height: 70px;">
                <a type="button" class="close" data-dismiss="modal" style="top: 45px;-webkit-appearance: none;">&times;</a>
                <span class="modal-title" style="font-size: 26px;float:left;">Invoice</span>
                <a href="#" class="prnintpage" onclick="window.print(); return false;" style="position: absolute;right: 100px;"><i class="fa fa-print fa-2x modal-icon-box" aria-hidden="true" style="color:#ccc;"></i></a>
                <a href="#" name="mile_submit" class="download_pdf" trip_id="{{$trip->id}}" style="position: absolute;right: 47px;"><i class="fa fa-download fa-2x modal-icon-box" aria-hidden="true" style="color:#ccc;"></i></a>
            </div>
            <div>
                <div id="invoicecont" class="invoice-box" style="max-width: initial;">
                    <div id="_editor"></div>
                    @include('user.invoicepdf', $trip)
                </div>
            </div>
        </div>
    </form>
</div>
@endif
