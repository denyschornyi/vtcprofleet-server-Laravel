<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ config('constants.site_favicon', asset('favicon.ico')) }}" type="image/x-icon">
    <link rel="icon" href="{{ config('constants.site_favicon', asset('favicon.ico')) }}" type="image/x-icon">

    <title>@yield('title'){{ config('constants.site_title', 'Tranxit') }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ config('constants.site_icon') }}"/>

    <!-- Styles -->
    <link href="{{ asset('asset/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('asset/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('asset/css/slick.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('asset/css/slick-theme.css') }}" rel="stylesheet" type="text/css">


    @if(Config::get('app.locale')=='ar')
    <link href="{{ asset('asset/css/arabic_dashboard-style.css') }}" rel="stylesheet" type="text/css">
    @else
    <link href="{{ asset('asset/css/dashboard-style.css') }}" rel="stylesheet" type="text/css">
    @endif

    @yield('styles')


    <!-- <link href="{{asset('newAssets/css/bootstrap.css')}}" rel="stylesheet" type="text/css" /> -->
    <link href="{{asset('newAssets/css/jquery-ui.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('newAssets/css/semantic.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('newAssets/css/style.min.css')}}" rel="stylesheet" type="text/css" />

    <link href="{{asset('newAssets/css/custom.css')}}" rel="stylesheet" type="text/css" />

    <!-- END THEME STYLES -->
    <script>
        var assetBaseUrl = "{{ asset('') }}storage/";
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
    @yield('styles')
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body>
    <div class="clearfix"></div>

    <div class="page-container">
        <div class="container-fluid">
            <div class="row row-eq-height">
                @include('provider.include.sidebar')
                <div class="page-content dashboard-page col-lg-9 col-md-12 col-sm-12 col-lg-push-3" style="padding-bottom:0px !important;">
                    @include('provider.include.top-header')
                    <div class="content-area py-1">
                        <div class="container-fluid">
                            <div class="box box-block bg-white">
                                @yield('content')
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <!-- @include('provider.layout.partials.footer') -->
    <div id="modal-incoming"></div>

    <script type="text/javascript" src="{{ asset('asset/js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/jquery.mousewheel.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/jquery-migrate-1.2.1.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/slick.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/rating.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/dashboard-scripts.js') }}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/switchery/dist/switchery.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/15.3.1/react.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/15.3.1/react-dom.js"></script>
    <script src="https://unpkg.com/babel-standalone@6.15.0/babel.min.js"></script>
    <script src="https://unpkg.com/react@16/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@16/umd/react-dom.production.min.js"></script>

    <script src="{{asset('newAssets/js/jquery-ui.min.js')}}" type="text/javascript"></script>

    <!-- <script src="{{asset('newAssets/js/semantic.min.js')}}" type="text/javascript"></script> -->
    <script src="{{asset('newAssets/js/jquery.nicescroll.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('newAssets/js/client.js')}}" type="text/javascript"></script>
    <script src="{{asset('newAssets/js/general.js')}}" type="text/javascript"></script>
    <!-- <script src="../assets/js/canvasjs.min.js" type="text/javascript"></script> -->
    <!-- include summernote css/js -->
    <link href="{{asset('newAssets/css/summernote-bs4.css')}}" rel="stylesheet">
    <script src="{{asset('newAssets/js/summernote-bs4.js')}}"></script>

    <script src="{{asset('newAssets/js/sidebar.js')}}"></script>
    <!-- END CORE PLUGINS -->
    @if(Route::current()->getName()!='provider.cards')
        <script type="text/babel" src="{{ asset('asset/js/incominggg.js') }}"></script>
    @endif

    @yield('scripts')

    <script type="text/javascript">


        $('body').on('keypress', '.numbers', function(e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            }
        });

        $('body').on('focus', '.price', function() {

            if ($(this).val() == "0.00") {
                $(this).val("");
            } else if (($(this).val()).length > 0) {
                $(this).val((parseFloat($(this).val())).toFixed(2));
            }
        }).on('focusout', '.price', function() {

            if ($(this).val() == "") {
                $(this).val("0.00");
            } else if (($(this).val()).length > 0) {
                $(this).val((parseFloat($(this).val())).toFixed(2));
            }
        });

        $('body').on('keypress', '.price', function(e) {
            if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
                return false;
            }
        });
    </script>
</body>
<!-- END BODY -->
</html>
