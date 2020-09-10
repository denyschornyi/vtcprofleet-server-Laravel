<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9 no-js"> <![endif]-->

<html lang="en" class="no-js">

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf_token" content="{{ csrf_token() }}">
    <meta content="" name="description"/>
    <meta content="" name="author"/>

    <title>{{config('constants.site_title','Tranxit')}} - @lang('admin.custom.book_form')</title>
    <link rel="shortcut icon" type="image/png" href="{{ config('constants.site_icon') }}"/>

    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/bootstrap-extended.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/colors.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/components.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/themes/dark-layout.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/themes/semi-dark-layout.min.css')}}">


    <link rel="icon" href="{{ config('constants.site_icon') }}" sizes="16x16" type="image/png">
    <link rel="stylesheet" href="{{asset('asset/booking_form/css/public.css')}}"/>
    <link rel="stylesheet" href="{{asset('asset/booking_form/css/style.css')}}"/>
    <link rel="stylesheet" href="{{asset('asset/booking_form/css/switchery.css')}}"/>
    <link rel="stylesheet" href="{{asset('asset/css/bootstrap-datetimepicker.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('asset/booking_form/css/jquery.ui.min.css')}}">
    <link rel="stylesheet" href="{{asset('asset/booking_form/css/bootstrap-material-datetimepicker.css')}}"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/extensions/toastr.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/plugins/extensions/toastr.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('asset/booking_form/css/jquery.auto-complete.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.5.10/js/material.min.js"></script>
    <script src="https://unpkg.com/sweetalert2@7.8.2/dist/sweetalert2.all.js"></script>

    <style type="text/css">
        ::placeholder {
            color: lightgray;
        }

        ::-ms-input-placeholder {
            color: lightgray;
        }

        .rating-outer span,
        .rating-symbol-background {
            color: #ffe000 !important;
        }

        .rating-outer span,
        .rating-symbol-foreground {
            color: #ffe000 !important;
        }

        th {
            color: rgba(0, 0, 0, 0.87);
            font: 16px Lato, sans-serif;
            padding: 10.5px 30px 10.5px 10.5px;
            font-weight: bold;
        }

        td {
            color: rgba(0, 0, 0, 0.87);
            font: 16px Lato, sans-serif;
            padding: 10.5px;
        }

        .py-1 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        @media (min-width: 1200px) {
            .container {
                max-width: 1300px;
            }
        }

        .dtp table.dtp-picker-days tr > td > span.dtp-select-day {
            font-size: 14px;
        }

        .dtp table.dtp-picker-days tr > td > a.selected {
            font-size: 14px;
        }

        .dtp table.dtp-picker-days tr > td > a, .dtp .dtp-picker-time > a {
            font-size: 14px;
        }
        .disable-button{
            pointer-events: none;
            opacity: 0.3;
        }
        .dtp-buttons .btn-group-sm>.btn, .btn-sm {
            font-size: 0.9rem;
        }
    </style>

</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body>
<div class="clearfix"></div>

<div class="page-container">
    <div class="container-fluid">
        <div class="row row-eq-height">
            <div class="dashboard-page col-md-12 col-sm-12 col-lg-push-3" style="padding-bottom:0px !important;">
                <div class="content-body">
                    <div class="card" style="border: none;">
                        <div class="card-body p-0">
                            <div class="clearfix container" style="margin-top: 50px;">
                                <div class="row chbs-main chbs-booking-form-id-10007 chbs-clear-fix chbs-width-1220"
                                     id="chbs_booking_form_C5961B482E2E1E6A202D2999585137FB">
                                    <form name="chbs-form">
                                        <div class="chbs-main-navigation-default chbs-clear-fix hidden-sm hidden-xs">
                                            <ul class="chbs-list-reset">
                                                <li data-step="1" class="chbs-state-selected">
                                                    <div></div>
                                                    <a href="#">
                                            <span>
                                                <span>1</span>
                                                <span class="chbs-meta-icon-tick"></span>
                                            </span>
                                                        <span>@lang('admin.custom.book_enter')</span>
                                                    </a>
                                                </li>
                                                <li data-step="2">
                                                    <div></div>
                                                    <a href="#">
                                            <span>
                                                <span>2</span>
                                                <span class="chbs-meta-icon-tick"></span>
                                            </span>
                                                        <span>@lang('admin.custom.book_Vehicle')</span>
                                                    </a>
                                                </li>
                                                <li data-step="3">
                                                    <div></div>
                                                    <a href="#">
                                            <span>
                                                <span>3</span>
                                                <span class="chbs-meta-icon-tick"></span>
                                            </span>
                                                        <span>@lang('admin.custom.book_enter_cont')</span>
                                                    </a>
                                                </li>
                                                <li data-step="4">
                                                    <div></div>
                                                    <a href="#">
                                            <span>
                                                <span>4</span>
                                                <span class="chbs-meta-icon-tick"></span>
                                            </span>
                                                        <span>@lang('admin.custom.book_Details')</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="chbs-main-content chbs-clear-fix">
                                            <div class="chbs-main-content-step-1">
                                                <div class="chbs-notice chbs-hidden"></div>
                                                <div class="chbs-layout-50x50 chbs-clear-fix">
                                                    <div class="chbs-layout-column-left">
                                                        <div class="chbs-tab chbs-box-shadow ui-tabs ui-widget ui-widget-content ui-corner-all">
                                                            <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all"
                                                                role="tablist">
                                                                <li id="ui-id-li-2" data-id="1"
                                                                    class="ui-state-default ui-corner-top ui-tabs-active"
                                                                    role="tab"
                                                                    tabindex="-1" aria-controls="panel-2"
                                                                    aria-labelledby="ui-id-2"
                                                                    aria-selected="false" aria-expanded="true">
                                                                    <a href="#panel-1"
                                                                       class="ui-tabs-anchor"
                                                                       role="presentation"
                                                                       tabindex="-1"
                                                                       id="ui-id-2">@lang('admin.custom.book_now')</a>
                                                                </li>
                                                                <li id="ui-id-li-3" data-id="2"
                                                                    class="ui-state-default ui-corner-top" role="tab"
                                                                    tabindex="-1"
                                                                    aria-controls="panel-3" aria-labelledby="ui-id-3"
                                                                    aria-selected="false" aria-expanded="false">
                                                                    <a href="#panel-1"
                                                                       class="ui-tabs-anchor"
                                                                       role="presentation"
                                                                       tabindex="-1"
                                                                       id="ui-id-3">@lang('admin.custom.book_advance')</a>
                                                                </li>
                                                            </ul>
                                                            <div id="panel-1" aria-labelledby="ui-id-1"
                                                                 class="ui-tabs-panel ui-widget-content ui-corner-bottom"
                                                                 role="tabpanel" aria-hidden="false">
                                                                <label class="chbs-form-label-group">@lang('admin.custom.book_ride_details')</label>
                                                                <div class="chbs-clear-fix" id="panle_1_pickuparea">

                                                                    <div class="chbs-form-field chbs-form-field-width-50">
                                                                        <label class="chbs-form-field-label">@lang('admin.custom.book_pick')</label>
                                                                        <input type="text"
                                                                               name="chbs_pickup_date_service_type_1"
                                                                               {{--                                                                               style="padding-left: 40px;"--}}
                                                                               value="" id="start_travelldate"
                                                                               class="chbs-datepicker"
                                                                               size="10">
                                                                    </div>

                                                                    <div class="chbs-form-field chbs-form-field-width-50">
                                                                        <label>@lang('admin.custom.book_pick_heure')</label>
                                                                        <input type="text" autocomplete="off"
                                                                               name="chbs_pickup_time_service_type_1"
                                                                               class="chbs-timepicker"
                                                                               id="start_travelltime" value="">
                                                                    </div>
                                                                </div>
                                                                <div class="chbs-form-field chbs-form-field-location-autocomplete chbs-form-field-location-switch chbs-hidden">
                                                                    {{--                                                                    <i class="fa fa-map-marker"></i>--}}
                                                                    <label>@lang('admin.custom.book_stop')</label>
                                                                    <input type="text" autocomplete="off"
                                                                           name="chbs_waypoint_location_service_type_1[]"
                                                                           {{--                                                                           style="padding-left: 40px;"--}}
                                                                           id="chbs_location_APQBLDDWDIKXGRYC"
                                                                           placeholder="@lang('admin.custom.book_enter_lo')">
                                                                    <input type="hidden"
                                                                           name="chbs_waypoint_location_coordinate_service_type_1[]">
                                                                    <span class="chbs-location-add chbs-meta-icon-plus"></span>
                                                                    <span class="chbs-location-remove chbs-meta-icon-minus"></span>
                                                                </div>
                                                                <div class="chbs-form-field chbs-form-field-location-autocomplete chbs-form-field-location-switch"
                                                                     data-label-waypoint="Waypoint">
                                                                    {{--                                                                    <i class="fa fa-map-marker"></i>--}}
                                                                    <label>@lang('admin.custom.book_location')</label>
                                                                    <input type="text" autocomplete="off"
                                                                           name="chbs_pickup_location_service_type_1"
                                                                           value=""
                                                                           {{--                                                                           style="padding-left: 40px;"--}}
                                                                           id="chbs_location_YBXAHEPRDMOQFHHS"
                                                                           placeholder="@lang('admin.custom.book_enter_lo')">

                                                                    <input type="hidden"
                                                                           name="chbs_pickup_location_coordinate_service_type_1"
                                                                           value="">
                                                                    <span class="chbs-location-add chbs-meta-icon-plus"></span>
                                                                </div>
                                                                <div class="chbs-form-field chbs-form-field-location-autocomplete">
                                                                    {{--                                                                    <i class="fa fa-map-marker"></i>--}}
                                                                    <label>@lang('admin.custom.book_drop')</label>
                                                                    <input type="text" autocomplete="off"
                                                                           {{--                                                                           style="padding-left: 40px;"--}}
                                                                           name="chbs_dropoff_location_service_type_1"
                                                                           value=""
                                                                           id="chbs_location_NVYPVDEISKBNOXSW"
                                                                           placeholder="@lang('admin.custom.book_enter_lo')">
                                                                    <input type="hidden"
                                                                           name="chbs_dropoff_location_coordinate_service_type_1"
                                                                           value="">
                                                                </div>
                                                                <div class="chbs-form-field" id="return_range">
                                                                    <span style="display: block;float: left; color:#2C3E50; font-size:18px; font-weight:700; margin-left: 20px;">
                                                                        @lang('admin.custom.book_return')
                                                                    </span>
                                                                    <div class="onoffswitch"
                                                                         style="margin-left: auto; margin-right: 10px; margin-bottom: 10px;">
                                                                        <input type="checkbox" class="js-switch"/>
                                                                    </div>
                                                                </div>
                                                                <div class="chbs-clear-fix" id="return_date"
                                                                     style="display: none">

                                                                    <div class="chbs-form-field chbs-form-field-width-50">
                                                                        <label class="chbs-form-field-label">@lang('admin.custom.book_return_date')</label>
                                                                        <input type="text" autocomplete="off"
                                                                               name="chbs_return_date_service_type_2"
                                                                               class="chbs-datepicker hasDatepicker"
                                                                               value=""
                                                                               id="return_travelldate" size="10">
                                                                    </div>

                                                                    <div class="chbs-form-field chbs-form-field-width-50">
                                                                        <label>@lang('admin.custom.book_pick_heure')</label>
                                                                        <input type="text" autocomplete="off"
                                                                               name="chbs_pickup_time_service_type_2"
                                                                               id="return_time_picker"
                                                                               class="chbs-timepicker" value="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="chbs-layout-column-right hidden-sm hidden-xs">
                                                        <div class="chbs-google-map">
                                                            <div id="chbs_google_map"></div>
                                                            <div class="chbs-ride-info chbs-box-shadow">
                                                                <div>
                                                                    <span class="chbs-meta-icon-route"></span>
                                                                    <span>@lang('admin.custom.book_distance')</span>
                                                                    <span>
                                                                        <span>0</span>
                                                                        <span>km</span>
					                                                </span>
                                                                </div>
                                                                <div>
                                                                    <span class="chbs-meta-icon-clock"></span>
                                                                    <span>@lang('admin.custom.book_time')</span>
                                                                    <span>
                                                                        <span>0</span>
                                                                        <span>h</span>
                                                                        <span>0</span>
                                                                        <span>m</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="chbs-clear-fix chbs-main-content-navigation-button">
                                                    <a href="#"
                                                       class="chbs-button chbs-button-style-1 chbs-button-step-next">
                                                        @lang('admin.custom.book_choose') <span
                                                                class="chbs-meta-icon-arrow-horizontal-large"></span>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="chbs-main-content-step-2">
                                                <div class="chbs-layout-25x75 chbs-clear-fix"
                                                     style="position: relative;">
                                                    <div class="chbs-layout-column-left hidden-sm hidden-xs hidden-ml"
                                                         style="position:static;  width:264px; height:630px; display:block;vertical-align:baseline; float:left;">
                                                        <div class="chbs-summary">
                                                            <div class="chbs-summary-header">
                                                                <h4>@lang('admin.custom.book_sum')</h4>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_type')</div>
                                                                <div class="chbs-summary-field-value service_kind_type"></div>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_from')</div>
                                                                <div class="chbs-summary-field-value from_to_address">
                                                                </div>
                                                            </div>
                                                            <div class="chbs-summary-field return-trip chbs-hidden">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_from') @lang('admin.custom.book_return_trip')
                                                                </div>
                                                                <div class="chbs-summary-field-value return_from_to_address"></div>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_pick_time')
                                                                </div>
                                                                <div class="chbs-summary-field-value booking_date"></div>
                                                            </div>
                                                            <div class="chbs-summary-field returnDateScope chbs-hidden">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_pick_date')
                                                                </div>
                                                                <div class="chbs-summary-field-value return_date"></div>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-layout-50x50 chbs-clear-fix">
                                                                    <div class="chbs-layout-column-left">
                                                                        <div class="chbs-summary-field-name">@lang('admin.custom.book_distance')
                                                                        </div>
                                                                        <div class="chbs-summary-field-value">
                                                                            <span class="total_distance">0</span>
                                                                            <span>km</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="chbs-layout-column-right">
                                                                        <div class="chbs-summary-field-name">@lang('admin.custom.book_time')
                                                                        </div>
                                                                        <div class="chbs-summary-field-value">
                                                                            <span class="total_hours">0</span>
                                                                            <span>h</span>
                                                                            <span class="total_mins">0</span>
                                                                            <span>m</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="chbs-summary-price-element">
                                                            <div class="chbs-summary-price-element-total"
                                                                 style="border-top: none;">
                                                                <span>Total H.T</span>
                                                                <span class="total_fare"
                                                                      style="border-top: none;">00.00 {{$currency}}</span>
                                                            </div>
                                                            <div class="chbs-summary-price-element-total"
                                                                 style="border-top: none;margin-top: 0;padding-top: 0;">
                                                                <span>@lang('admin.custom.book_promo')</span>
                                                                <span class="promocode_price"
                                                                      style="border-top: none; padding-top: 0;">00.00 {{$currency}}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="chbs-layout-column-right">
                                                        <div class="chbs-vehicle-filter chbs-box-shadow chbs-clear-fix hidden-sm hidden-xs">
                                                            <label class="chbs-form-label-group">@lang('admin.custom.book_lug')</label>
                                                            <div class="chbs-form-field chbs-form-field-width-33">
                                                                <label class="chbs-form-field-label">@lang('admin.custom.book_passenger')</label>
                                                                <div>
                                                                    <select name="chbs_vehicle_passenger_count"
                                                                            id="ui-id-passenger"
                                                                            style="display: none;">
                                                                        <option value="1" selected="selected">1</option>
                                                                        <option value="2">2</option>
                                                                        <option value="3">3</option>
                                                                        <option value="4">4</option>
                                                                        <option value="5">5</option>
                                                                        <option value="6">6</option>
                                                                        <option value="7">7</option>
                                                                        <option value="8">8</option>
                                                                        <option value="9">9</option>
                                                                        <option value="10">10</option>
                                                                        <option value="11">11</option>
                                                                        <option value="12">12</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="chbs-form-field chbs-form-field-width-33"
                                                                 style="cursor: pointer;">
                                                                <label>@lang('admin.custom.book_suite')</label>
                                                                <select name="chbs_vehicle_bag_count"
                                                                        id="ui-id-suitcase"
                                                                        style="display: none;">
                                                                    <option value="1" selected="selected">1</option>
                                                                    <option value="2">2</option>
                                                                    <option value="3">3</option>
                                                                    <option value="4">4</option>
                                                                    <option value="5">5</option>
                                                                    <option value="6">6</option>
                                                                    <option value="7">7</option>
                                                                    <option value="8">8</option>
                                                                    <option value="9">9</option>
                                                                    <option value="10">10</option>
                                                                </select>
                                                            </div>

                                                            <div class="chbs-form-field chbs-form-field-width-33"
                                                                 style="cursor: pointer;">
                                                                <label>@lang('admin.custom.book_service_type')</label>
                                                                <select name="chbs_vehicle_category"
                                                                        id="ui-id-vehicle-type"
                                                                        style="display: none;">
                                                                    <option value="0">- @lang('admin.custom.book_all') -</option>
                                                                    @foreach($services as $key=>$val)
                                                                        <option value={{$val->id}}>{{$val->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                        </div>
                                                        <div class="chbs-notice chbs-hidden"></div>
                                                        <div class="chbs-vehicle-list" id="chbs-vehicle-list">
                                                            <ul class="chbs-list-reset" id="chbs-vehicle-search">

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="chbs-clear-fix chbs-main-content-navigation-button">
                                                    <a href="#"
                                                       class="chbs-button chbs-button-style-2 chbs-button-step-prev">
                                                        <span class="chbs-meta-icon-arrow-horizontal-large"></span>
                                                        @lang('admin.custom.book_choose_ride') </a>
                                                    <a href="#"
                                                       class="chbs-button chbs-button-style-1 chbs-button-step-next">
                                                        @lang('admin.custom.book_enter_cont') <span
                                                                class="chbs-meta-icon-arrow-horizontal-large"></span>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="chbs-main-content-step-3">
                                                <div class="chbs-layout-25x75 chbs-clear-fix"
                                                     style="position: relative;">

                                                    <div class="chbs-layout-column-left hidden-sm hidden-xs hidden-ml"
                                                         style="">
                                                        <div class="chbs-summary">
                                                            <div class="chbs-summary-header">
                                                                <h4>@lang('admin.custom.book_sum')</h4>

                                                            </div>

                                                            <div class="chbs-summary-field">

                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_type')</div>
                                                                <div class="chbs-summary-field-value service_kind_type"></div>

                                                            </div>

                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_from')</div>
                                                                <div class="chbs-summary-field-value from_to_address">
                                                                </div>
                                                            </div>
                                                            <div class="chbs-summary-field return-trip chbs-hidden">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_from') @lang('admin.custom.book_return_trip')
                                                                </div>
                                                                <div class="chbs-summary-field-value return_from_to_address"></div>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_pick_time')
                                                                </div>
                                                                <div class="chbs-summary-field-value booking_date"></div>
                                                            </div>
                                                            <div class="chbs-summary-field returnDateScope chbs-hidden">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_pick_date')
                                                                </div>
                                                                <div class="chbs-summary-field-value return_date"></div>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-layout-50x50 chbs-clear-fix">
                                                                    <div class="chbs-layout-column-left">

                                                                        <div class="chbs-summary-field-name">@lang('admin.custom.book_distance')
                                                                        </div>
                                                                        <div class="chbs-summary-field-value">
                                                                            <span class="total_distance">0</span>
                                                                            <span>km</span>
                                                                        </div>

                                                                    </div>
                                                                    <div class="chbs-layout-column-right">

                                                                        <div class="chbs-summary-field-name">@lang('admin.custom.book_time')
                                                                        </div>
                                                                        <div class="chbs-summary-field-value">
                                                                            <span class="total_hours">0</span>
                                                                            <span>h</span>
                                                                            <span class="total_mins">49</span>
                                                                            <span>m</span></div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_Vehicle')</div>
                                                                <div class="chbs-summary-field-value selected_vehicle_name"></div>
                                                            </div>
                                                        </div>

                                                        <div class="chbs-summary-price-element">
                                                            <div class="chbs-summary-price-element-total"
                                                                 style="border-top: none;">
                                                                <span>Total H.T</span>
                                                                <span class="total_fare"
                                                                      style="border-top: none;">00.00{{$currency}}</span>
                                                            </div>
                                                            <div class="chbs-summary-price-element-total"
                                                                 style="border-top: none;margin-top: 0;padding-top: 0;">
                                                                <span>@lang('admin.custom.book_promo')</span>
                                                                <span class="promocode_price"
                                                                      style="border-top: none; padding-top: 0;">00.00 {{$currency}}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="chbs-layout-column-right">

                                                        <div class="chbs-notice chbs-hidden"></div>

                                                        <div class="chbs-client-form">
                                                            <div class="chbs-client-form-sign-up">

                                                                <div class="chbs-box-shadow">

                                                                    <div class="chbs-clear-fix">
                                                                        <div class="chbs-clear-fix">
                                                                            <div class="chbs-form-field">
                                                                                <label>@lang('admin.custom.book_book_comment')</label>
                                                                                <textarea
                                                                                        name="chbs_comments"></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="chbs-clear-fix">
                                                                    </div>
                                                                    @if ($user_type == "COMPANY" || $user_type == "FLEET_COMPANY")
                                                                        <div class="chbs-clear-fix">
                                                                            <label class="chbs-form-label-group passenger-selects">
                                                                            <span class="chbs-form-checkbox">
                                                                                <span class="chbs-meta-icon-tick"></span>
                                                                            </span>
                                                                                <input name="chbs_client_billing_detail_enable" type="hidden" value="0">
                                                                                @lang('admin.custom.book_have_pass')
                                                                            </label>
                                                                        </div>
                                                                        <div class="chbs-hidden" id="extra-fields">
                                                                            <div class="chbs-form-field chbs-form-field-width-50">
                                                                                <label>@lang('admin.custom.book_first')</label>
                                                                                <input name="chbs_client_contact_detail_first_name_passenger"
                                                                                       type="text"
                                                                                       id="chbs_client_contact_detail_first_name_passenger"
                                                                                       value="">
                                                                            </div>
                                                                            <div class="chbs-form-field chbs-form-field-width-50">
                                                                                <label>@lang('admin.custom.book_last')</label>
                                                                                <input name="chbs_client_contact_detail_last_name_passenger"
                                                                                       type="text" value=""
                                                                                       id="chbs_client_contact_detail_last_name_passenger">
                                                                            </div>
                                                                            <div class="chbs-clear-fix">
                                                                                <div class="chbs-form-field chbs-form-field-width-33">
                                                                                    <label>@lang('admin.custom.book_country')</label>
                                                                                    <input name="chbs_client_billing_detail_country_code_passenger"
                                                                                           type="text"
                                                                                           id="chbs_client_billing_detail_country_code_passenger"
                                                                                           value="">
                                                                                    {{--<select name="chbs_client_billing_detail_country_code" id="ui-id-country" style="display: none;">
                                                                                        @foreach($country_list as $key=>$val)
                                                                                            <option value="{{$val->phonecode}}">{{$val->nicename}}({{$val->phonecode}})</option>
                                                                                        @endforeach
                                                                                    </select>--}}
                                                                                </div>
                                                                                <div class="chbs-form-field chbs-form-field-width-33">
                                                                                    <label>@lang('admin.custom.book_number')</label>
                                                                                    <input name="chbs_client_contact_detail_phone_number_passenger"
                                                                                           type="text"
                                                                                           id="chbs_client_contact_detail_phone_number_passenger"
                                                                                           value="">
                                                                                </div>
                                                                                <div class="chbs-form-field chbs-form-field-width-33">
                                                                                    <label>@lang('admin.custom.book_email')</label>
                                                                                    <input name="chbs_client_contact_detail_email_address_passenger"
                                                                                           type="text"
                                                                                           id="chbs_client_contact_detail_email_address_passenger"
                                                                                           value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @elseif ($user_type == "NORMAL" || $user_type == "FLEET_PASSENGER")
                                                                        <input name="chbs_client_billing_detail_enable"
                                                                               type="hidden" value="0">
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <h4 class="chbs-payment-header">
                                                            @lang('admin.custom.book_method') </h4>
                                                        <ul class="chbs-payment chbs-list-reset">
                                                            @if(Config::get('constants.cash') == 1)
                                                                <li>
                                                                    <a class="chbs-payment-type-1"
                                                                       data-payment-id="CASH">
                                                                        <span class="chbs-meta-icon-wallet"></span>
                                                                        <span class="chbs-payment-name">@lang('admin.custom.book_cash')</span>
                                                                        <span class="chbs-meta-icon-tick"></span>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            <li id="wallet_balance" class="chbs-hidden">
                                                                <a class="chbs-payment-type-1" data-payment-id="WALLET">
                                                                    {{--                                                                    <input type="hidden" name="chbs_wallet_balance" value="0">--}}
                                                                    <span class="chbs-meta-icon-wallet"></span>
                                                                    <span class="chbs-payment-name">@lang('admin.custom.book_wallet')</span>
                                                                    <span class="chbs-meta-icon-tick"></span>
                                                                </a>
                                                            </li>

                                                            {{--<li class="chbs-hidden" id="wallet_balance">
                                                                <a style="text-align: left;">
                                                                    <div class="chbs-wallet-checkbox">
                                                                        <span class="chbs-wallets-checkbox" style="margin-right: 5px;">
                                                                            <span class="chbs-meta-icon-tick"></span>
                                                                        </span>
                                                                        <input type="hidden" name="chbs_wallet_balance" value="0">
                                                                        @lang('user.use_wallet_balance')
                                                                    </div>
                                                                    <div class="chbs-wallet">
                                                                        <span> @lang('user.available_wallet_balance')</span>
                                                                        <span id="wallet_amount"></span>
                                                                    </div>
                                                                </a>
                                                            </li>--}}
                                                            @if(Config::get('constants.card') == 1)
                                                                <li>
                                                                    <a class="chbs-payment-type-2"
                                                                       data-payment-id="CARD">
                                                                        <span class="chbs-meta-icon-tick"></span>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if(Config::get('constants.paypal') == 1)
                                                                <li>
                                                                    <a class="chbs-payment-type-3" data-payment-id="3">
                                                                        <span class="chbs-meta-icon-tick"></span>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>

                                                </div>

                                                <div class="chbs-clear-fix chbs-main-content-navigation-button">
                                                    <a href="#"
                                                       class="chbs-button chbs-button-style-2 chbs-button-step-prev">
                                                        <span class="chbs-meta-icon-arrow-horizontal-large"></span>
                                                        @lang('admin.custom.book_choose') </a>
                                                    <a href="#"
                                                       class="chbs-button chbs-button-style-1 chbs-button-step-next">
                                                        @lang('admin.custom.book_book_sum') <span
                                                                class="chbs-meta-icon-arrow-horizontal-large"></span>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="chbs-main-content-step-4">
                                                <div class="chbs-layout-33x33x33 chbs-clear-fix">

                                                    <div class="chbs-notice chbs-hidden"></div>

                                                    <div class="chbs-layout-column-left">
                                                        <div class="chbs-summary">
                                                            <div class="chbs-summary-header">
                                                                <h4>@lang('admin.custom.book_bill')</h4>
                                                                <a href="#" data-step="3">@lang('admin.custom.book_edit')</a>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_book_comment')</div>
                                                                <div class="chbs-summary-field-value"
                                                                     id="chbs_comment"></div>
                                                            </div>
                                                            <div id="passenger_info">
                                                                <div class="chbs-summary-field">
                                                                    <div class="chbs-layout-50x50 chbs-clear-fix">
                                                                        <div class="chbs-layout-column-left">
                                                                            <div class="chbs-summary-field-name">@lang('admin.custom.book_first')
                                                                            </div>
                                                                            <div class="chbs-summary-field-value"
                                                                                 id="first_name_passenger"></div>
                                                                        </div>
                                                                        <div class="chbs-layout-column-right">
                                                                            <div class="chbs-summary-field-name">@lang('admin.custom.book_last')
                                                                            </div>
                                                                            <div class="chbs-summary-field-value"
                                                                                 id="last_name_passenger"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="chbs-summary-field company_name_passenger">
                                                                    <div class="chbs-summary-field-name">@lang('admin.custom.book_company')
                                                                    </div>
                                                                    <div class="chbs-summary-field-value"
                                                                         id="company_name_passenger"></div>
                                                                </div>
                                                                <div class="chbs-summary-field address_passenger">
                                                                    <div class="chbs-summary-field-name">@lang('admin.custom.book_address')</div>
                                                                    <div class="chbs-summary-field-value"
                                                                         id="address_passenger">

                                                                    </div>
                                                                </div>
                                                                <div class="chbs-summary-field country_code_passenger">
                                                                    <div class="chbs-layout-column-right">
                                                                        <div class="chbs-summary-field-name">@lang('admin.custom.book_country')
                                                                        </div>
                                                                        <div class="chbs-summary-field-value"
                                                                             id="country_code_passenger">

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="chbs-summary-field">
                                                                    <div class="chbs-summary-field-name">@lang('admin.custom.book_number')
                                                                    </div>
                                                                    <div class="chbs-summary-field-value"
                                                                         id="phone_number_passenger">
                                                                    </div>
                                                                </div>
                                                                <div class="chbs-summary-field">
                                                                    <div class="chbs-summary-field-name">@lang('admin.custom.book_email')
                                                                    </div>
                                                                    <div class="chbs-summary-field-value"
                                                                         id="email_address_passenger">

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="chbs-summary">
                                                            <div class="chbs-summary-header">
                                                                <h4>@lang('admin.custom.book_payment_method')</h4>
                                                                <a href="#" data-step="3">@lang('admin.custom.book_edit')</a>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_choice')</div>
                                                                <div class="chbs-summary-field-value"
                                                                     id="payment_setting"></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="chbs-layout-column-center">
                                                        <div class="chbs-google-map-summary">
                                                            <div id="chbs_google_map"
                                                                 style=""></div>
                                                        </div>
                                                        <div class="chbs-summary">
                                                            <div class="chbs-summary-header">
                                                                <h4>@lang('admin.custom.book_ride_details')</h4>
                                                                <a href="#" data-step="1">@lang('admin.custom.book_edit')</a>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_type')</div>
                                                                <div class="chbs-summary-field-value service_kind_type"></div>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_from')</div>
                                                                <div class="chbs-summary-field-value from_to_address"></div>
                                                            </div>
                                                            <div class="chbs-summary-field return-trip chbs-hidden">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_from') @lang('admin.custom.book_return_trip')
                                                                </div>
                                                                <div class="chbs-summary-field-value return_from_to_address"></div>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_pick_time')
                                                                </div>
                                                                <div class="chbs-summary-field-value booking_date"></div>
                                                            </div>
                                                            <div class="chbs-summary-field returnDateScope chbs-hidden">
                                                                <div class="chbs-summary-field-name">@lang('admin.custom.book_return_date')
                                                                </div>
                                                                <div class="chbs-summary-field-value return_date"></div>
                                                            </div>
                                                            <div class="chbs-summary-field">
                                                                <div class="chbs-layout-50x50 chbs-clear-fix">
                                                                    <div class="chbs-layout-column-left">
                                                                        <div class="chbs-summary-field-name">@lang('admin.custom.book_distance')
                                                                        </div>
                                                                        <div class="chbs-summary-field-value">
                                                                            <span class="total_distance">0</span>
                                                                            <span>km</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="chbs-layout-column-right">
                                                                        <div class="chbs-summary-field-name">@lang('admin.custom.book_time')
                                                                        </div>
                                                                        <div class="chbs-summary-field-value">
                                                                            <span class="total_hours">0</span>
                                                                            <span>h</span>
                                                                            <span class="total_mins">0</span>
                                                                            <span>m</span></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="chbs-layout-column-right">
                                                        <div id="vehicle_info">
                                                            <div>
                                                                <img src="" alt="" id="vehicle_image_url">
                                                            </div>
                                                            <div class="chbs-summary">
                                                                <div class="chbs-summary-header">
                                                                    <h4>@lang('admin.custom.book_vehicle_info')</h4>
                                                                    <a href="#" data-step="2">@lang('admin.custom.book_edit')</a>
                                                                </div>
                                                                <div class="chbs-summary-field">
                                                                    <div class="chbs-summary-field-name">@lang('admin.custom.book_Vehicle')</div>
                                                                    <div class="chbs-summary-field-value selected_vehicle_name"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="chbs-clear-fix chbs-coupon-code-section">
                                                            <div class="chbs-form-field">
                                                                <label>@lang('admin.custom.book_have_discount')</label>
                                                                <input maxlength="12" name="chbs_coupon_code" value=""
                                                                       type="text">
                                                            </div>
                                                            <a id="promo_code" class="chbs-button chbs-button-style-2">
                                                                @lang('admin.custom.book_have_apply')
                                                                <span class="chbs-meta-icon-arrow-horizontal"></span>
                                                            </a>
                                                        </div>

                                                        <div class="chbs-summary-price-element">
                                                            <div class="chbs-summary-price-element-total"
                                                                 style="border-top: none;">
                                                                <span>Total H.T</span>
                                                                <span class="total_fare"
                                                                      style="border-top: none;">00.00{{$currency}}</span>
                                                            </div>
                                                            <div class="chbs-summary-price-element-total"
                                                                 style="border-top: none;margin-top: 0;padding-top: 0;">
                                                                <span>@lang('admin.custom.book_promo')</span>
                                                                <span class="promocode_price"
                                                                      style="border-top: none; padding-top: 0;">00.00 {{$currency}}</span>
                                                            </div>
                                                            <div class="chbs-summary-price-element-total" style="border-top: none;margin-top: 0px;padding-top: 0px;border-bottom: 5px solid;padding-bottom: 10px;">
                                                                <span>@lang('admin.custom.book_tax') ({{$tax}}%)</span>
                                                                <span class="tax_price" style="border-top: none;padding-top: 0px;">00.00 €</span>
                                                            </div>
                                                            <div class="chbs-summary-price-element-total" style="border-top: none;margin-top: 12px;padding-top: 0px;/* border-bottom: 5px solid; *//* padding-bottom: 7px; */">
                                                                <span></span>
                                                                <span class="total_price" style="border-top: none;padding-top: 0px;font-size: 22px;">00.00 €</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="chbs-clear-fix chbs-main-content-navigation-button">
                                                    <a href="#"
                                                       class="chbs-button chbs-button-style-2 chbs-button-step-prev">
                                                        <span class="chbs-meta-icon-arrow-horizontal-large"></span>
                                                        @lang('admin.custom.book_enter_cont') </a>
                                                    <a href="#"
                                                       class="chbs-button chbs-button-style-1 chbs-button-step-next"
                                                       id="booking_submit">
                                                        @lang('admin.custom.book_book_now')
                                                        <span class="chbs-meta-icon-arrow-horizontal-large"></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <input data-value="" name="action" type="hidden" value="">

                                        <input data-value="1" name="chbs_step" type="hidden" value="1">
                                        <input data-value="1" name="chbs_step_request" type="hidden" value="1">

                                        <input data-value="0" name="chbs_payment_id" type="hidden" value="0">
                                        <input data-value="-1" name="chbs_vehicle_id" type="hidden" value="-1">

                                        <input data-value="0" name="chbs_booking_extra_id" type="hidden" value="0">

                                        <input data-value="0" name="chbs_distance_map" type="hidden" value="0">
                                        <input data-value="0" name="chbs_duration_map" type="hidden" value="0">

                                        <input data-value="0" name="chbs_distance_sum" type="hidden" value="0">
                                        <input data-value="0" name="chbs_duration_sum" type="hidden" value="0">

                                        <input data-value="0" name="chbs_base_location_distance" type="hidden"
                                               value="0">
                                        <input data-value="0" name="chbs_base_location_duration" type="hidden"
                                               value="0">

                                        <input data-value="0" name="chbs_base_location_return_distance" type="hidden"
                                               value="0">
                                        <input data-value="0" name="chbs_base_location_return_duration" type="hidden"
                                               value="0">
                                        <input data-value="10007" name="chbs_booking_form_id" type="hidden"
                                               value="10007">

                                        <input data-value="1" name="chbs_service_type_id" type="hidden" value="1">

                                        <input data-value="10008" name="chbs_post_id" type="hidden" value="10008">

                                        <input data-value="0" name="search_status" type="hidden" value="0">
                                        <input data-value="0" name="temp_search_status" type="hidden" value="0">
                                        <input data-value="0" name="surge_price" type="hidden" value="0">

                                        <input data-value="{{$user_id}}" name="passenger_id" type="hidden"
                                               value="{{$user_id}}">
                                        <input data-value="0" name="promocode_id" type="hidden" value="0">
                                        <input data-value="0" name="promo_percentage" type="hidden" value="0">

                                        <input name="currency" type="hidden" value="{{$currency}}">
                                        <input data-value="0"  id = "promo_max_amount" type="hidden" value="0">
                                        <input id = "tax_percentage" type="hidden" value="{{$tax}}">

                                        <input type="hidden" id = "errorTxt" value=@lang('admin.custom.error')>
                                        <input type="hidden" id = "successTxt" value=@lang('admin.custom.success')>
                                        <input type="hidden" id = "successTxt" value=<?php echo trans('admin.custom.waypoint_error'); ?>>

                                    </form>
                                    <div id="chbs-preloader"></div>
                                    <div id="chbs-preloader-start"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

<script type="text/javascript" src="{{asset('/app-assets/js/core/libraries/jquery.min.js')}}"></script>

<script>
    function initMap() {
        var input = document.getElementById('chbs_client_billing_detail_address_passenger');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            document.getElementById('address').value = place.name;
            // document.getElementById('latitude').value = place.geometry.location.lat();
            // document.getElementById('longitude').value = place.geometry.location.lng();
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('constants.map_key') }}&libraries=places&callback=initMap"></script>
<script src="{{asset('app-assets/vendors/js/extensions/toastr.min.js')}}"></script>
<script>
    flag = false;

    function resize() {
        let size = screen.width;

        $(".chbs-main").removeClass("chbs-width-300");
        $(".chbs-main").removeClass("chbs-width-480");
        $(".chbs-main").removeClass("chbs-width-768");
        $(".chbs-main").removeClass("chbs-width-1220");

        if (size < 480) {
            $(".chbs-main").addClass("chbs-width-300");
        } else if (size < 768) {
            $(".chbs-main").addClass("chbs-width-480");
        } else if (size < 1220) {
            $(".chbs-main").addClass("chbs-width-768");
        } else {
            $(".chbs-main").addClass("chbs-width-1220");
        }
    }

    $(document).ready(function () {
       var way_txt = "{{trans('admin.custom.waypoint_error')}}";
       var pickup_require = "{{ trans('admin.custom.pickup_require') }}";
       var drop_require = "{{ trans('admin.custom.drop_require') }}";
       var distance_require = "{{ trans('admin.custom.distance_require') }}";
       var time_require = "{{ trans('admin.custom.time_require') }}";
       var return_date_require = "{{ trans('admin.custom.return_date_require') }}";
       var return_time_require = "{{ trans('admin.custom.return_time_require') }}";
       var return_date = "{{ trans('admin.custom.return_date') }}";
       var select_vehicle = "{{ trans('admin.custom.select_vehicle') }}";
       var select_payment = "{{ trans('admin.custom.select_payment') }}";
       var first_name_require = "{{ trans('admin.custom.first_name_require') }}";
       var last_name_require = "{{ trans('admin.custom.last_name_require') }}";
       var phone_number_require = "{{ trans('admin.custom.phone_number_require') }}";
       var email_require = "{{ trans('admin.custom.email_require') }}";
       var company_name_require = "{{ trans('admin.custom.company_name_require') }}";
       var address_field_require = "{{ trans('admin.custom.address_field_require') }}";
       var zone_error = "{{ trans('admin.custom.zone_error') }}";
       var promo_error = "{{ trans('admin.custom.promo_error') }}";
       var update_successfully = "{{ trans('admin.custom.update_successfully') }}";
       var promo_used = "{{ trans('admin.custom.promo_used') }}";
       var promo_apply = "{{ trans('admin.custom.promo_apply') }}";

        var userBookingForm = new UserBookingForm($('#chbs_booking_form_C5961B482E2E1E6A202D2999585137FB'), {
                // ajax_url :   'http://quanticalabs.com/wp_plugins/chauffeur-booking-system/wp-admin/admin-ajax.php',

                length_unit: 1,
                time_format: 'G:i',
                date_format: 'd-m-Y',
                date_format_js: 'dd-mm-yy',
                message:
                    {
                        designate_route_error: 'It is not possible to create a route between chosen points.',
                        way_txt : way_txt,
                        pickup_require : pickup_require,
                        drop_require : drop_require,
                        distance_require : distance_require,
                        time_require : time_require,
                        return_date_require : return_date_require,
                        return_time_require : return_time_require,
                        return_date : return_date,
                        select_vehicle : select_vehicle,
                        select_payment : select_payment,
                        first_name_require : first_name_require,
                        last_name_require : last_name_require,
                        phone_number_require : phone_number_require,
                        email_require : email_require,
                        company_name_require : company_name_require,
                        address_field_require : address_field_require,
                        zone_error : zone_error,
                        promo_error : promo_error,
                        update_successfully : update_successfully,
                        promo_used : promo_used,
                        promo_apply : promo_apply
                    },
                text:
                    {
                        unit_length_short: 'km',
                        unit_time_hour_short: 'h',
                        unit_time_minute_short: 'h',
                    },
                date_exclude: [],
                business_hour: {
                    "1": {"start": "06:00", "stop": "22:00"},
                    "2": {"start": "06:00", "stop": "22:00"},
                    "3": {"start": "06:00", "stop": "22:00"},
                    "4": {"start": "06:00", "stop": "22:00"},
                    "5": {"start": "06:00", "stop": "22:00"},
                    "6": {"start": "06:00", "stop": "22:00"},
                    "7": {"start": null, "stop": null}
                },
                booking_period_from: null,
                booking_period_to: null,
                timepicker_step: 30,
                summary_sidebar_sticky_enable: 1,
                driving_zone:
                    {
                        pickup:
                            {
                                enable: 0,
                                country: [-1],
                                area:
                                    {
                                        radius: 50,
                                        coordinate:
                                            {
                                                lat: '',
                                                lng: ''
                                            }
                                    }
                            },
                        dropoff:
                            {
                                enable: 0,
                                country: [-1],
                                area:
                                    {
                                        radius: 50,
                                        coordinate:
                                            {
                                                lat: '',
                                                lng: ''
                                            }
                                    }
                            }
                    },
                gooogle_map_option:
                    {
                        route_avoid: [-1],
                        draggable:
                            {
                                enable: 1
                            },
                        traffic_layer:
                            {
                                enable: 0
                            },
                        scrollwheel:
                            {
                                enable: 1
                            },
                        map_control:
                            {
                                enable: 1,
                                id: 'ROADMAP',
                                style: 'DROPDOWN_MENU',
                                position: 'LEFT_TOP'
                            },
                        zoom_control:
                            {
                                enable: 1,
                                style: 'DEFAULT',
                                position: 'RIGHT_BOTTOM',
                                level: 6
                            },
                        default_location:
                            {
                                type: 2,
                                coordinate:
                                    {
                                        lat: 48.8572484,
                                        lng: 2.3525650999999925
                                    }
                            },
                    },
                base_location:
                    {
                        coordinate:
                            {
                                lat: '',
                                lng: ''
                            }
                    },
                widget:
                    {
                        mode: 0,
                        booking_form_url: ''
                    },
                rtl_mode: 0,
                scroll_to_booking_extra_after_select_vehicle_enable: 0,
                current_date: '30-09-2019',
                current_time: '05:56',
                extra_time_unit: 1
            });
        userBookingForm.setup();

        //set page visible
        $('#panle_1_pickuparea').addClass('display-hidden');
        $('#return_range').addClass('display-hidden');
        // booking.setScreenSetting();

        // ------begin step 1 ----------------


        $('.chbs-datepicker').bootstrapMaterialDatePicker
        ({
            time: false,
            clearButton: true,
            format: 'DD-MM-YYYY',
            minDate: new Date()
        });
        $('.chbs-timepicker').bootstrapMaterialDatePicker
        ({
            date: false,
            shortTime: false,
            format: 'HH:mm'
        });

        $('#ui-id-71').selectmenu();
        $('#ui-id-8').selectmenu();
        $('#ui-id-7').selectmenu();
        $('#ui-id-9').selectmenu();

        //end ----------step 1 -----------------

        //step2
        $('#ui-id-passenger').selectmenu();
        $('#ui-id-suitcase').selectmenu();
        $('#ui-id-vehicle-type').selectmenu();
        //step 3
        $('#ui-id-country').selectmenu();

        $("select + span>span:first-child").attr("class", "chbs-meta-icon-arrow-vertical-large");
        $(".chbs-payment.chbs-list-reset a").click(function () {
            $(".chbs-payment.chbs-list-reset a").removeClass("chbs-state-selected");
            $(this).addClass("chbs-state-selected");
        });

        $(".chbs-form-checkbox").click(function () {
            $(this).toggleClass("chbs-state-selected");
            $("#extra-fields").toggleClass("chbs-hidden");
        });

        $(".chbs-wallets-checkbox").click(function () {
            $(this).toggleClass("chbs-state-selected");
            var wallet_balance = $('input[name="chbs_wallet_balance"]');
            var values = wallet_balance.val() == 1 ? 0 : 1;
            wallet_balance.val(values);
        });

        $('#chbs_client_contact_detail_first_name_passenger').autoComplete({
            minChars: 1,
            source: function (term, suggest) {
                term = term.toLowerCase();
                var choices = @json($first_name);

                var suggestions = [];
                for (i = 0; i < choices.length; i++)
                    if (~choices[i].toLowerCase().indexOf(term)) suggestions.push(choices[i]);
                suggest(suggestions);
            }
        });

        $('#chbs_client_contact_detail_first_name_passenger').change(function () {
            var first_name = $('#chbs_client_contact_detail_first_name_passenger').val();

            var choices = @json($first_name);
            for (i = 0; i < choices.length; i++)
                if (first_name == choices[i]) {
                    $.ajax({
                        url: '/getAllFieldUser',
                        type: 'get',
                        data: {option: 'first_name', first_name: first_name},
                        success: function (res) {
                            $('input[name="chbs_client_contact_detail_last_name_passenger"]').val(res.last_name);
                            $('input[name="passenger_id"]').val(res.id);
                            $('input[name="chbs_client_contact_detail_email_address_passenger"]').val(res.email);
                            $('input[name="chbs_client_contact_detail_phone_number_passenger"]').val(res.mobile);
                            $('input[name="chbs_client_billing_detail_country_code_passenger"]').val(res.country_code);
                            //whether show wallet function or not
                            userBookingForm.showUseWallet();
                        }
                    })
                }
        });

        $('#chbs_client_contact_detail_last_name_passenger').autoComplete({
            minChars: 1,
            source: function (term, suggest) {
                term = term.toLowerCase();
                var choices = @json($last_name);

                var suggestions = [];
                for (i = 0; i < choices.length; i++)
                    if (~choices[i].toLowerCase().indexOf(term)) suggestions.push(choices[i]);
                suggest(suggestions);
            }
        });

        $('#chbs_client_contact_detail_last_name_passenger').change(function () {
            var last_name = $('#chbs_client_contact_detail_last_name_passenger').val();
            // console.log(last_name);
            var choices = @json($last_name);
            for (i = 0; i < choices.length; i++)
                if (last_name == choices[i]) {
                    $.ajax({
                        url: '/getAllFieldUser',
                        type: 'get',
                        data: {option: 'last_name', last_name: last_name},
                        success: function (res) {
                            $('input[name="chbs_client_contact_detail_first_name_passenger"]').val(res.first_name);
                            $('input[name="passenger_id"]').val(res.id);
                            $('input[name="chbs_client_contact_detail_email_address_passenger"]').val(res.email);
                            $('input[name="chbs_client_contact_detail_phone_number_passenger"]').val(res.mobile);
                            $('input[name="chbs_client_billing_detail_country_code_passenger"]').val(res.country_code);
                            //whether show wallet function or not
                            userBookingForm.showUseWallet();
                        }
                    })
                }
        });

        $('#chbs_client_contact_detail_email_address_passenger').autoComplete({
            minChars: 1,
            source: function (term, suggest) {
                term = term.toLowerCase();
                var choices = @json($email);

                var suggestions = [];
                for (i = 0; i < choices.length; i++)
                    if (~choices[i].toLowerCase().indexOf(term)) suggestions.push(choices[i]);
                suggest(suggestions);
            }
        });

        $('#chbs_client_contact_detail_email_address_passenger').change(function () {
            var email = $('#chbs_client_contact_detail_email_address_passenger').val();
            // console.log(email);
            var choices = @json($email);
            for (i = 0; i < choices.length; i++)
                if (email == choices[i]) {
                    $.ajax({
                        url: '/getAllFieldUser',
                        type: 'get',
                        data: {option: 'email', email: email},
                        success: function (res) {
                            $('input[name="chbs_client_contact_detail_first_name_passenger"]').val(res.first_name);
                            $('input[name="chbs_client_contact_detail_last_name_passenger"]').val(res.last_name);
                            $('input[name="passenger_id"]').val(res.id);
                            // $('input[name="chbs_client_contact_detail_email_address_passenger"]').val(res.email);
                            $('input[name="chbs_client_contact_detail_phone_number_passenger"]').val(res.mobile);
                            $('input[name="chbs_client_billing_detail_country_code_passenger"]').val(res.country_code);
                            //whether show wallet function or not
                            userBookingForm.showUseWallet();
                        }
                    })
                }
        });

        $('#chbs_client_contact_detail_phone_number_passenger').autoComplete({
            minChars: 1,
            source: function (term, suggest) {
                term = term.toLowerCase();
                var choices = @json($mobile);

                var suggestions = [];
                for (i = 0; i < choices.length; i++)
                    if (~choices[i].toLowerCase().indexOf(term)) suggestions.push(choices[i]);
                suggest(suggestions);
            }
        });
    });

    $('#chbs_client_contact_detail_phone_number_passenger').change(function () {
        var mobile = $('#chbs_client_contact_detail_phone_number_passenger').val();

        var choices = @json($mobile);
        for (i = 0; i < choices.length; i++)
            if (mobile == choices[i]) {
                $.ajax({
                    url: '/getAllFieldUser',
                    type: 'get',
                    data: {option: 'mobile', mobile: mobile},
                    success: function (res) {
                        console.log(res);
                        $('input[name="chbs_client_contact_detail_first_name_passenger"]').val(res.first_name);
                        $('input[name="chbs_client_contact_detail_last_name_passenger"]').val(res.last_name);
                        $('input[name="passenger_id"]').val(res.id);
                        $('input[name="chbs_client_contact_detail_email_address_passenger"]').val(res.email);
                        $('input[name="chbs_client_billing_detail_country_code_passenger"]').val(res.country_code);
                        //whether show wallet function or not
                        userBookingForm.showUseWallet();
                    }
                })
            }
    });

    $("#booking_submit").click(function () {
        // console.log('click');
        var csrf_token = $('meta[name="csrf_token"]').attr('content');
        var start_location = $('input[name="chbs_pickup_location_coordinate_service_type_1"]').val();
        var s_latitude = JSON.parse(start_location)['lat'];
        var s_longitude = JSON.parse(start_location)['lng'];
        var s_address = JSON.parse(start_location)['address'];
        var chbs_service_type_id = $('input[name="chbs_service_type_id"]').val();
        var destination_location = $('input[name="chbs_dropoff_location_coordinate_service_type_1"]').val();
        var d_latitude = JSON.parse(destination_location)['lat'];
        var d_longitude = JSON.parse(destination_location)['lng'];
        var d_address = JSON.parse(destination_location)['address'];
        var way_location = document.getElementsByName("chbs_waypoint_location_coordinate_service_type_1[]");
        if (way_location.length > 1) {
            var way_locations = [];
            for (var i = 0; i < way_location.length; i++) {
                if (way_location[i].value !== "")
                    way_locations.push(JSON.parse(way_location[i].value));
            }
        }
        var schedule_date = $('input[name="chbs_pickup_date_service_type_1"]').val();
        var schedule_time = $('input[name="chbs_pickup_time_service_type_1"]').val();
        var switch_val = $('.js-switch').prop('checked');
        if (switch_val === true) {
            var schedule_return_date = $('input[name="chbs_return_date_service_type_2"]').val();
            var schedule_return_time = $('input[name="chbs_pickup_time_service_type_2"]').val();
        }

        var total_distance = $('input[name="chbs_distance_sum"]').val();
        var total_time = $('input[name="chbs_duration_sum"]').val();
        var vehicle_id = $('input[name="chbs_vehicle_id"]').val();
        var chbs_comments = $('textarea[name="chbs_comments"]').val();
        var traveller_type = '';
        if ($('input[name="chbs_client_billing_detail_enable"]').val() == '0')
            traveller_type = 'TRAVELLER';
        else
            traveller_type = 'PASSENGER';

        var passenger_firstname = $('input[name="chbs_client_contact_detail_first_name_passenger"]').val();
        var passenger_lastname = $('input[name="chbs_client_contact_detail_last_name_passenger"]').val();
        var passenger_name = passenger_firstname + " " + passenger_lastname;
        var passenger_email_address = $('input[name="chbs_client_contact_detail_email_address_passenger"]').val();
        var passenger_phone_number = $('input[name="chbs_client_contact_detail_phone_number_passenger"]').val();
        var passenger_country_code = $('select[name="chbs_client_billing_detail_country_code_passenger"]').val();

        var chbs_payment_id = $('input[name="chbs_payment_id"]').val();
        var chbs_coupon_code = $('input[name="chbs_coupon_code"]').val();
        var is_user_pro_status = $('input[name="chbs_client_user_pro_enable"]').val();
        var wallet_balance = $('input[name="chbs_wallet_balance"]').val();
        var total_price = document.getElementsByClassName('total_fare')[0].lastChild.textContent;
        var promocode_id = $('input[name="promocode_id"]').val();
        var promo_price = document.getElementsByClassName('promocode_price')[0].lastChild.textContent;

        var calculateState = $('input[name="search_status"]').val();
        var temp_search_status = $('input[name="temp_search_status"]').val();
        var surge_price = $('input[name="surge_price"]').val();
        if(calculateState == 'distance'){
            calculateState = 'distance'
        }else if(calculateState == 'poi'){
            if(temp_search_status == 'poi'){
                calculateState = 'poi'  //poi , 1, poi (search_status, surge, temp_search_status)
            }else if(temp_search_status == 'distance'){
                calculateState = 'distance'  // poi , 1, distance (search_status, surge, temp_search_status)
            }
        }

        var data = {
            _token: csrf_token,
            s_latitude: s_latitude,
            s_longitude: s_longitude,
            s_address: s_address,
            destination_location: destination_location,
            d_latitude: d_latitude,
            d_longitude: d_longitude,
            d_address: d_address,
            way_locations: JSON.stringify(way_locations),
            schedule_date: schedule_date,
            schedule_time: schedule_time,
            chbs_service_type_id: chbs_service_type_id,
            schedule_return_date: schedule_return_date,
            schedule_return_time: schedule_return_time,
            distance: total_distance,
            total_time: total_time,
            service_type: vehicle_id,
            note: chbs_comments,
            traveller_type: traveller_type,
            passenger_name: passenger_name,
            passenger_firstname: passenger_firstname,
            passenger_lastname: passenger_lastname,
            passenger_email_address: passenger_email_address,
            passenger_phone: passenger_phone_number,
            passenger_country_code: passenger_country_code,
            payment_mode: chbs_payment_id,
            is_user_pro_status: is_user_pro_status,
            wallet_balance: wallet_balance,
            total_price: total_price,
            promocode_id: promocode_id,
            promo_price: promo_price,
            calculateState : calculateState,
            surge_price : surge_price  //1: applied surge price, 0: not applied.
        };
        // console.log(data);
        $('#booking_submit').addClass('disable-button');
        $.ajax({
            url: '/create/ride',
            method: 'POST',
            data: data,
            success: function (result) {
                // console.log(result);
                $('#booking_submit').removeClass('disable-button');
                if (result.error) {
                    swal(
                        '@lang('admin.custom.error')',
                        result.error,
                        'error'
                    );
                    return false;
                } else if (result.message) {
                    swal(
                        '@lang('admin.custom.success')',
                        result.message,
                        'success'
                    );
                    location.reload();
                }

            }
        })
    });

    $("#promo_code").click(function ()
    {
        if ($("#promo_code").hasClass('chbs-state-selected')) return;
        var chbs_coupon_code = $('input[name="chbs_coupon_code"]').val();

        if (chbs_coupon_code == "") {
            swal(
                '@lang('admin.custom.error')',
                '@lang("admin.custom.promo_code")',
                'error'
            );
            return false;
        }
        var passenger_id = $('input[name="passenger_id"]').val();
        var csrf_token = $('meta[name="csrf_token"]').attr('content');

        data = {
            coupon_code: chbs_coupon_code,
            passenger_id: passenger_id,
            _token: csrf_token
        };

        $.ajax({
            url: '/checkPromoCodeUsage',
            method: 'POST',
            data: data,
            success: function (result) {
                if (!result.status) {
                    swal(
                        '@lang('admin.custom.error')',
                        result.error,
                        'error'
                    );
                    return false;
                } else {
                    swal(
                        '@lang('admin.custom.success')',
                        result.message,
                        'success'
                    );

                    $('input[name="promocode_id"]').val(result.promo_id);
                    let currency = $('input[name="currency"]').val();
                    let total_fare = parseFloat(document.getElementsByClassName('total_fare')[0].lastChild.textContent.slice(0, -1));
                    $('input[name="promo_percentage"]').val(result.percentage); //set promo percentage

                    let promo_pecentage = $('input[name="promo_percentage"]').val();
                    let promo_price = parseFloat(total_fare * promo_pecentage / 100).toFixed(2);

                    if(promo_price > result.max_amount){
                        promo_price = result.max_amount;
                    }
                    $('#promo_max_amount').val(result.max_amount);
                    $('.promocode_price').text(promo_price + " " + currency);
                    $('.total_fare').text(parseFloat(total_fare - promo_price).toFixed(2) + " " + currency);

                    let tax_percentage = parseFloat($("#tax_percentage").val());
                    let tax_price = parseFloat((total_fare - promo_price) * (tax_percentage / 100));
                    $('.tax_price').text(tax_price.toFixed(2) + " " + currency);
                    $('.total_price').text(parseFloat(total_fare - promo_price + tax_price).toFixed(2) + " " + currency);

                    $("#promo_code").addClass("chbs-state-selected");

                }
            }
        })
    });
    // $("#booking_submit").text('Book Now');
    // $("#booking_submit").text('');
</script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="{{asset('asset/booking_form/js/jquery.actual.min.js')}}"></script>
<script type="text/javascript" src="{{asset('asset/booking_form/js/jquery.timepicker.min.js')}}"></script>
<script type="text/javascript" src="{{asset('asset/booking_form/js/jquery.qtip.min.js')}}"></script>
<script type="text/javascript" src="{{asset('asset/booking_form/js/jquery.sticky-kit.min.js')}}"></script>
<script type="text/javascript" src="{{asset('asset/booking_form/js/jquery.fancybox.js')}}"></script>
<script type="text/javascript" src="{{asset('asset/booking_form/js/jquery.fancybox-media.js')}}"></script>
<script type="text/javascript" src="{{asset('asset/booking_form/js/jquery.fancybox-buttons.js')}}"></script>
{{--    <script type="text/javascript" src="{{asset('asset/booking_form/js/jquery.scrollTo.min.js')}}"></script>--}}
@if(is_null(\Illuminate\Support\Facades\Auth::user()->language) || \Illuminate\Support\Facades\Auth::user()->language =='en')
    <script type="text/javascript" src="{{asset('asset/booking_form/js/moment-with-locales.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('asset/booking_form/js/bootstrap-material-datetimepicker.js')}}"></script>
@else
    <script type="text/javascript" src="{{asset('asset/booking_form/js/moment-with-france.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('asset/booking_form/js/bootstrap-material-datetimepicker_fr.js')}}"></script>
@endif

<script type="text/javascript" src="{{asset('asset/booking_form/js/switchery.js')}}"></script>

<script src="{{asset('asset/booking_form/js/helper.js')}}"></script>
{{--    <script src="{{asset('asset/booking_form/js/jquery.BookingForm.js')}}"></script>--}}
<script src="{{asset('asset/booking_form/js/jquery.UserBookingForm.js')}}"></script>
<script src="{{asset('asset/booking_form/js/jquery.auto-complete.js')}}"></script>

</body>

</html>





