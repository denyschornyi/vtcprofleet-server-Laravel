<div class="sidebar-admin col-lg-3 col-md-3 col-sm-12">
    <div class="logo-admin-area">
        <div class="cross-mobile"><i class="fa fa-times" aria-hidden="true"></i></div>
        <div class="mobile-welcome">
            <div class="msr-wrapc">
                <div class="msg-img">
                    <img src="{{ config('constants.site_logo', asset('logo-black.png')) }}" class="img-fluid rounded-circle" />
                </div>
                <div class="msg-welcome"><span>WELCOME</span><br></div>
            </div>
        </div>
        <img src="{{ config('constants.site_logo', asset('logo-black.png')) }}" class="img-fluid" />

    </div><!-- logo-admin-area -->
    <div class="admin-nav-area">
        <div class="bigbutton"><a href="{{ url('/makearide') }}" target="_blank">Make a ride<span></span></a></div>
        <ul class="list-unstyled" style="outline: none;" tabindex="0">
            <li>
                <a href="{{ url('dashboard') }}">
                    <span class="s-icon"><i class="fa fa-edit" aria-hidden="true"></i></span>
                    @lang('user.dashboard')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ url('trips') }}">
                    <span class="s-icon"><i class="fa fa-wheelchair" aria-hidden="true"></i></span>
                    @lang('user.my_trips')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ url('upcoming/trips') }}">
                    <span class="s-icon"><i class="fa fa-automobile" aria-hidden="true"></i></span>
                    @lang('user.upcoming_trips')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ url('payment') }}">
                    <span class="s-icon"><i class="fa fa-history" aria-hidden="true"></i></span>
                    Payment<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ url('change/password') }}">
                    <span class="s-icon"><i class="fa fa-user-md" aria-hidden="true"></i></span>
                    @lang('user.profile.change_password')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ url('/wallet') }}">
                    <span class="s-icon"><i class="fa fa-usd" aria-hidden="true"></i></span>
                    @lang('user.my_wallet')   {{currency(Auth::user()->wallet_balance)}}<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            @if(Auth::user()->user_type === "COMPANY")
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#user-list11">
                        <span class="s-icon"><i class="fa fa-user" aria-hidden="true"></i></span>
                        @lang('admin.include.users')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>

                    <ul id="user-list11" class="collapse">
                        <li><a href="{{ route('passenger.index') }}">@lang('admin.include.list_users')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('passenger.create') }}">@lang('admin.include.add_new_user')<i class="fa fa-chevron-right"></i></a></li>
                    </ul>
                </li>
            @endif
            @if(Auth::user()->user_type === "FLEET_COMPANY")
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#user-list11">
                        <span class="s-icon"><i class="fa fa-user" aria-hidden="true"></i></span>
                        @lang('admin.include.users')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>

                    <ul id="user-list11" class="collapse">
                        <li><a href="{{ route('user-passenger.index') }}">@lang('admin.include.list_users')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('user-passenger.create') }}">@lang('admin.include.add_new_user')<i class="fa fa-chevron-right"></i></a></li>
                    </ul>
                </li>
            @endif
            @if(config('constants.referral') == 1)
            <li>
                <a href="{{ url('/referral') }}">
                    <span class="s-icon"><i class="fa fa-empire" aria-hidden="true"></i></span>
                    @lang('user.referral')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ url('/logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <span class="s-icon"><i class="fa fa-sign-out" aria-hidden="true"></i></span>
                    @lang('user.profile.logout')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>

        </ul>

        <p class="copyright">{{ config('constants.site_copyright', '&copy; '.date('Y').' Appoets') }}</p>

    </div>
</div>
