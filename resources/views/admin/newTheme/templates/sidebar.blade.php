
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
        {{-- <img src="{{ config('constants.site_logo', asset('logo-black.png')) }}" class="img-fluid" /> --}}
        <img src="{{ asset('logo-black.png') }}" class="img-fluid" />

    </div><!-- logo-admin-area -->
    <div class="admin-nav-area">
        <div class="bigbutton"><a href="{{ route('admin.dispatcher.index') }}" target="_blank">@lang('admin.include.dispatcher_panel')<span></span></a></div>
        <ul class="list-unstyled" style="outline: none;" tabindex="0">
            @role('ADMIN|ACCOUNT')
            <li class="menu-title">@lang('admin.include.admin_dashboard')</li>
            <li class="">
                <a href="{{ route('admin.dashboard') }}">
                    <span><i class="fa fa-tachometer" aria-hidden="true"></i></span>
                    @lang('admin.include.dashboard')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            @endrole
            @can('heat-map')
                <li class="">
                    <a href="{{ route('admin.heatmap') }}">
                        <span class="s-icon"><i class="ti-map"></i></span>
                        @lang('admin.include.heat_map')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan
            @can('god-eye')
                <li class="">
                    <a href="{{ route('admin.godseye') }}">
                        <span class="s-icon"><i class="fa fa-eye"></i></span>
                        God's Eye<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan
            {{-- @can('dispatcher-panel')
             <li>
                 <a href="{{ route('admin.dispatcher.index') }}">
                     <span class="s-icon"><i class="fa fa-transgender-alt" aria-hidden="true"></i></span>
                     @lang('admin.include.dispatcher_panel')<i class="fa fa-chevron-right"></i>
                 </a>
             </li>
             @endcan--}}

            @can('dispute-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#dispute-list11">
                        <span><i class="fa fa-user-circle-o" aria-hidden="true"></i></span>
                        @lang('admin.include.dispute_panel')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="dispute-list11" class="collapse">
                        <li class=""><a href="{{ route('admin.dispute.index') }}">@lang('admin.include.dispute_type')<i class="fa fa-chevron-right"></i></a></li>
                        <li class=""><a href="{{ route('admin.userdisputes') }}">@lang('admin.include.dispute_request')<i class="fa fa-chevron-right"></i></a></li>
                    </ul>
                </li>
            @endcan
            @can('lost-item-list')
                <li>
                    <a href="{{ route('admin.lostitem.index') }}" class="waves-effect waves-light">
                        <span class="s-icon"><i class="ti-write"></i></span>
                        @lang('admin.include.lostitem')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan

            @role('ADMIN')
            <li class="menu-title">@lang('admin.include.rides')</li>
            @endrole

            @can('ride-history')
                <li>
                    <a href="{{ route('admin.requests.index') }}">
                        <span class="s-icon"><i class="fa fa-history" aria-hidden="true"></i></span>
                        @lang('admin.include.ride_history')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan

            @can('schedule-rides')
                <li>
                    <a href="{{ route('admin.requests.scheduled') }}">
                        <span class="s-icon"><i class="ti-palette"></i></span>
                        @lang('admin.include.scheduled_rides')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan
            @can('schedule-rides')
                <li>
                    <a href="{{ url('admin/get_pool/1') }}">
                        <span class="s-icon"><i class="fa fa-reddit-alien"></i></span>
                        @lang('admin.custom.public_pool')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.get_private_pool') }}">
                        <span class="s-icon"><i class="fa fa-reddit-square"></i></span>
                        @lang('admin.custom.private_pool')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan
            @can('ratings')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#ratings-list11">
                        <span class="s-icon"><i class="fa fa-star-half-o" aria-hidden="true"></i></span>
                        @lang('admin.include.ratings') &amp; @lang('admin.include.reviews')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="ratings-list11" class="collapse">
                        <li><a href="{{ route('admin.user.review') }}">@lang('admin.include.user_ratings')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('admin.provider.review') }}">@lang('admin.include.provider_ratings')<i class="fa fa-chevron-right"></i></a></li>
                    </ul>
                </li>
            @endcan

            @role('ADMIN')
            <li class="menu-title">@lang('admin.include.offer')</li>
            @endrole

            @can('promocodes-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#promocodes-list11">
                        <span class="s-icon"><i class="ti-layout-tab"></i></span>
                        @lang('admin.include.promocodes')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="promocodes-list11" class="collapse">
                        @can('promocodes-list')<li><a href="{{ route('admin.promocode.index') }}">@lang('admin.include.list_promocodes')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('promocodes-create')<li><a href="{{ route('admin.promocode.create') }}">@lang('admin.include.add_new_promocode')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan
            @can('custom-push')
                <li>
                    <a href="{{ route('admin.push') }}" class="waves-effect waves-light">
                        <span class="s-icon"><img src="{{asset('asset/img/push-icon_black.png')}}"></span>
                        @lang('admin.include.custom_push')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan
            @can('notification-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#notification-list11">
                        <span class="s-icon"><i class="fa fa-envelope" aria-hidden="true"></i></span>
                        @lang('admin.include.notify')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="notification-list11" class="collapse">
                        @can('notification-list')<li><a href="{{ route('admin.notification.index') }}">@lang('admin.include.list_notifications')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('notification-create')<li><a href="{{ route('admin.notification.create') }}">@lang('admin.include.add_new_notification')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan
            @can('advertisement-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#advertisement-list11">
                        <span class="s-icon"><i class="fa fa-handshake-o" aria-hidden="true"></i></span>
                        @lang('admin.include.advertisement')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="advertisement-list11" class="collapse">
                        @can('advertisement-list')<li><a href="{{ route('admin.advertisement.index') }}">@lang('admin.include.list_advertisement')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('advertisement-create')<li><a href="{{ route('admin.advertisement.create') }}">@lang('admin.include.add_advertisement')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan

            @role('ADMIN')
            <li class="menu-title">@lang('admin.include.accounts')</li>
            @endrole

            @can('statements')
                <li>
                    <a href="{{ route('admin.b2b') }}">
                        <span class="s-icon"><i class="fa fa-bank"></i> </span>
                        @lang('admin.include.b2b')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#statements-list11">
                        <span class="s-icon"><i class="fa fa-book" aria-hidden="true"></i></span>
                        @lang('admin.include.statements')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="statements-list11" class="collapse">

                        <li><a href="{{ route('admin.ride.statement') }}">@lang('admin.include.overall_ride_statments')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('admin.ride.statement.provider') }}">@lang('admin.include.provider_statement')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('admin.ride.statement.user') }}">@lang('admin.include.user_statement')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('admin.ride.statement.fleet') }}">@lang('admin.include.fleet_statement')<i class="fa fa-chevron-right"></i></a></li>
                    <!-- <li><a href="{{ route('admin.ride.statement.today') }}">@lang('admin.include.daily_statement')<i class="fa fa-chevron-right"></i></a></li>
					<li><a href="{{ route('admin.ride.statement.monthly') }}">@lang('admin.include.monthly_statement')<i class="fa fa-chevron-right"></i></a></li>
					<li><a href="{{ route('admin.ride.statement.yearly') }}">@lang('admin.include.yearly_statement')<i class="fa fa-chevron-right"></i></a></li> -->
                    </ul>
                </li>
            @endcan

            @can('settlements')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#settlements-list11">
                        <span class="s-icon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span>
                        @lang('admin.include.transaction')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="settlements-list11" class="collapse">
                        <li><a href="{{ route('admin.providertransfer') }}">@lang('admin.include.provider_request')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('admin.fleettransfer') }}">@lang('admin.include.fleet_request')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('admin.transactions') }}">@lang('admin.include.all_transaction')<i class="fa fa-chevron-right"></i></a></li>
                    </ul>
                </li>
                {{-- <li class="">
                    <a href="#" data-toggle="collapse" data-target="#provider-req-list11">
                        <span class="s-icon"><i class="fa fa-money" aria-hidden="true"></i></span>
                        @lang('admin.include.payment_request')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="provider-req-list11" class="collapse">
                        <li><a href="{{ route('admin.payment_request') }}">@lang('admin.include.payment_request')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('admin.payment.transactions') }}">@lang('admin.include.all_transaction')<i class="fa fa-chevron-right"></i></a></li>
                    </ul>
                </li> --}}
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#payment-history">
                        <span class="s-icon"><i class="fa fa-money" aria-hidden="true"></i></span>
                        @lang('admin.include.payment_request')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="payment-history" class="collapse">
                        <li><a href="{{ route('admin.payment_provider') }}">@lang('admin.include.provider_payment_history')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('admin.payment_fleet') }}">@lang('admin.include.fleet_payment_history')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('admin.payment_demand') }}">@lang('admin.include.demand')<i class="fa fa-chevron-right"></i></a></li>
                    </ul>
                </li>
            @endcan
            @can('payment-history')
                {{-- <li class="">
                    <a href="#" data-toggle="collapse" data-target="#payment-history">
                        <span class="s-icon"><i class="fa fa-paypal" aria-hidden="true"></i></span>
                        @lang('admin.include.payment_history')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="payment-history" class="collapse">
                        <li><a href="{{ route('admin.payment_provider') }}">@lang('admin.include.provider_payment_history')<i class="fa fa-chevron-right"></i></a></li>
                        <li><a href="{{ route('admin.payment_fleet') }}">@lang('admin.include.fleet_payment_history')<i class="fa fa-chevron-right"></i></a></li>
                    </ul>
                </li> --}}
                <li>
                    <a href="{{ route('admin.payment') }}">
                        <span class="s-icon"><i class="fa fa-paypal" aria-hidden="true"></i></span>
                        @lang('admin.include.payment_history')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan

            @role('ADMIN')
            <li class="menu-title">@lang('admin.include.members')</li> 
            @endrole

            @can('role-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#role-list11">
                        <span><i class="fa fa-user-circle-o" aria-hidden="true"></i></span>
                        @lang('admin.include.roles')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="role-list11" class="collapse">
                        @can('role-list')<li class=""><a href="{{ route('admin.role.index') }}">@lang('admin.include.role_types')<i class="fa fa-user-circle-o"></i></a></li>@endcan
                        @can('role-list')<li class=""><a href="{{ route('admin.sub-admins.index') }}">@lang('admin.include.sub_admins')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan

            @can('user-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#user-list11">
                        <span class="s-icon"><i class="fa fa-user-o" aria-hidden="true"></i></span>
                        @lang('admin.include.users')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="user-list11" class="collapse">
                        @can('user-list')<li><a href="{{ route('admin.user.index') }}">@lang('admin.include.list_users')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('user-create')<li><a href="{{ route('admin.user.create') }}">@lang('admin.include.add_new_user')<i class="fa fa-user-o"></i></a></li>@endcan
                    </ul>
                </li>
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#user-pro-list11">
                        <span class="s-icon"><i class="fa fa-user" aria-hidden="true"></i></span>
                        @lang('admin.include.user_pro')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="user-pro-list11" class="collapse">
                        @can('user-list')<li><a href="{{ route('admin.user-pro.index') }}">@lang('admin.include.list_users')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('user-create')<li><a href="{{ route('admin.user-pro.create') }}">@lang('admin.include.add_new_user')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        <li><a href="{{ route('admin.pro_payment') }}">@lang('admin.include.user_payment')<i class="fa fa-chevron-right"></i></a></li>
                    </ul>
                </li>
            @endcan

            @can('provider-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#provider-list11">
                        <span class="s-icon"><i class="fa fa-server" aria-hidden="true"></i></span>
                        @lang('admin.include.providers')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="provider-list11" class="collapse">
                        @can('provider-list')<li><a href="{{ route('admin.provider.index') }}">@lang('admin.include.list_providers')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('provider-create')<li><a href="{{ route('admin.provider.create') }}">@lang('admin.include.add_new_provider')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan
            @can('fleet-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#fleet-list11">
                        <span class="s-icon"><img src="{{asset('asset/img/boss_black.png')}}"></span>
                        @lang('admin.include.fleet_owner')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="fleet-list11" class="collapse">
                        @can('fleet-list')<li><a href="{{ route('admin.fleet.index') }}">@lang('admin.include.list_fleets')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('fleet-create')<li><a href="{{ route('admin.fleet.create') }}">@lang('admin.include.add_new_fleet_owner')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan

            @can('dispatcher-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#dispatcher-list11">
                        <span class="s-icon"><i class="fa fa-share-square-o" aria-hidden="true"></i></span>
                        @lang('admin.include.dispatcher')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="dispatcher-list11" class="collapse">
                        @can('dispatcher-list')<li><a href="{{ route('admin.dispatch-manager.index') }}">@lang('admin.include.list_dispatcher')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('dispatcher-create')<li><a href="{{ route('admin.dispatch-manager.create') }}">@lang('admin.include.add_new_dispatcher')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan

            @can('account-manager-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#account-manager-list11">
                        <span class="s-icon"><i class="fa fa-address-card-o" aria-hidden="true"></i></span>
                        @lang('admin.include.account_manager')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="account-manager-list11" class="collapse">
                        @can('account-manager-list')<li><a href="{{ route('admin.account-manager.index') }}">@lang('admin.include.list_account_managers')<i class="fa fa-address-card-o"></i></a></li>@endcan
                        @can('account-manager-create')<li><a href="{{ route('admin.account-manager.create') }}">@lang('admin.include.add_new_account_manager')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan
            @can('dispute-manager-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#dispute-manager-list11">
                        <span class="s-icon"><img src="{{asset('asset/img/account_black.png')}}"></span>
                        @lang('admin.include.dispute_manager')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="dispute-manager-list11" class="collapse">
                        @can('dispute-manager-list')<li><a href="{{ route('admin.dispute-manager.index') }}">@lang('admin.include.list_dispute_managers')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('dispute-manager-create')<li><a href="{{ route('admin.dispute-manager.create') }}">@lang('admin.include.add_new_dispute_manager')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan


            @role('ADMIN')
            <li class="menu-title">@lang('admin.include.general')</li>
            @endrole
            @can('cancel-reasons-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#cancel-reasons-list11">
                        <span class="s-icon"><i class="fa fa-user" aria-hidden="true"></i></span>
                        @lang('admin.include.reason')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="cancel-reasons-list11" class="collapse">
                        @can('cancel-reasons-list')<li><a href="{{ route('admin.reason.index') }}">@lang('admin.include.list_reasons')<i class="fa fa-window-close"></i></a></li>@endcan
                        @can('cancel-reasons-create')<li><a href="{{ route('admin.reason.create') }}">@lang('admin.include.add_new_reason')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan
            @can('documents-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#documents-list11">
                        <span class="s-icon"><i class="fa fa-file-text" aria-hidden="true"></i></span>
                        @lang('admin.include.documents')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="documents-list11" class="collapse">
                        @can('documents-list')<li><a href="{{ route('admin.document.index') }}">@lang('admin.include.list_documents')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('documents-create')<li><a href="{{ route('admin.document.create') }}">@lang('admin.include.add_new_document')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan
            @can('service-types-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#service-types-list11">
                        <span class="s-icon"><img src="{{asset('asset/img/support-service_black.png')}}"></span>
                        @lang('admin.include.service_types')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="service-types-list11" class="collapse">
                        @can('service-types-list')<li><a href="{{ route('admin.service.index') }}">@lang('admin.include.list_service_types')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('service-types-create')<li><a href="{{ route('admin.service.create') }}">@lang('admin.include.add_new_service_type')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('peak-hour-list')<li><a href="{{ route('admin.peakhour.index') }}">@lang('admin.include.peakhour')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan
            @can('poi-category-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#poi-list">
                        <span class="s-icon"><i class="fa fa-map-o" aria-hidden="true"></i></span>
                        @lang('admin.include.poi')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="poi-list" class="collapse">
                        @can('poi-category-list')<li><a href="{{ route('admin.poicategory.index') }}">@lang('admin.include.poi_categories')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('point-interest-list')<li><a href="{{ route('admin.pointinterest.index') }}">@lang('admin.include.points_of_interest')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('polygon-shape-list')<li><a href="{{ route('admin.polygonshape.index') }}">@lang('admin.include.poi_shape')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan
            @can('driving-zone-list')
                <li class="">
                    <a href="#" data-toggle="collapse" data-target="#driverzone-list">
                        <span class="s-icon"><i class="fa fa-podcast" aria-hidden="true"></i></span>
                        @lang('admin.include.driving_zone')<i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <ul id="driverzone-list" class="collapse">
                        @can('driving-zone-list')<li><a href="{{ route('admin.drivingzone.index') }}">@lang('admin.include.list_driverzone')<i class="fa fa-chevron-right"></i></a></li>@endcan
                        @can('driving-zone-create')<li><a href="{{ route('admin.drivingzone.create') }}">@lang('admin.include.add_driverzone')<i class="fa fa-chevron-right"></i></a></li>@endcan
                    </ul>
                </li>
            @endcan

            {{--            @role('ADMIN')--}}
            {{--            <li class="menu-title">@lang('admin.include.payment_details')</li>--}}
            {{--            @endrole--}}

            <li class="menu-title">@lang('admin.include.settings')</li>
            @can('site-settings')
                <li>
                    <a href="{{ route('admin.settings') }}">
                        <span class="s-icon"><img src="{{asset('asset/img/repairing-service_black.png')}}"></span>
                        @lang('admin.include.site_settings')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan
            @can('payment-settings')
                <li>
                    <a href="{{ route('admin.settings.payment') }}">
                        <span class="s-icon"><img src="{{asset('asset/img/credit-card_black.png')}}"></span>
                        @lang('admin.include.payment_settings')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan

            @can('cms-pages')
                <li>
                    <a href="{{ route('admin.cmspages') }}" class="waves-effect waves-light">
                        <span class="s-icon"><i class="ti-file"></i></span>
                        @lang('admin.include.cms_pages')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan
            @can('help')
                <li>
                    <a href="{{ route('admin.help') }}" class="waves-effect waves-light">
                        <span class="s-icon"><i class="ti-help"></i></span>
                        @lang('admin.include.help')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan

            @can('transalations')
                <li>
                    <a href="{{route('admin.translation') }}" class="waves-effect waves-light">
                        <span class="s-icon"><i class="ti-smallcap"></i></span>
                        @lang('admin.include.translations')<i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            @endcan


            {{--            @role('ADMIN')--}}
            {{--            <li class="menu-title">@lang('admin.include.others')</li>--}}
            {{--            @endrole--}}

            {{--@role('ADMIN')
            <li class="menu-title">@lang('admin.include.account')</li>
            @endrole

            @can('account-settings')
            <li>
                <a href="{{ route('admin.profile') }}">
                    <span class="s-icon"><img src="{{asset('asset/img/manager_black.png')}}"></span>
                    @lang('admin.include.account_settings')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            @endcan
            @can('change-password')
            <li>
                <a href="{{ route('admin.password') }}">
                    <span class="s-icon"><i class="fa fa-key" aria-hidden="true"></i></span>
                    @lang('admin.include.change_password')<i class="fa fa-chevron-right"></i>
                </a>
            </li>
            @endcan
            <li class="compact-hide">
                <a href="{{ url('/admin/logout') }}" onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
                    <span class="s-icon"><i class="ti-power-off"></i></span>
                    @lang('admin.include.logout')<i class="fa fa-chevron-right"></i>
                </a>

                <form id="logout-form" action="{{ url('/admin/logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </li>--}}

        </ul>

        <p class="copyright">{{ config('constants.site_copyright', '&copy; '.date('Y').' Appoets') }}</p>

    </div>
</div>
