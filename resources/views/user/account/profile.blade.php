@extends('user.layout.base')

@section('title')

@section('content')

<h2 class="page-title">@lang('user.profile.general_information')</h2>

<div class="add-client">
    <form action="{{url('profile')}}" method="post" enctype="multipart/form-data">
    {{csrf_field()}}

        <div class="row">
            <div class="col-md-4 upload-profile-pic">
                <div class="upload-pro-pic">
                    <div class="img-uploadwrap">
                        <img src="{{img(Auth::user()->picture)}}" class="img-fluid" id="blah" />
                    </div>
                    <div class="input-btn">
                        +
                        <input type="file" name="picture" data-clientid="1" class="pro-pic" id="imgInp" />
                    </div>
                    <h4>@lang('admin.custom.fleet_image')</h4>
                    <div style="display:none;" class="imageupates col-md-12">
                        <p class='alert alert-success'><i class='fa fa-check'></i> @lang('admin.custom.fleet_picture')</p>
                    </div>
                    <div style="display:none;" class="imageupatesfail col-md-12">
                        <p class='alert alert-danger'><i class='fa fa-close'></i> @lang('admin.account.Protoo')</p>
                    </div>
                </div>
            </div>

            <div class="col-md-8 user-info">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="firstName">@lang('user.profile.first_name')</label></div>
                                <input  type="text" name="first_name" style="height:55px !important;"   class="form-control" required="" value="{{Auth::user()->first_name}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                            <label>@lang('user.profile.mobile')</label>
                            <input type="text" class="form-control" id="phone_number"  style="height:55px !important;" required placeholder="@lang('user.profile.mobile')" name="mobile" value="{{ Auth::user()->mobile }}" data-validation="custom length" data-validation-length="10-15" data-validation-regexp="^([0-9\+]+)$" data-validation-error-msg="Incorrect phone number" disabled="disabled">
                            <div id="phone_number_container" style="display: none;">
                                <div class="prof-sub-col col-sm-3 no-left-padding">
                                <input type="text" class="form-control col-sm-2"  style="height:55px !important;" name="country_code" value="" placeholder="+33" >
                                </div>
                                <div class="prof-sub-col col-sm-9 no-left-padding">
                                <input type="text" class="form-control col-sm-2"  style="height:55px !important;" name="phone_number" value="" >
                                </div>
                            </div>
                            <div id="mobile_verfication"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12 passfieldcont">
                                <div class="field-label"><label for="email">@lang('user.profile.email')</label></div>
                                <input disabled type="email" name=""  style="height:55px !important;" class="form-control" required="" value="{{Auth::user()->email}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="phone">@lang('user.profile.wallet_balance')</label></div>
                                <input disabled type="text" name="phone"  style="height:55px !important;" class="form-control" value="{{currency(Auth::user()->wallet_balance)}}">

                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="">@lang('user.profile.country_code')</label></div>
                                <input disabled type="text" name=""  style="height:55px !important;" class="form-control" value="{{Auth::user()->country_code}}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="">@lang('user.profile.last_name')</label></div>
                                <input  type="text" name="last_name"  style="height:55px !important;" class="form-control" required="" value="{{Auth::user()->last_name}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12 passfieldcont">
                                <a class="btn btn-block btn-primary update-link update-mobile" style="margin-top: 20px; padding:20px; height:55px">@lang('user.profile.change_mobile')</a>
                                <a class="btn btn-block btn-primary update-link verify-mobile" style="margin-top: 20px; padding:20px; height:55px; display: none;">@lang('user.profile.verify')</a>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="website">@lang('user.profile.language')</label></div>
                                @php($language=get_all_language())
                                <select class="form-control" name="language" id="language" style="height:55px!important;border-radius:5px!important; padding:0;">
                                    @foreach($language as $lkey=>$lang)
                                        <option value="{{$lkey}}" @if(Auth::user()->language==$lkey) selected @endif>{{$lang}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="phone">@lang('user.profile.company_name')</label></div>
                                <input type="text" name="company_name"  style="height:55px !important;" class="form-control" value="{{Auth::user()->company_name}}">

                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="Facebook">@lang('user.profile.qr_code')</label></div>
                                <img src="{{asset(Auth::user()->qrcode_url)}}" width="114" height="114">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 submit-btnal">
                        <div class="form-group row">
                            <input class="bigbutton" value="@lang('provider.profile.update')" style="width:200px" name="add-client" type="submit" />
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </form>
</div>

@endsection
@section('scripts')
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
<script type="text/javascript">
    $.validate();
</script>
<script src="https://sdk.accountkit.com/en_US/sdk.js"></script>
<script>

  $('.update-mobile').on('click', function() {
    smsLogin();
  });

  $('.verify-mobile').on('click', function() {
    verify();
  });

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

      $.post("{{route('account.kit')}}",{ code : code }, function(data){
        $('#phone_number').attr('readonly',true);
        $('#phone_number').attr('disabled',false);
        $('#phone_number').val('+'+data.phone.country_prefix+data.phone.national_number);
        $('.verify-mobile').text("@lang('user.profile.verified')");
        $('.verify-mobile').removeClass('verify-mobile');

        $('#phone_number_container').hide();
        $('#phone_number').show();
        $('#phone_number').attr('disabled',false);
        $('#mobile_verfication').html("");
        //console.log(data);
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
    $('#phone_number_container').show();
    $('#phone_number').hide();
    $('.update-mobile').hide();
    $('.verify-mobile').show();
  }

  function verify() {
    $('#phone_number').attr('disabled',false);
    $('.update-mobile').text("@lang('provider.profile.verify')");

    var countryCode = $('input[name=country_code]').val();
    var phoneNumber = $('input[name=phone_number]').val();

    $.post("{{url('/user/verify-credentials')}}",{ _token: '{{csrf_token()}}', id : '{{ Auth::user()->id }}', mobile : countryCode+phoneNumber }).done(function(data){
        // $('#mobile_verfication').html("<p class='helper'> Please Wait... </p>");

        AccountKit.login(
          'PHONE',
          {countryCode: countryCode, phoneNumber: phoneNumber}, // will use default values if not specified
          loginCallback
        );
    })
    .fail(function(xhr, status, error) {
        $('#mobile_verfication').html("<p class='helper'> "+xhr.responseJSON.message+" </p>");
    });

    /*var countryCode = "+91";
    var phoneNumber = document.getElementById("phone_number").value;*/

    /*$('#mobile_verfication').html("<p class='helper'> Please Wait... </p>");

    */
  }

</script>
@endsection
