<div class="navbar-container main-menu-content" data-menu="menu-container">

    <ul class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation">
        <li @if(Request::is('admin/dispatcher')) class="active" @else class="nav-item" @endif >
            <a href="{{ route('admin.dispatcher.index') }}">
                <i class="fa fa-location-arrow" aria-hidden="true"></i>
                <span class="menu-title" data-i18n="Email">@lang('admin.include.dispatcher_panel')</span>
            </a>
        </li>

        <li @if(Request::is('admin/dispatcher/requests') || Request::is('admin/requests/*')) class="active" @else class="navigation-item" @endif >
            <a href="{{ route('admin.requests.dispatcher') }}">
                <i class="fa fa-car" aria-hidden="true"></i>
                <span class="menu-title" data-i18n="Email">@lang('admin.include.ride_history')</span>
            </a>
        </li>
        <li @if(Request::is('admin/profile')) class="active" @else class="navigation-item" @endif>
            <a href="{{route('admin.profile')}}"><i class="fa fa-user-secret"></i><span
                        class="menu-item" data-i18n="Shop">Account Settings</span>
            </a>
        </li>
        <li @if(Request::is('admin/password')) class="active" @else class="navigation-item" @endif>
            <a href="{{route('admin.password')}}"><i class="fa fa-key"></i><span
                        class="menu-item" data-i18n="Wish List">Change Password</span>
            </a>
        </li>
        <li>
            <a href="{{ url('/admin/logout') }}" onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
                <i class="feather icon-power"></i>
                <span  class="menu-item" data-i18n="Checkout">@lang('admin.include.logout')</span>
            </a>
            <form id="logout-form" action="{{ url('/admin/logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        </li>
    </ul>

</div>
