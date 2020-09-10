@extends('provider.layout.app')

@section('contentasdf')
<div class="pro-dashboard-head">
    <div class="container">
        <a href="#" class="pro-head-link active">@lang('provider.profile.profile')</a>
        <a href="{{ route('provider.documents.index') }}" class="pro-head-link">@lang('provider.profile.manage_documents')</a>
        <a href="{{ route('provider.location.index') }}" class="pro-head-link">@lang('provider.profile.update_location')</a>
        <a href="{{route('provider.wallet.transation')}}" class="pro-head-link">@lang('provider.profile.wallet_transaction')</a>
        @if(config('constants.card')==1)
        <a href="{{ route('provider.cards') }}" class="pro-head-link">@lang('provider.card.list')</a>
        @endif
        <a href="{{ route('provider.transfer') }}" class="pro-head-link">@lang('provider.profile.transfer')</a>
        @if(config('constants.referral')==1)
        <a href="{{ route('provider.referral') }}" class="pro-head-link">@lang('provider.profile.refer_friend')</a>
        @endif
    </div>
</div>
@endsection
@section('content')


<div class="add-client">
    <form action="{{route('provider.profile.update')}}" method="POST" enctype="multipart/form-data" role="form">
    {{csrf_field()}}
        <div class="row">
            <div class="col-md-4 upload-profile-pic">
                <div class="upload-pro-pic">
                    <div class="img-uploadwrap">
                        <img src="{{ Auth::guard('provider')->user()->avatar ? asset('storage/'.Auth::guard('provider')->user()->avatar) : asset('asset/img/provider.jpg') }}" class="img-fluid" id="blah" />
                    </div>
                    <div class="input-btn">
                        +
                        <input type="file" name="avatar" data-clientid="1" class="pro-pic" id="imgInp" />
                    </div>
                    <h4>Change profile image</h4>
                    <div style="display:none;" class="imageupates col-md-12">
                        <p class='alert alert-success'><i class='fa fa-check'></i> Profile Picture Updated</p>
                    </div>
                    <div style="display:none;" class="imageupatesfail col-md-12">
                        <p class='alert alert-danger'><i class='fa fa-close'></i> Image formate not Supported or image is too big</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8 user-info">
                <div class="row">
                <div class="col-md-12">
                    <h3 class="prof-name">{{ Auth::guard('provider')->user()->first_name }} {{ Auth::guard('provider')->user()->last_name }}</h3>
                    <p class="board-badge">{{ strtoupper(Auth::guard('provider')->user()->status) }}</p>
                </div>
                </div>
                <br><br>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('provider.profile.first_name')</label></div>
                                <input type="text" style="height:55px!important" name="first_name" class="form-control" placeholder="@lang('provider.profile.first_name')" required value="{{ Auth::guard('provider')->user()->first_name }}" data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="@lang('provider.profile.first_name') @lang('provider.profile.error_msg')">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('provider.profile.phone')</label></div>
                                <input type="text"  style="height:55px!important" class="form-control" id="phone_number" required placeholder="Contact Number" name="mobile" value="{{ Auth::guard('provider')->user()->mobile }}" data-validation="custom length" data-validation-length="10-15" data-validation-regexp="^([0-9\+]+)$" data-validation-error-msg="@lang('provider.profile.error_phone')" disabled="disabled">
                                <div id="phone_number_container" style="display: none;">
                                    <div class="prof-sub-col col-sm-3 no-left-padding">
                                        <input type="text" class="form-control col-sm-2"  style="height:55px!important" name="country_code" value="" placeholder="+91">
                                    </div>
                                    <div class="prof-sub-col col-sm-9 no-left-padding no-right-padding">
                                        <input type="text" class="form-control col-sm-2"  style="height:55px!important" name="phone_number" value="">
                                    </div>
                                </div>
                                <div id="mobile_verfication"></div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('provider.profile.language')</label></div>
                                @php($language=get_all_language())
                                <select class="form-control" name="language" id="language" style="height:55px !important; border-radius:5px!important">
                                    @if(Auth::guard('provider')->user()->profile)
                                    @foreach($language as $lkey=>$lang)
                                    <option value="{{$lkey}}" @if(Auth::guard('provider')->user()->profile->language==$lkey) selected @endif>{{$lang}}</option>
                                    @endforeach
                                    @else
                                    @foreach($language as $lkey=>$lang)
                                    <option value="{{$lkey}}">{{$lang}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('provider.profile.car_number')</label></div>
                                <input type="text" class="form-control"  style="height:55px!important" placeholder="@lang('provider.profile.car_number')" name="service_number" value="{{ Auth::guard('provider')->user()->service->service_number ? Auth::guard('provider')->user()->service->service_number : "" }}" data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="@lang('provider.profile.car_number') @lang('provider.profile.error_msg')">
                            </div>
                        </div>
                    </div>



                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('provider.profile.last_name')</label></div>
                                <input type="text" name="last_name"  style="height:55px!important" placeholder="@lang('provider.profile.last_name')" class="form-control" required value="{{ Auth::guard('provider')->user()->last_name }}" data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="@lang('provider.profile.last_name')">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12 passfieldcont">
                                <a class="btn btn-block btn-primary update-link update-mobile" style="margin-top: 25px; padding:20px; height:55px">@lang('provider.profile.change_mobile')</a>
                                <a class="btn btn-block btn-primary update-link verify-mobile" style="margin-top: 25px; padding:20px; height:55px; display: none;">@lang('provider.profile.verify')</a>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('provider.profile.service_type')</label></div>
                                <select class="form-control" name="service_type" data-validation="required" style="height:55px !important; border-radius:5px!important">
                                    <option value="">Select Service</option>
                                    @foreach(get_all_service_types() as $type)
                                    <option @if(Auth::guard('provider')->user()->service->service_type->id == $type->id) selected="selected" @endif value="{{$type->id}}">{{$type->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('provider.profile.car_model')</label></div>
                                <input type="text" placeholder="@lang('provider.profile.car_model')"  style="height:55px!important" class="form-control" name="service_model" value="{{ Auth::guard('provider')->user()->service->service_model ? Auth::guard('provider')->user()->service->service_model : "" }}" data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="@lang('provider.profile.car_model') @lang('provider.profile.error_msg')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('provider.profile.address')</label></div>
                                <input type="text" class="form-control"  style="height:55px!important" placeholder="@lang('provider.profile.address')" name="address" value="{{ Auth::guard('provider')->user()->profile ? Auth::guard('provider')->user()->profile->address : "" }}">
                                <input type="text" class="form-control"  style="height:55px!important" placeholder="@lang('provider.profile.full_address')" style="border-top: none;" name="address_secondary" value="{{ Auth::guard('provider')->user()->profile ? Auth::guard('provider')->user()->profile->address_secondary : "" }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('provider.profile.qr_code')</label></div>
                                <img src="{{asset(Auth::guard('provider')->user()->qrcode_url)}}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 submit-btnal">
                        <div class="form-group row">
                            <input class="bigbutton" style="width:200px" value="@lang('provider.profile.update')" name="add-client" type="submit" />
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

    $.post("{{url('/provider/verify-credentials')}}",{ _token: '{{csrf_token()}}', id : '{{ Auth::guard('provider')->user()->id }}', mobile : countryCode+phoneNumber }).done(function(data){
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
