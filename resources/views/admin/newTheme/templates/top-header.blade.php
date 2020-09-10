<div class="row message-tbar">
    <div class="col-lg-9 col-md-8 msg-lft">
        <div class="mobile-menu"><i class="fa fa-bars" aria-hidden="true"></i>
            <img src="{{ config('constants.site_logo', asset('logo-black.png')) }}" class="img-fluid">
        </div>
        <div class="msg-date">@php date_default_timezone_set('Europe/paris'); $date = date('d/m/Y - H:i', time()); echo $date; @endphp</div>
        <div class="msg-icons">
            <div class="msg-icon-img">
                <i class="fa fa-envelope-o" aria-hidden="true"></i>
                <span>0</span>
                <div class="msg-menu">
                    <div class="msg-menuww">
                        <i class="fa fa-caret-up"></i>
                        <h4>@lang('admin.dashboard.Msg')</h4>
                        <div class="unread-scroll" style="overflow: hidden; outline: none;" tabindex="1">
                            <ul>
                                <li></li>
                            </ul>
                        </div>
                    </div>
                    <div id="ascrail2001" class="nicescroll-rails nicescroll-rails-vr"
                         style="width: 7px; z-index: 100; position: absolute; top: 0px; left: -7px; height: 0px; opacity: 0.6; touch-action: none; cursor: default; display: none;">
                        <div class="nicescroll-cursors"
                             style="position: relative; top: 0px; float: right; width: 5px; height: 0px; background-color: rgb(47, 46, 46); border: 1px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 5px; touch-action: none;"></div>
                    </div>
                    <div id="ascrail2001-hr" class="nicescroll-rails nicescroll-rails-hr"
                         style="height: 7px; z-index: 100; top: -7px; left: 0px; position: absolute; opacity: 0.6; cursor: default; display: none;">
                        <div class="nicescroll-cursors"
                             style="position: absolute; top: 0px; height: 5px; width: 0px; background-color: rgb(47, 46, 46); border: 1px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 5px;"></div>
                    </div>
                </div>
            </div>
            @php
                $ride_ids = App\UserRequests::where('fleet_id', 0)->where('status', 'COMPLETED')->pluck('id')->toArray();
                
                $revenue = App\UserRequestPayment::whereIn('request_id', $ride_ids)->sum('total') + App\UserRequestPayment::whereIn('request_id', $ride_ids)->sum('tips');
            @endphp
            <div class="msg-icon-price" data-toggle="tooltip" data-placement="bottom" title=""
                 data-original-title="Total Earnings">{{currency($revenue)}}</div>
        </div>
    </div>

    <div class="mobile-mils">
        Total: {{currency(App\UserRequestPayment::where('fleet_id',0)->sum('cash') + App\UserRequestPayment::where('fleet_id',0)->sum('card') + App\UserRequestPayment::where('fleet_id',0)->sum('wallet'))}}</div>
    <div class="col-lg-3 col-md-4 msg-rt">
        <div class="msr-wrapc">
            <div class="msg-welcome"><span>@lang('admin.dashboard.Wlc')</span><br></div>
            <div class="msg-img">
                <img src="{{ Auth::guard('admin')->user()->picture ? asset('storage/'.Auth::guard('admin')->user()->picture) : asset('asset/img/provider.jpg') }}"
                     class="img-fluid rounded-circle">
                <i class="fa fa-caret-down"></i></div>
            <div class="logout-menu">
                <div class="logout-menuwrap">
                    <i class="fa fa-caret-up"></i>
                    <ul>
                        @can('account-settings')
                            <li><a href="{{route('admin.profile')}}"><i class="fa fa-user"
                                                                        aria-hidden="true"></i>@lang('admin.include.profile')
                                </a></li>
                        @endcan
                        @can('change-password')
                            <li><a href="{{route('admin.password')}}"><i class="fa fa-key"
                                                                         aria-hidden="true"></i>@lang('admin.account.change_password')
                                </a></li>
                        @endcan
                            <form id="logout-form" action="{{ url('/admin/logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        <div class="dropdown-divider"></div>
                        <li><a href="{{ url('/admin/logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                        class="fa fa-sign-out" aria-hidden="true"></i>@lang('admin.include.sign_out')
                            </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
