<div class="datemenu" style="padding:15px">
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
<div class="clearfix" style="margin-top: 15px; margin-bottom: 30px;">

    <form class="form-horizontal" action="{{route($action)}}" method="GET" enctype="multipart/form-data" role="form">
        <input class="form-control" type="hidden" name="date_filter" id="date_filter" required>

        <div class="form-group row col-md-5">
            <label for="name" class="col-xs-4 col-form-label">Date From</label>
            <div class="col-xs-8">
                <input class="form-control" type="date" name="from_date" id="from_date" required placeholder="From Date">
            </div>
        </div>

        <div class="form-group row col-md-5">
            <label for="email" class="col-xs-4 col-form-label">Date To</label>
            <div class="col-xs-8">
                <input class="form-control" type="date" required name="to_date" id="to_date" placeholder="To Date">
            </div>
        </div>
        <div class="form-group row col-md-2">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>
