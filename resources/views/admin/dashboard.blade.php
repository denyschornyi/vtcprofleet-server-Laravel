@extends('admin.layout.base')

@section('title', 'Dashboard ')

@section('content')
    @can('dashboard-menus')
        <div class="row db-container cont-top-main">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="db-box-wrap">
                    <div class="db-boxh">
                        <div class="db-hlft">@lang('admin.dashboard.Rides')</div>
                        <div class="db-hrt"><a
                                    href="{{ route('admin.requests.index') }}">@lang('admin.dashboard.Viewall')</a>
                        </div>
                    </div>
                    <div class="chart-wrap">
                        <div id="chartContainer" style="height: 230px; max-width: 920px; margin: 0px auto;"></div>
                        <div class="total-pro">{{$rides_of_users}}<span>@lang('admin.dashboard.Rides')</span></div>
                    </div>
                    <div class="container">
                        <div class="row chart-footer">
                            <div class="col-md-6 foot-c-box"><span
                                        class="fc-scolor"></span>{{$completed_ride}}
                                <span class="fctxt">@lang('admin.dashboard.completed_ride')</span></div>
                            <div class="col-md-6 foot-c-box"><span
                                        class="fc-fcolor"></span>{{$cancel_rides}}<span
                                        class="fctxt">@lang('admin.dashboard.cancelled_ride')</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 clients-chart">
                <div class="db-box-wrap">
                    <div class="db-boxh">
                        <div class="db-hlft">@lang('admin.dashboard.Utilisateur')</div>
                        <div class="db-hrt"><a style="display:none" href="#">@lang('admin.dashboard.Viewall')</a></div>
                    </div>
                    <div class="chart-wrap">
                        <div id="chartContainer2" style="height: 230px; max-width: 920px; margin: 0px auto;">
                        </div>
                        <div class="total-pro">{{$provider + $user_count}}
                            <span>@lang('admin.dashboard.Utilisateur')</span></div>
                    </div>
                    <div class="container">
                        <div class="row chart-footer">
                            <div class="col-md-6 foot-c-box"><span class="fc-fcolor"></span>{{$provider}}<span
                                        class="fctxt">@lang('admin.dashboard.Providerss')</span></div>
                            <div class="col-md-6 foot-c-box"><span class="fc-scolor"></span>{{$user_count}}<span
                                        class="fctxt">@lang('admin.dashboard.Userss')</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-sm-12">
                <div class="admin-dash-rt blue-shadow">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8 col-8 dash-lftb">
                                <span>@lang('admin.dashboard.scheduled')</span><a
                                        href="{{ route('admin.requests.scheduled') }}">@lang('admin.dashboard.Viewall')</a>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-4 dash-rttb">{{$scheduled_rides}}</div>
                        </div>
                    </div>
                </div>
                <div class="admin-dash-rt white-admin-box admin-mid-box">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8 col-8 dash-lftb">
                                <span>@lang('admin.dashboard.Paymentrequest')</span>
                                <a href="{{ route('admin.providertransfer') }}">@lang('admin.dashboard.Providerss')</a>&nbsp; &nbsp; &nbsp;
                                <a href="{{ route('admin.fleettransfer') }}">@lang('admin.dashboard.Fleetss')</a>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-4 dash-rttb">{{$pendingReqCount}}</div>
                        </div>
                    </div>
                </div>
                <div class="admin-dash-rt white-admin-box">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8 col-8 dash-lftb">
                                <span>@lang('admin.dashboard.UnpaidInvoices')</span><a
                                        href="{{ route('admin.requests.index') }}">@lang('admin.dashboard.Viewall')</a></div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-4 dash-rttb">{{ $unpaid_invoices }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row cont-bot-main">
            <div class="col-md-4 col-sm-4">
                <div class="monthly-rev">
                    <div class="month-heading">@lang('admin.dashboard.Commissioonth')</div>
                    <div class="month-rps">{{currency($commission)}}</div>
                    <div class="form-group">
                        <select class="form-control month-drop">
                            <option selected value="0">@lang('admin.dashboard.Allss')</option>
                            <option value="01">@lang('admin.dashboard.Janay')</option>
                            <option value="02">@lang('admin.dashboard.Feary')</option>
                            <option value="03">@lang('admin.dashboard.Mch')</option>
                            <option value="04">@lang('admin.dashboard.Apl')</option>
                            <option value="05">@lang('admin.dashboard.Mayhh')</option>
                            <option value="06">@lang('admin.dashboard.Jue')</option>
                            <option value="07">@lang('admin.dashboard.Jly')</option>
                            <option value="08">@lang('admin.dashboard.Aujt')</option>
                            <option value="09">@lang('admin.dashboard.Seber')</option>
                            <option value="10">@lang('admin.dashboard.Ocer')</option>
                            <option value="11">@lang('admin.dashboard.Nover')</option>
                            <option value="12">@lang('admin.dashboard.Decer')</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-sm-8">
                <div class="container dash-total-wrap">
                    <div class="dash-centhead">@lang('admin.dashboard.Accounting')</div>
                    <div class="row">
                        <div class="dascont-comn dascont-lft col-md-6">
                            <div class="dash-shead">@lang('admin.dashboard.Revenue')</div>
                            <div class="dash-sprice">{{currency($revenue)}}</div>
                        </div>
                        <div class="dascont-comn dascont-rt col-md-6">
                            <div class="dash-shead"
                                 style="color:red !important">@lang('admin.dashboard.Companiesdebit')</div>
                            <div class="dash-sprice" style="color:red !important">@if($companies_debit == 0)
                                    -@endif{{currency($companies_debit)}}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row db-container cont-bot-main">
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="db-box-wrap db-box-wrapadmin" tabindex="2" style="overflow: hidden; outline: none;">
                    <div class="db-boxh">
                        <div class="db-hlft">@lang('admin.dashboard.Recent_Rides')</div>
                        <div class="db-hrt"><a
                                    href="{{ route('admin.requests.index') }}">@lang('admin.dashboard.Viewall')</a>
                        </div>
                    </div>
                    <div class="dashbord-pro">

                        <div class="table-responsive">
                            <table class="table table-new projectspage indexpage" data-pagination="true"
                                   data-page-size="5">
                                <tbody id="projects-tbl">
                                    @if(count($rides) !== '0')
                                        @foreach($rides as $index => $ride)
                                            <tr>
                                                <th scope="row">{{$index + 1}}</th>
                                                <td>
                                                    @if($ride->user->company_name !='')
                                                        {{$ride->user->company_name}}
                                                    @else
                                                        {{$ride->user->first_name}}  {{$ride->user->last_name}}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($ride->status !== "CANCELLED")
                                                        <a class="text-primary"
                                                           href="{{route('admin.requests.show',$ride->id)}}"><span
                                                                    class="underline">@lang('admin.dashboard.View_Ride_Details')</span></a>
                                                    @else
                                                        <span>@lang('admin.dashboard.No_Details_Found') </span>
                                                    @endif
                                                </td>
                                                <td>
                                            <span class="text-muted">
                                                @if($ride->status !== "SCHEDULED")
                                                    {{appDate($ride->created_at)}}
                                                @else
                                                    {{appDate($ride->schedule_at)}}
                                                @endif
                                            </span>
                                                </td>
                                                <td>
                                                    @if($ride->status == "COMPLETED")
                                                        <span class="tag tag-success">{{$ride->status}}</span>
                                                    @elseif($ride->status == "CANCELLED")
                                                        <span class="tag tag-danger">{{$ride->status}}</span>
                                                    @else
                                                        <span class="tag tag-info">{{$ride->status}}</span>
                                                    @endif
                                                </td>
                                                @php if($index === 10) break; @endphp
                                            </tr>
                                        @endforeach
                                        @else
                                            <span>There is no rides.</span>
                                    @endif
                                </tbody>
                            </table>

                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 col-sm-12">
                <div class="db-box-wrap db-box-wrapadmin" tabindex="2" style="overflow: hidden; outline: none;">
                    <div class="db-boxh">
                        <div class="db-hlft">@lang('admin.dashboard.Walletsummary')</div>
                        <div class="db-hrt"></div>
                    </div>
                    <div class="dashbord-pro">

                        <div class="table-responsive">
                            @can('wallet-summary')
                                <table class="table table-new projectspage indexpage" data-pagination="true"
                                       data-page-size="5">
                                    <tbody id="projects-tbl">
                                    @php($total=$wallet['admin'])
                                    {{-- <tr>
                                        <th scope="row">@lang('admin.dashboard.Admincredit')</th>
                                        <td class="text-success">{{currency($wallet['admin'])}}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.Companiescredit')</th>
                                        <td class="text-success">{{currency($companies_credit)}}</td>
                                    </tr> --}}
                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.user_pro')</th>
                                        <td class="text-success" style="color:red !important">@if($companies_debit == 0)
                                                -@endif{{currency($companies_debit)}}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.Procred')</th>
                                        @if($wallet['provider_credit'])
                                            @php($total=$total-$wallet['provider_credit'])
                                            <td class="text-success">{{currency($wallet['provider_credit'])}}</td>
                                        @else
                                            <td class="text-success">{{currency()}}</td>
                                        @endif
                                    </tr>

                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.Prodebit')</th>
                                        @if($wallet['provider_debit'])

                                            <td class="text-danger">{{currency($wallet['provider_debit'])}}</td>
                                        @else
                                            <td class="text-danger">{{currency()}}</td>
                                        @endif
                                    </tr>

                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.Fleetcre')</th>
                                        <td class="text-success">{{currency($wallet['fleet_credit'])}}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.Fleetdt')</th>
                                        <td class="text-danger">{{currency($wallet['fleet_debit'])}}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.Comm')</th>
                                        <td class="text-success">{{currency($wallet['admin_commission'])}}</td>
                                    </tr>
                                    {{-- <tr>
                                        <th scope="row">@lang('admin.dashboard.Peakcomm')</th>
                                        <td class="text-success">{{currency($wallet['peak_commission'])}}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.Waitingcomm')</th>
                                        <td class="text-success">{{currency($wallet['waiting_commission'])}}</td>
                                    </tr> --}}
                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.Disct')</th>
                                        <td class="text-danger">{{currency($wallet['admin_discount'])}}</td>
                                    </tr>
                                    <tr>
                                        @php($total=$total-($wallet['admin_tax']))
                                        <th scope="row">@lang('admin.dashboard.Taxunt')</th>
                                        <td class="text-danger">{{currency($wallet['admin_tax'])}}</td>
                                    </tr>

                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.Tpps')</th>
                                        <td class="text-success">{{currency($wallet['tips'])}}</td>
                                    </tr>

                                    {{-- <tr>
                                        <th scope="row">@lang('admin.dashboard.Refeals')</th>
                                        <td class="text-danger">{{currency($wallet['admin_referral'])}}</td>
                                    </tr>

                                    <tr>
                                        <th scope="row">@lang('admin.dashboard.Dispes')</th>
                                        <td class="text-danger">{{currency($wallet['admin_dispute'])}}</td>
                                    </tr> --}}
                                    </tbody>
                                </table>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@section('scripts')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load("current", {
            packages: ["corechart"]
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Task', 'Rides per Day'],
                ["@lang('admin.dashboard.cancelled_ride')", {{$cancel_rides}}],
                ["@lang('admin.dashboard.completed_ride')", {{ $completed_ride }}]
            ]);
            var options = {
                title: '',
                pieHole: 0.8,
                pieSliceText: 'none',
                legend: {
                    position: 'none'
                },
                chartArea: {
                    left: 10,
                    top: 10,
                    right: 10,
                    bottom: 10,
                    width: "100%",
                    height: "100%"
                },
                tooltip: {
                    text: 'value',
                    textStyle: {
                        fontSize: 12
                    },
                },
                slices: {
                    0: {
                        color: '#b531ba'
                    },
                    1: {
                        color: '#7eb735'
                    }
                },
            };

            var chartb = new google.visualization.PieChart(document.getElementById('chartContainer'));
            chartb.draw(data, options);
        }

        google.charts.load("current", {
            packages: ["corechart"]
        });
        google.charts.setOnLoadCallback(drawChartb);

        function drawChartb() {
            var datab = google.visualization.arrayToDataTable([
                ['Task', 'Hours per Day'],
                ['Users', {{$user_count}}],
                ['Providers', {{$provider}}]
            ]);
            var optionsb = {
                title: '',
                pieHole: 0.8,
                pieSliceText: 'none',
                legend: {
                    position: 'none'
                },
                chartArea: {
                    left: 10,
                    top: 10,
                    right: 10,
                    bottom: 10,
                    width: "100%",
                    height: "100%"
                },
                tooltip: {
                    text: 'value',
                    textStyle: {
                        fontSize: 12
                    },
                },
                slices: {
                    0: {
                        color: '#7eb735'
                    },
                    1: {
                        color: '#b531ba'
                    }
                },
            };

            var chartb = new google.visualization.PieChart(document.getElementById('chartContainer2'));
            chartb.draw(datab, optionsb);
        }

        $(".month-drop").change(function () {
            var Month = $(this).val();
            var month_url = "{{url('admin/revenue/monthly')}}";
            $.ajax({
                type: "POST",
                url: month_url,
                data: {
                    _token: '{{ csrf_token() }}',
                    month: Month
                },
                error: function (e) {
                    $('.month-rps').html('<p>' + $.ajaxError + '</p>');
                },
                success: function (html) {
                    $('.month-rps').html(html);
                }
            });

        });

    </script>
@endsection