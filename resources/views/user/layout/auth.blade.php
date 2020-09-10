<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="apple-itunes-app" content="app-id={{env('USER_APPSTORE_ID_NINE', '')}}, affiliate-data=myAffiliateData, app-argument=myURL">

    <title>{{config('constants.site_title','Tranxit')}}</title>

    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="shortcut icon" type="image/png" href="{{ config('constants.site_icon') }}"/>
    <link href="{{asset('asset/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('asset/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <link href="{{asset('asset/css/style.css')}}" rel="stylesheet">

</head>

<body>

	@yield('content')

    <script src="{{asset('asset/js/jquery.min.js')}}"></script>
    <script src="{{asset('asset/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('asset/js/scripts.js')}}"></script>
    @if(Setting::get('demo_mode', 0) == 1)
    @endif

    @yield('scripts')
    
</body>
</html>