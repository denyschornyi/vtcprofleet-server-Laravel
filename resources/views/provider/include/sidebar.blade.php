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
        <img style="margin-top:40px; margin-bottom:40px;" src="{{ config('constants.site_logo', asset('logo-black.png')) }}" class="img-fluid" />

    </div><!-- logo-admin-area -->
    <div class="admin-nav-area">
        <ul class="list-unstyled" style="outline: none;" tabindex="0">
            <li>
                <a href="{{ route('provider.index') }}">
                    <span class="s-icon"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                    Dashboard<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('provider.earnings') }}">
                    <span class="s-icon"><i class="fa fa-edit" aria-hidden="true"></i></span>
                    @lang('provider.profile.partner_earnings')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('provider.documents.index') }}">
                    <span class="s-icon"><i class="fa fa-book" aria-hidden="true"></i></span>
                    @lang('provider.profile.manage_documents')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('provider.location.index') }}">
                    <span class="s-icon"><i class="fa fa-location-arrow" aria-hidden="true"></i></span>
                    @lang('provider.profile.update_location')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{route('provider.wallet.transation')}}">
                    <span class="s-icon"><i class="fa fa-money" aria-hidden="true"></i></span>
                    @lang('provider.profile.wallet_transaction')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            @if(config('constants.card')==1)
            <li>
                <a href="{{ route('provider.cards') }}">
                    <span class="s-icon"><i class="fa fa-credit-card" aria-hidden="true"></i></span>
                    @lang('provider.card.list')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            @endif    
            <li>
                <a href="{{ route('provider.transfer') }}">
                    <span class="s-icon"><i class="fa fa-arrow-right" aria-hidden="true"></i></span>
                    @lang('provider.profile.transfer')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            @if(config('constants.referral')==1)
            <li>
                <a href="{{ route('provider.referral') }}">
                    <span class="s-icon"><i class="fa fa-user-plus" aria-hidden="true"></i></span>
                    @lang('provider.profile.refer_friend')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ url('/provider/logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <span class="s-icon"><i class="fa fa-sign-out" aria-hidden="true"></i></span>
                    @lang('provider.profile.logout')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>

        </ul>

        <p class="copyright">{{ config('constants.site_copyright', '&copy; '.date('Y').' Appoets') }}</p>

    </div>
</div> 