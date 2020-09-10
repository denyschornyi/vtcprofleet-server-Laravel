<nav class="header-navbar navbar-expand-lg navbar navbar-with-menu  navbar-fixed bg-purple navbar-shadow  navbar-brand-center">
    <div class="navbar-wrapper">
        <div class="navbar-container content">
            <div class="navbar-collapse" id="navbar-mobile">
                <div class="mr-auto float-left bookmark-wrapper d-flex align-items-center">
                    <ul class="nav navbar-nav">
                        <li class="nav-item mobile-menu d-xl-none mr-auto">
                            <a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i
                                        class="ficon feather icon-menu"></i></a>
                        </li>
                        <ul class="nav navbar-nav bookmark-icons">
                            <li class="nav-item d-none d-lg-block">

                                <a class="nav-link" href="#" data-toggle="tooltip" data-placement="top"
                                   title="Current Time">
                                    @php $dt = new DateTime(); echo $dt->format('m/d/Y - H:i') @endphp
                                </a>
                            </li>
                        </ul>
                    </ul>
                </div>
                <ul class="nav navbar-nav float-right">
                    <li class="nav-item d-none d-lg-block"><a class="nav-link nav-link-expand">
                            <i class="ficon feather icon-maximize"></i></a></li>


                    <li class="dropdown dropdown-user nav-item"><a class="dropdown-toggle nav-link dropdown-user-link"
                                                                   href="#" data-toggle="dropdown">
                            <div class="user-nav d-sm-flex d-none"><span
                                        class="user-name text-bold-600">{{ Auth::user()->name }}</span>
                                <span class="user-status">Available</span></div>
                            <span><img class="round"
                                       src="{{ Auth::guard('admin')->user()->picture ? asset('storage/'.Auth::guard('admin')->user()->picture) : asset('asset/img/provider.jpg') }}"
                                       alt="avatar" height="40" width="40"/>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" style="color: #626262 !important;" href="{{route('admin.profile')}}">
                                <i class="fa fa-user" style="color: #626262 !important;"></i>
                                Profile
                            </a>
                            <a class="dropdown-item" style="color: #626262 !important;" href="{{route('admin.dispatcher.index')}}">
                                <i class="fa fa-user" style="color: #626262 !important;"></i>
                                @lang('admin.include.dispatcher_panel')
                            </a>

                            <a class="dropdown-item" style="color: #626262 !important;" href="{{route('admin.requests.dispatcher')}}">
                                <i class="fa fa-user" style="color: #626262 !important;"></i>
                                @lang('admin.include.ride_history')
                            </a>

                            <a class="dropdown-item" href="{{route('admin.password')}}" style="color: #626262 !important;">
                                <i class="fa fa-key" style="color: #626262 !important;"></i>
                                Change Password
                            </a>
                            <form id="logout-form" action="{{ url('/admin/logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                            <a class="dropdown-item" style="color: #626262 !important;" href="{{ url('admin/logout') }}" onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
                                <i class="feather icon-power" style="color: #626262 !important;"></i>
                                @lang('admin.include.sign_out')
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
