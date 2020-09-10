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
        <div class="bigbutton"><a href="{{ route('fleet.dispatcher.index') }}" target="_blank">@lang('admin.include.dispatcher_panel')<span></span></a></div>
        <ul class="list-unstyled" style="outline: none;" tabindex="0">
            <li class="menu-title">@lang('admin.include.fleet_dashboard')</li>
            <li>
                <a href="{{ route('fleet.dashboard') }}">
                    <span class="s-icon"><i class="fa fa-tachometer" aria-hidden="true"></i></span>
                    @lang('admin.include.dashboard')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('fleet.dispatcher.index') }}">
                    <span class="s-icon"><i class="fa fa-transgender-alt" aria-hidden="true"></i></span>
                    @lang('admin.include.dispatcher_panel')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
           {{-- <li class="">
                <a href="{{ route('fleet.godseye') }}">
                    <span class="s-icon"><i class="fa fa-eye"></i></span>
                    God's Eye<i class="fa fa-chevron-right"></i>
                </a>
            </li>--}}

            <li class="menu-title">@lang('admin.include.members')</li>
            <li class="">
                <a href="#" data-toggle="collapse" data-target="#member-list11">
                    <span class="s-icon"><i class="ti-car" aria-hidden="true"></i></span>
                    @lang('admin.include.members')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="member-list11" class="collapse">
                    <li><a href="{{ route('fleet.provider.index') }}">@lang('admin.include.list_providers')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.provider.create') }}">@lang('admin.include.add_new_provider')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>

            <li class="">
                <a href="#" data-toggle="collapse" data-target="#user-list11">
                    <span class="s-icon"><i class="fa fa-user" aria-hidden="true"></i></span>
                    @lang('admin.include.users')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="user-list11" class="collapse">
                    <li><a href="{{ route('fleet.user.index') }}">@lang('admin.include.list_users')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.user.create') }}">@lang('admin.include.add_new_user')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>
            <li class="">
                <a href="#" data-toggle="collapse" data-target="#user-pro-list11">
                    <span class="s-icon"><i class="fa fa-user" aria-hidden="true"></i></span>
                    @lang('admin.include.user_pro')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="user-pro-list11" class="collapse">
                    <li><a href="{{ route('fleet.user-pro.index') }}">@lang('admin.include.list_users')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.user-pro.create') }}">@lang('admin.include.add_new_user')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.pro_payment') }}">@lang('admin.include.user_payment')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>
            {{-- <li class="">
                <a href="#" data-toggle="collapse" data-target="#dispatcher-list11">
                    <span class="s-icon"><i class="fa fa-share-square-o" aria-hidden="true"></i></span>
                    @lang('admin.include.dispatcher')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="dispatcher-list11" class="collapse">
                   <li><a href="{{ route('fleet.dispatch-manager.index') }}">@lang('admin.include.list_dispatcher')<i class="fa fa-chevron-right"></i></a></li>
                   <li><a href="{{ route('fleet.dispatch-manager.create') }}">@lang('admin.include.add_new_dispatcher')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li> --}}

            {{--<li class="">
                <a href="#" data-toggle="collapse" data-target="#account-manager-list11">
                    <span class="s-icon"><img src="{{asset('asset/img/account_black.png')}}"></span>
                    @lang('admin.include.account_manager')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="account-manager-list11" class="collapse">
                   <li><a href="{{ route('fleet.account-manager.index') }}">@lang('admin.include.list_account_managers')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.account-manager.create') }}">@lang('admin.include.add_new_account_manager')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>--}}

            <li class="menu-title">@lang('admin.include.accounts')</li>
            {{-- <li class="">
                <a href="#" data-toggle="collapse" data-target="#b2b-list11">
                    <span class="s-icon"><i class="fa fa-bank" aria-hidden="true"></i> </span>
                    @lang('admin.include.b2b')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="b2b-list11" class="collapse">
                    <li><a href="{{ route('fleet.b2b') }}">@lang('admin.include.b2b')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.poolPayment') }}">@lang('admin.custom.Payment')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li> --}}
            <li>
                <a href="{{ route('fleet.b2b') }}">
                    <span class="s-icon"><i class="fa fa-bank" aria-hidden="true"></i></span>
                    @lang('admin.include.b2b')<i class="fa fa-chevron-right"></i>
                </a>
            </li>

            <li class="">
                <a href="#" data-toggle="collapse" data-target="#statements-list11">
                    <span class="s-icon"><i class="fa fa-book" aria-hidden="true"></i></span>
                    @lang('admin.include.statements')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="statements-list11" class="collapse">
                    <li><a href="{{ route('fleet.ride.statement') }}">@lang('admin.include.overall_ride_statments')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.ride.statement.provider') }}">@lang('admin.include.provider_statement')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.ride.statement.user') }}">@lang('admin.include.user_statement')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>
            <li class="">
                <a href="#" data-toggle="collapse" data-target="#statements-list17">
                    <span class="s-icon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span>
                    @lang('admin.include.transaction')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="statements-list17" class="collapse">
                    <li><a href="{{ route('fleet.providertransfer') }}">@lang('admin.include.provider_request')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.fleettransfer') }}">@lang('admin.include.fleet_request')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.transactions') }}">@lang('admin.include.all_transaction')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>
            {{-- <li>
                <a href="{{ route('fleet.providertransfer') }}">
                    <span class="s-icon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span>
                    @lang('admin.include.provider_request')<i class="fa fa-chevron-right"></i>
                </a>
            </li> --}}
            <li class="">
                <a href="#" data-toggle="collapse" data-target="#provider-req-list11">
                    <span class="s-icon"><i class="fa fa-money" aria-hidden="true"></i></span>
                    @lang('admin.include.payment_request')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="provider-req-list11" class="collapse">
                    {{-- <li><a href="{{ route('fleet.payment_request') }}">@lang('admin.include.payment_request')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.payment.transactions') }}">@lang('admin.include.all_transaction')<i class="fa fa-chevron-right"></i></a></li> --}}
                    <li><a href="{{ route('fleet.payment_provider') }}">@lang('admin.include.provider_payment_history')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.payment_fleet') }}">@lang('admin.include.fleet_payment_history')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.payment_demand') }}">@lang('admin.include.demand')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>

            <li class="menu-title">@lang('admin.include.details')</li>
            <li>
                <a href="{{ route('fleet.map.index') }}">
                    <span class="s-icon"><i class="ti-map-alt" aria-hidden="true"></i></span>
                    @lang('admin.include.map')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li class="">
                <a href="#" data-toggle="collapse" data-target="#rating-reviews-list11">
                    <span class="s-icon"><i class="ti-view-grid" aria-hidden="true"></i></span>
                    @lang('admin.include.ratings') &amp; @lang('admin.include.reviews')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="rating-reviews-list11" class="collapse">
                    <li><a href="{{ route('fleet.provider.review') }}">@lang('admin.include.provider_ratings')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>

            {{--   <li class="menu-title">@lang('admin.include.rides')</li>
               <li>
                   <a href="{{ route('admin.requests.index') }}">
                       <span class="s-icon"><i class="fa fa-history" aria-hidden="true"></i></span>
                       @lang('admin.include.ride_history')<i class="fa fa-chevron-right"></i>
                   </a>
               </li>
               <li>
                   <a href="{{ route('admin.requests.scheduled') }}">
                       <span class="s-icon"><i class="ti-palette"></i></span>
                       @lang('admin.include.scheduled_rides')<i class="fa fa-chevron-right"></i>
                   </a>
               </li>--}}

            <li class="menu-title">@lang('admin.include.requests')</li>
            <li>
                <a href="{{ route('fleet.requests.index') }}">
                    <span class="s-icon"><i class="ti-infinite" aria-hidden="true"></i></span>
                    @lang('admin.include.request_history')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('fleet.requests.scheduled') }}">
                    <span class="s-icon"><i class="ti-palette" aria-hidden="true"></i></span>
                    @lang('admin.include.scheduled_rides')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('fleet.get_pool',1) }}">
                    <span class="s-icon"><i class="fa fa-reddit-alien"></i></span>
                    @lang('admin.custom.public_pool')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('fleet.get_private_pool') }}">
                    <span class="s-icon"><i class="fa fa-reddit-square"></i></span>
                    @lang('admin.custom.private_pool')<i class="fa fa-chevron-right"></i>
                </a>
            </li>

            <li class="menu-title">@lang('admin.include.general')</li>
            <li class="">
                <a href="#" data-toggle="collapse" data-target="#service-types-list11">
                    <span class="s-icon"><img src="{{asset('asset/img/support-service_black.png')}}"></span>
                    @lang('admin.include.service_types')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="service-types-list11" class="collapse">
                    <li><a href="{{ route('fleet.service.index') }}">@lang('admin.include.list_service_types')<i class="fa fa-chevron-right"></i></a></li>
{{--                    <li><a href="{{ route('fleet.service.create') }}">@lang('admin.include.add_new_service_type')<i class="fa fa-chevron-right"></i></a></li>--}}
                    <li><a href="{{ route('fleet.peakhour.index') }}">@lang('admin.include.peakhour')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>
            <li class="">
                <a href="#" data-toggle="collapse" data-target="#poi-list">
                    <span class="s-icon"><i class="fa fa-book" aria-hidden="true"></i></span>
                    @lang('admin.include.poi')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="poi-list" class="collapse">
                    <li><a href="{{ route('fleet.poiCategory.index') }}">@lang('admin.include.poi_categories')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.pointInterest.index') }}">@lang('admin.include.points_of_interest')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.polygonShape.index') }}">@lang('admin.include.poi_shape')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>

            <li class="menu-title">@lang('admin.include.transaction')</li>
            <li>
                <a href="{{ route('fleet.wallet') }}">
                    <span class="s-icon"><i class="ti-money" aria-hidden="true"></i></span>
                    @lang('admin.include.wallet')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            @if(config('constants.card')==1)
                <li>
                    <a href="{{ route('fleet.cards') }}">
                        <span class="s-icon"><i class="ti-exchange-vertical" aria-hidden="true"></i></span>
                        @lang('admin.include.debit_card')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endif
            <li>
                <a href="{{ route('fleet.transfer') }}">
                    <span class="s-icon"><i class="ti-exchange-vertical" aria-hidden="true"></i></span>
                    @lang('admin.include.transfer')<i class="fa fa-chevron-right"></i>
                </a>
            </li>

            <li class="menu-title">@lang('admin.include.payment_details')</li>
            <li>
                <a href="{{ route('fleet.payment') }}">
                    <span class="s-icon"><i class="fa fa-money" aria-hidden="true"></i></span>
                    @lang('admin.include.payment_history')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('fleet.settings.payment') }}">
                    <span class="s-icon"><img src="{{asset('asset/img/credit-card_black.png')}}"></span>
                    @lang('admin.include.payment_settings')<i class="fa fa-chevron-right"></i>
                </a>
            </li>

            <li class="menu-title">@lang('admin.include.others')</li>
            <li>
                <a href="{{ route('fleet.push') }}" class="waves-effect waves-light">
                    <span class="s-icon"><img src="{{asset('asset/img/push-icon_black.png')}}"></span>
                    @lang('admin.include.custom_push')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li class="">
                <a href="#" data-toggle="collapse" data-target="#notification-list11">
                    <span class="s-icon"><i class="fa fa-user" aria-hidden="true"></i></span>
                    @lang('admin.include.notify')<i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul id="notification-list11" class="collapse">
                    <li><a href="{{ route('fleet.notification.index') }}">@lang('admin.include.list_notifications')<i class="fa fa-chevron-right"></i></a></li>
                    <li><a href="{{ route('fleet.notification.create') }}">@lang('admin.include.add_new_notification')<i class="fa fa-chevron-right"></i></a></li>
                </ul>
            </li>


            <li class="menu-title">@lang('admin.include.account')</li>
            <li>
                <a href="{{ route('fleet.profile') }}">
                    <span class="s-icon"><i class="ti-user" aria-hidden="true"></i></span>
                    @lang('admin.include.account_settings')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('fleet.password') }}">
                    <span class="s-icon"><i class="ti-exchange-vertical" aria-hidden="true"></i></span>
                    @lang('admin.include.change_password')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            <li>
                <a href="{{ url('/logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <span class="s-icon"><i class="ti-power-off" aria-hidden="true"></i></span>
                    @lang('admin.include.logout')<i class="fa fa-chevron-right"></i>
                </a>
            </li>

            <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>

        </ul>

        <p class="copyright">{{ config('constants.site_copyright', '&copy; '.date('Y').' Appoets') }}</p>

    </div>
</div>
