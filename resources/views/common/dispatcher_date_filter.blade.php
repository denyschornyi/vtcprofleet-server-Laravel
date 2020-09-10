<div class="datemenu pl-2" style="padding:15px;padding-bottom: 0px;">
    <span>
        <a style="cursor:pointer" id="tday" class="showdate">@lang('admin.statement_date.Today')</a>
        <a style="cursor:pointer" id="yday" class="showdate">@lang('admin.statement_date.Yesterday')</a>
        <a style="cursor:pointer" id="cweek" class="showdate">@lang('admin.statement_date.Current_Week')</a>
        <a style="cursor:pointer" id="pweek" class="showdate">@lang('admin.statement_date.Previous_Week')</a>
        <a style="cursor:pointer" id="cmonth" class="showdate">@lang('admin.statement_date.Current_Month')</a>
        <a style="cursor:pointer" id="pmonth" class="showdate">@lang('admin.statement_date.Previous_Month')</a>
        <a style="cursor:pointer" id="cyear" class="showdate">@lang('admin.statement_date.Current_Year')</a>
        <a style="cursor:pointer" id="pyear" class="showdate">@lang('admin.statement_date.Previous_Year')</a>
    </span>
</div>
<div class="clearfix">
    <form class="form-horizontal" action="{{route($action)}}" method="GET" enctype="multipart/form-data" role="form">
        <input class="form-control" type="hidden" name="date_filter" id="date_filter" required>
        <div class="row pl-1">
            <div class="col-12">
                <div class="form-group row">
                    <div class=" col-md-4 mt-1">
                        <div class="col-md-5 col-sm-5 float-left" style="margin-top: 9px;">
                            <span>Date From</span>
                        </div>
                        <div class="col-md-7 col-sm-5 float-left">
                            <input class="form-control" type="date" name="from_date" id="from_date" required placeholder="From Date">
                        </div>
                    </div>
                    <div class="col-md-4 mt-1">
                        <div class="col-md-5 float-left" style="margin-top: 9px;">
                            <span>Date From</span>
                        </div>
                        <div class="col-md-7 float-left">
                            <input class="form-control" type="date" required name="to_date" id="to_date" placeholder="To Date">
                        </div>
                    </div>
                    <div class="col-md-2 mt-1" style="margin-left: 12px;">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
