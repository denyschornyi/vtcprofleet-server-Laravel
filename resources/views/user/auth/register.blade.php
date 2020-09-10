@extends('user.layout.auth')

@section('content')

<?php $login_user = asset('asset/img/login-user-bg.jpg'); ?>
<div class="full-page-bg" style="background-image: url({{$login_user}});">
<div class="log-overlay"></div>
    <div class="full-page-bg-inner">
        <div class="row no-margin">
            <div class="col-md-6 log-left">
                <span class="login-logo"><img src="{{ config('constants.site_logo', asset('logo-black.png'))}}"></span>
                <h2>@lang('admin.custom.user_text_a')</h2>
                <p>@lang('admin.custom.user_text_b') {{config('constants.site_title','Tranxit')}}, @lang('admin.custom.user_text_c')</p>
            </div>
            <div class="col-md-6 log-right">
                <div class="login-box-outer">
                <div class="login-box row no-margin">
                    <div class="col-md-12">
                        <a class="log-blk-btn" href="{{url('login')}}">@lang('admin.custom.user_new')</a>
                        <h3>@lang('admin.custom.user_have')</h3>
                    </div>
                    <form role="form" method="POST" action="{{ url('/register') }}">

                        <div id="first_step" >
                            <div class="col-md-4">
                                <input value="+33" type="text" placeholder="+33" id="country_code" name="country_code" />
                            </div> 
                            
                            <div class="col-md-8">
                                <input type="text" autofocus id="phone_number" class="form-control" placeholder="@lang('admin.fleets.mobile')" name="phone_number" value="{{ old('phone_number') }}" data-stripe="number" maxlength="10" onkeypress="return isNumberKey(event);" />
                            </div>

                            <div class="col-md-8">
                                @if ($errors->has('phone_number'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('phone_number') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-12" style="padding-bottom: 10px;" id="mobile_verfication"></div>
                            <div class="col-md-12" style="padding-bottom: 10px;">
                                <input type="button" class="log-teal-btn small verify_btn" onclick="smsLogin();" value="@lang('admin.custom.user_verify')"/>
                            </div>

                        </div>

                        {{ csrf_field() }}

                        <div id="second_step" style="display: none;">

                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="@lang('admin.fleets.first_name')" name="first_name" value="{{ old('first_name') }}" data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="@lang('admin.custom.user_login')">

                                @if ($errors->has('first_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('first_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="@lang('admin.fleets.mast_name')" name="last_name" value="{{ old('last_name') }}" data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="@lang('admin.custom.last_num')">
                                @if ($errors->has('last_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <input type="email" class="form-control" name="email" placeholder="@lang('admin.user-pro.email')" value="{{ old('email') }}" data-validation="email">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif                        
                            </div>

                            <div class="col-md-12">
                                <label class="radio-inline"><input type="radio" name="gender" value="MALE" data-validation="required" data-validation-error-msg="@lang('admin.custom.gender')">&nbsp;&nbsp;&nbsp; @lang('admin.custom.male')</label>
                                <label class="radio-inline"><input type="radio" name="gender"  value="FEMALE">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; @lang('admin.custom.female')</label>
                                @if ($errors->has('gender'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gender') }}</strong>
                                    </span>
                                @endif                        
                            </div>

                            <div class="col-md-12">
                                <input type="password" class="form-control" name="password" placeholder="@lang('admin.fleet.password')" data-validation="length" data-validation-length="min6" data-validation-error-msg="@lang('admin.custom.six')">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-12">
                                <input type="password" placeholder="@lang('admin.fleet.password_confirmation')" class="form-control" name="password_confirmation" data-validation="confirmation" data-validation-confirm="password" data-validation-error-msg="@lang('admin.custom.notmatch')">
                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                            @if(config('constants.referral') == 1)
                            <div class="col-md-12">
                                <input type="text" placeholder="@lang('admin.custom.refop')" class="form-control" name="referral_code" >

                                @if ($errors->has('referral_code'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('referral_code') }}</strong>
                                    </span>
                                @endif
                            </div>
                            @else
                                <input type="hidden" name="referral_code" >
                            @endif
                            
                            <div class="col-md-12">
                                <label class="radio-inline" style="padding-left:0"><input style="margin-right:10px;" type="checkbox" class="form-control" name="company_user" value="COMPANY" >@lang('admin.custom.comp')</label>
                            </div>

                            <div class="col-md-12">
                                <button class="log-teal-btn" type="submit">@lang('admin.custom.register')</button>
                            </div>

                        </div>

                    </form>     

                    <div class="col-md-12">
                        <p class="helper">@lang('admin.custom.or_sign')<a href="{{route('login')}}">@lang('admin.auth.sign_in')</a>@lang('admin.custom.user_accowit')</p>   
                    </div>

                </div>

                <div class="log-copy"><p class="no-margin">{{ config('constants.site_copyright', '&copy; '.date('Y').' Appoets') }}</p></div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
<script type="text/javascript">
    @if (count($errors) > 0)
        $("#second_step").show();
    @endif
    $.validate({
        modules : 'security',
    });
    $('.checkbox-inline').on('change', function() {
        $('.checkbox-inline').not(this).prop('checked', false);  
    });
    function isNumberKey(evt)
    {
        var edValue = document.getElementById("phone_number");
        var s = edValue.value;
        if (event.keyCode == 13) {
            event.preventDefault();
            if(s.length>=10){
                smsLogin();
            }
        }
        var charCode = (evt.which) ? evt.which : event.keyCode;
        if (charCode != 46 && charCode > 31 
        && (charCode < 48 || charCode > 57))
            return false;

        return true;
    }
</script>
<script src="https://sdk.accountkit.com/en_US/sdk.js"></script>
<script>
  // initialize Account Kit with CSRF protection
  AccountKit_OnInteractive = function(){
    AccountKit.init(
      {
        appId: {{Config::get('constants.facebook_app_id')}}, 
        state:"state", 
        version: "{{Config::get('constants.facebook_app_version')}}",
        fbAppEventsEnabled:true
      }
    );
  };

  // login callback
  function loginCallback(response) {
    if (response.status === "PARTIALLY_AUTHENTICATED") {
      var code = response.code;
      var csrf = response.state;
      // Send code to server to exchange for access token
      $('#mobile_verfication').html("<p class='helper'> * Phone Number Verified </p>");
      $('#phone_number').attr('readonly',true);
      $('#country_code').attr('readonly',true);
      $('#second_step').fadeIn(400);
      $('.verify_btn').hide();

      $.post("{{route('account.kit')}}",{ code : code }, function(data){
        $('#phone_number').val(data.phone.national_number);
        $('#country_code').val('+'+data.phone.country_prefix);
      });

    }
    else if (response.status === "NOT_AUTHENTICATED") {
      // handle authentication failure
      $('#mobile_verfication').html("<p class='helper'> * Authentication Failed </p>");
    }
    else if (response.status === "BAD_PARAMS") {
      // handle bad parameters
    }
  }

  // phone form submission handler
  function smsLogin() {
    var countryCode = document.getElementById("country_code").value;
    var phoneNumber = document.getElementById("phone_number").value;

    $.post("{{url('/user/verify-credentials')}}",{ _token: '{{csrf_token()}}', mobile : countryCode+phoneNumber }).done(function(data){ 


        $('#mobile_verfication').html("<p class='helper'> Please Wait... </p>");
        //$('#phone_number').attr('readonly',true);
        //$('#country_code').attr('readonly',true);

        AccountKit.login(
          'PHONE', 
          {countryCode: countryCode, phoneNumber: phoneNumber}, // will use default values if not specified
          loginCallback
        );

    })
    .fail(function(xhr, status, error) {
        $('#mobile_verfication').html("<p class='helper'> "+xhr.responseJSON.message+" </p>");
    });



    
  }

</script>

@endsection
