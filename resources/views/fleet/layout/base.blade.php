<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>@yield('title'){{ config('constants.site_title', 'Tranxit') }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf_token" content="{{ csrf_token() }}">
    <meta content="" name="description" />
    <meta content="" name="author" />

    <link rel="stylesheet" href="{{asset('main/vendor/bootstrap4/css/bootstrap.min.css')}}">
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
        .modal-icon-box {
            border: 2px solid #ccc;
            padding: 5px 10px;
            border-radius: 5px;
            background: 0 0;
        }
        #top_td, #bottom_td {
            display: none;
        }
        .middle_td {
            display: none;
        }
        #card_manage{
            padding:10px;
        }
        @yield('styles-in')
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
                @include('fleet.include.sidebar')
                <div class="page-content dashboard-page col-lg-9 col-md-12 col-sm-12 col-lg-push-3" style="padding-bottom:0px !important;">
                    @include('fleet.include.top-header')
                    @include('common.notify')
                    @yield('content')
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    <!-- Vendor JS -->
    <script type="text/javascript" src="{{asset('main/vendor/jquery/jquery-1.12.3.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/tether/js/tether.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/bootstrap4/js/bootstrap.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/detectmobilebrowser/detectmobilebrowser.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/jscrollpane/jquery.mousewheel.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/jscrollpane/mwheelIntent.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/jscrollpane/jquery.jscrollpane.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/jquery-fullscreen-plugin/jquery.fullscreen')}}-min.js"></script>
    <script type="text/javascript" src="{{asset('main/vendor/waves/waves.min.js')}}"></script>
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

    <script type="text/javascript" src="{{asset('main/vendor/switchery/dist/switchery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/dropify/dist/js/dropify.min.js')}}"></script>

    <script type="text/javascript" src="{{asset('main/vendor/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/clockpicker/dist/jquery-clockpicker.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/moment/moment.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/vendor/bootstrap-daterangepicker/daterangepicker.js')}}"></script>

    <!-- Neptune JS -->
    <script type="text/javascript" src="{{asset('main/assets/js/app.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/assets/js/demo.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/assets/js/forms-pickers.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/assets/js/tables-datatable.js')}}"></script>
    <script type="text/javascript" src="{{asset('main/assets/js/forms-upload.js')}}"></script>

    <script type="text/javascript">
        $.base_url = "<?php echo url(''); ?>";
    </script>
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

    @yield('scripts')

    <script type="text/javascript" src="{{asset('asset/js/rating.js')}}"></script>
    <script type="text/javascript">
        $('.rating').rating();
    </script>
    <script type="text/javascript">
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
