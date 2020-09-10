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
    <meta content="" name="description" />
    <meta content="" name="author" />

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href='https://fonts.googleapis.com/css?family=Raleway:400,600,500,700' rel='stylesheet' type='text/css'>

    <link href="{{asset('main/vendor/themify-icons/themify-icons.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('main/vendor/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('newAssets/css/bootstrap.css')}}" rel="stylesheet" type="text/css" />

    <link href="{{asset('newAssets/css/jquery-ui.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('newAssets/css/semantic.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('newAssets/css/style.min.css')}}" rel="stylesheet" type="text/css" />

    <link href="{{asset('newAssets/css/custom.css')}}" rel="stylesheet" type="text/css" />
    <!-- END THEME STYLES -->
    <link rel="icon" href="{{ config('constants.site_icon') }}" sizes="16x16" type="image/png">
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body>
<div class="clearfix"></div> 