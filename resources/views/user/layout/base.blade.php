<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="" name="description" />
    <meta content="" name="author" />

    <title>{{config('constants.site_title','Tranxit')}} - @yield('title') - User Dashboard</title>
    <link rel="shortcut icon" type="image/png" href="{{ config('constants.site_icon') }}"/>

{{--    <link href="{{asset('asset/css/bootstrap.min.css')}}" rel="stylesheet">--}}
    <link rel="stylesheet" href="{{asset('main/vendor/bootstrap4/css/bootstrap.min.css')}}">
    <link href="{{asset('asset/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <link href="{{asset('asset/css/slick.css')}}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('asset/css/slick-theme.css')}}"/>
    <link href="{{asset('asset/css/bootstrap-datepicker.min.css')}}" rel="stylesheet">
    <link href="{{asset('asset/css/bootstrap-timepicker.css')}}" rel="stylesheet">
    @if(Config::get('app.locale')=='ar')
    <link href="{{asset('asset/css/arabic_dashboard-style.css')}}" rel="stylesheet">
    @else
    <link href="{{ asset('asset/css/dashboard-style.css') }}" rel="stylesheet" type="text/css">
    @endif
    <link rel="stylesheet" href="{{ asset('main/assets/css/style_dialog.css')}}">

    <!-- <link href="{{asset('newAssets/css/bootstrap.css')}}" rel="stylesheet" type="text/css" /> -->
    <link href="{{asset('newAssets/css/jquery-ui.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('newAssets/css/semantic.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('newAssets/css/style.min.css')}}" rel="stylesheet" type="text/css" />

    <link href="{{asset('newAssets/css/custom.css')}}" rel="stylesheet" type="text/css" />
    <!-- END THEME STYLES -->
{{--    <link rel="stylesheet" href="{{asset('main/vendor/bootstrap4/css/bootstrap.min.css')}}">--}}
    <link rel="stylesheet" href="{{asset('main/vendor/themify-icons/themify-icons.css')}}">
    <link rel="stylesheet" href="{{asset('main/vendor/font-awesome/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('main/vendor/animate.css/animate.min.css')}}">
    <link rel="stylesheet" href="{{asset('main/vendor/jscrollpane/jquery.jscrollpane.css')}}">
    <link rel="stylesheet" href="{{asset('main/vendor/waves/waves.min.css')}}">
    <link rel="stylesheet" href="{{asset('main/vendor/switchery/dist/switchery.min.css')}}">
    <link rel="stylesheet" href="{{asset('main/vendor/DataTables/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('main/vendor/DataTables/Responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('main/vendor/DataTables/Buttons/css/buttons.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{asset('main/vendor/DataTables/Buttons/css/buttons.bootstrap4.min.css')}}">

    <link rel="stylesheet" type="text/css" href="{{asset('asset/css/bootstrap-datepicker.min.css')}}">
    <link rel="stylesheet" href="{{asset('asset/css/bootstrap-glyphicons.css')}}">
    <link rel="stylesheet" href="{{asset('asset/css/bootstrap-editable.css')}}">
    <link rel="stylesheet" href="{{ asset('main/vendor/dropify/dist/css/dropify.min.css') }}">
    @if(Config::get('app.locale')=='ar')
        <link rel="stylesheet" href="{{ asset('main/assets/css/arabic_core.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('main/assets/css/core.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('main/assets/css/style_pagination.css')}}">
    <link rel="stylesheet" href="{{ asset('main/assets/css/style_dialog.css')}}">

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href='https://fonts.googleapis.com/css?family=Raleway:400,600,500,700' rel='stylesheet' type='text/css'>

    <link href="{{asset('newAssets/css/jquery-ui.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('newAssets/css/semantic.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('newAssets/css/style.min.css')}}" rel="stylesheet" type="text/css" />

    <link href="{{asset('newAssets/css/custom.css')}}" rel="stylesheet" type="text/css" />
    <!-- END THEME STYLES -->

    <link rel="icon" href="{{ config('constants.site_icon') }}" sizes="16x16" type="image/png">
    <script>
        window.Laravel =<?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
    <style type="text/css">
        .rating-outer span,
        .rating-symbol-background {
            color: #ffe000!important;
        }
        .rating-outer span,
        .rating-symbol-foreground {
            color: #ffe000!important;
        }
        th{
            color: rgba(0,0,0,0.87);
            font: 16px Lato,sans-serif;
            padding: 10.5px 30px 10.5px 10.5px;
            font-weight: bold;
        }
        td{
            color: rgba(0,0,0,0.87);
            font: 16px Lato,sans-serif;
            padding: 10.5px;
        }
        .py-1 {
            padding-top: 1rem!important;
            padding-bottom: 1rem!important;
        }
    </style>
    @yield('styles')
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body>
    <div class="clearfix"></div>

    <div class="page-container">
        <div class="container-fluid">
            <div class="row row-eq-height">
                @include('user.include.sidebar')
                <div class="page-content dashboard-page col-lg-9 col-md-12 col-sm-12 col-lg-push-3" style="padding-bottom:0px !important;">
                    @include('user.include.top-header')
                    @include('common.notify')
                    @yield('content')
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    <script src="{{asset('asset/js/jquery.min.js')}}"></script>
    <script src="{{asset('asset/js/bootstrap.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('asset/js/jquery.mousewheel.js')}}"></script>
    <script type="text/javascript" src="{{asset('asset/js/jquery-migrate-1.2.1.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('asset/js/slick.min.js')}}"></script>
    <script src="{{asset('asset/js/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{asset('asset/js/bootstrap-timepicker.js')}}"></script>
    <script src="{{asset('asset/js/dashboard-scripts.js')}}"></script>

    <script src="{{asset('newAssets/js/jquery-ui.min.js')}}" type="text/javascript"></script>

    <script src="{{asset('newAssets/js/semantic.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('newAssets/js/jquery.nicescroll.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('newAssets/js/client.js')}}" type="text/javascript"></script>
    <script src="{{asset('newAssets/js/general.js')}}" type="text/javascript"></script>
    <!-- <script src="../assets/js/canvasjs.min.js" type="text/javascript"></script> -->
    <!-- include summernote css/js -->
    <link href="{{asset('newAssets/css/summernote-bs4.css')}}" rel="stylesheet">
    <script src="{{asset('newAssets/js/summernote-bs4.js')}}"></script>

    <script src="{{asset('newAssets/js/sidebar.js')}}"></script>
    <!-- END CORE PLUGINS -->

    <script type="text/javascript" src="{{asset('main/vendor/DataTables/js/jquery.dataTables.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/DataTables/js/dataTables.bootstrap4.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/DataTables/Responsive/js/dataTables.responsi')}}ve.min.js"></script>
    <script type="text/javascript" src="{{asset('main/vendor/DataTables/Responsive/js/responsive.bootstra')}}p4.min.js"></script>
    <script type="text/javascript" src="{{asset('main/vendor/DataTables/Buttons/js/dataTables.buttons')}}.min.js"></script>
    <script type="text/javascript" src="{{asset('main/vendor/DataTables/Buttons/js/buttons.bootstrap4')}}.min.js"></script>
    <script type="text/javascript" src="{{asset('main/vendor/DataTables/JSZip/jszip.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/DataTables/pdfmake/build/pdfmake.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/DataTables/pdfmake/build/vfs_fonts.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/DataTables/Buttons/js/buttons.html5.min.js')}}"></script>

    <script type="text/javascript" src="{{asset('main/vendor/DataTables/Buttons/js/buttons.print.min.js')}}"></script>

    <script type="text/javascript" src="{{asset('main/vendor/dropify/dist/js/dropify.min.js')}}"></script>

    <!-- Neptune JS -->
    <script type="text/javascript" src="{{asset('main/assets/js/tables-datatable.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/assets/js/forms-upload.js')}}"></script>
    @yield('scripts')

    <script>
        $(document).ready(function() {
            $('#editor, .editor').summernote({
                disableDragAndDrop: true,
                dialogsFade: true,
                height: 250,
                emptyPara: '',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['height', ['height']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
<!-- END BODY -->
</html>
