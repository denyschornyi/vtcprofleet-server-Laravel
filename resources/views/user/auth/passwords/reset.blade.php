@extends('user.layout.auth')

@section('content')

<?php $login_user = asset('asset/img/login-user-bg.jpg'); ?>
<div class="full-page-bg" style="background-image: url({{$login_user}});">
<div class="log-overlay"></div>
    <div class="full-page-bg-inner">
        <div class="row no-margin">
            <div class="col-md-6 log-left">
                <span class="login-logo"><img src="{{asset('asset/img/logo.png')}}"></span>
                <h2>@lang('admin.custom.user_text_a')</h2>
                <p>@lang('admin.custom.user_text_b') {{ config('constants.site_title', 'Tranxit')  }}, @lang('admin.custom.user_text_c')</p>
            </div>
            <div class="col-md-6 log-right">
                <div class="login-box-outer">
                <div class="login-box row no-margin">
                    <div class="col-md-12">
                        <a class="log-blk-btn" href="{{url('login')}}">@lang('admin.custom.user_have')</a>
                        <h3>@lang('admin.auth.reset_password')</h3>
                    </div>
                     @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form role="form" method="POST" action="{{ url('/password/reset') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="col-md-12">
                            <input type="email" class="form-control" name="email" placeholder="@lang('admin.user-pro.email')" value="{{ old('email') }}">

                            @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif                        
                        </div>
                        <div class="col-md-12">
                            <input type="password" class="form-control" name="password" placeholder="@lang('admin.password')">

                            @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <input type="password" placeholder="@lang('admin.fleet.password_confirmation')" class="form-control" name="password_confirmation">

                            @if ($errors->has('password_confirmation'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                                </span>
                            @endif
                        </div>
                        
                        <div class="col-md-12">
                            <button class="log-teal-btn" type="submit">@lang('admin.auth.reset_password')</button>
                        </div>
                    </form>     

                    <div class="col-md-12">
                        <p class="helper">@lang('admin.custom.or_sign') <a href="{{route('login')}}">@lang('admin.auth.sign_in')</a> @lang('admin.custom.user_accowit')</p>   
                    </div>

                </div>


                <div class="log-copy"><p class="no-margin">{{ config('constants.site_copyright', '&copy; '.date('Y').' Appoets') }}</p></div>
                </div>
            </div>
        </div>
    </div>
@endsection
