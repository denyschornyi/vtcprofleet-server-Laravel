@extends('fleet.layout.base')

@section('title', 'Update Profile ')
@section('styles')
    <link rel="stylesheet" href="{{asset('asset/css/intlTelInput.css')}}">
@endsection
@section('content')

<div class="add-client">
	<form class="form-horizontal" action="{{route('fleet.profile.update')}}" method="POST" enctype="multipart/form-data" role="form">
    {{csrf_field()}}

        <div class="row">
            <div class="col-md-4 upload-profile-pic">
                <div class="upload-pro-pic">
                    <div class="img-uploadwrap">
                        <img src="{{img(Auth::guard('fleet')->user()->logo)}}" class="img-fluid" id="blah" />
                    </div>
                    <div class="input-btn">
                        +
                        <input type="file" name="logo" data-clientid="1" class="pro-pic" id="imgInp" />
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
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="firstName">@lang('admin.name')</label></div>
								<input class="form-control" style="height:55px !important; border-radius:5px" type="text" value="{{ Auth::guard('fleet')->user()->name }}" name="name" required id="name" placeholder="@lang('admin.name')">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="">@lang('admin.company')</label></div>
								<input class="form-control" style="height:55px !important; border-radius:5px"  type="text" required name="company" value="{{ isset(Auth::guard('fleet')->user()->company) ? Auth::guard('fleet')->user()->company : '' }}" id="company" placeholder="@lang('admin.company')">
                            </div>
                        </div>

                    </div>

                    <div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-12">
                                <div class="field-label"><label for="">@lang('admin.email')</label></div>
								<input class="form-control" style="height:55px !important; border-radius:5px"  type="email" required name="email" value="{{ isset(Auth::guard('fleet')->user()->email) ? Auth::guard('fleet')->user()->email : '' }}" id="email" placeholder="@lang('admin.email')">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="">@lang('admin.mobile')</label></div>
								<input class="form-control" style="height:55px !important; border-radius:5px"  type="text" required name="mobile" value="{{ isset(Auth::guard('fleet')->user()->mobile) ? Auth::guard('fleet')->user()->mobile : '' }}" id="mobile" placeholder="@lang('admin.mobile')">
                            </div>
                        </div>
					</div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="">@lang('admin.account.Adrrs')</label></div>
                                <input class="form-control" style="height:55px !important; border-radius:5px"  type="text" required name="address" value="{{ isset(Auth::guard('fleet')->user()->address) ? Auth::guard('fleet')->user()->address : '' }}" id="address" placeholder="@lang('admin.account.Adrrs')">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="">@lang('admin.account.Zcoo')</label></div>
                                <input class="form-control" style="height:55px !important; border-radius:5px"  type="text" required name="zip_code" value="{{ isset(Auth::guard('fleet')->user()->zip_code) ? Auth::guard('fleet')->user()->zip_code : '' }}" id="zip_code" placeholder="@lang('admin.account.Zcoo')">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="">@lang('admin.account.Cytt')</label></div>
                                <input class="form-control" style="height:55px !important; border-radius:5px"  type="text" required name="city" value="{{ isset(Auth::guard('fleet')->user()->city) ? Auth::guard('fleet')->user()->city : '' }}" id="city" placeholder="@lang('admin.account.Cytt')">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="">@lang('admin.account.Countss')</label></div>
                                <input class="form-control" style="height:55px !important; border-radius:5px"  type="text" required name="country_code" value="{{ isset(Auth::guard('fleet')->user()->country_code) ? Auth::guard('fleet')->user()->country_code : '' }}" id="country_code" placeholder="+33">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.account.rcs')</label></div>
                                <input class="form-control" type="text" style="border-radius: 5px;height: 55px !important;" name="rcs" value="{{ isset(Auth::guard('fleet')->user()->rcs) ? Auth::guard('fleet')->user()->rcs : '' }}" id="rcs" placeholder="@lang('admin.account.rcs')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.account.siret')</label></div>
                                <input class="form-control" type="text" style="border-radius: 5px;height: 55px !important;" name="siret" value="{{ isset(Auth::guard('fleet')->user()->siret) ? Auth::guard('fleet')->user()->siret : '' }}" id="siret" placeholder="@lang('admin.account.siret')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.account.intracommunautaire')</label></div>
                                <input class="form-control" type="text" style="border-radius: 5px;height: 55px !important;" name="intracommunautaire" value="{{ isset(Auth::guard('fleet')->user()->intracommunautaire) ? Auth::guard('fleet')->user()->intracommunautaire : '' }}" id="intracommunautaire" placeholder="@lang('admin.account.intracommunautaire')">
                            </div>
                        </div>
                    </div>
					<div class="col-md-6">
					<div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label for="website">@lang('user.profile.language')</label></div>
								@php($language=get_all_language())
								<select class="form-control" name="language" id="language" style="height:55px !important; border-radius:5px; padding:15px !important;" >
									@foreach($language as $lkey=>$lang)
										<option value="{{$lkey}}" @if(Auth::user()->language==$lkey) selected @endif>{{$lang}}</option>
									@endforeach
								</select>
                            </div>
                        </div>
					</div>
                    <div class="col-md-12 submit-btnal">
                        <div class="form-group row">
                            <input class="bigbutton" value="@lang('admin.account.update_profile')" name="add-client" type="submit" />
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
    <script type="text/javascript" src="{{ asset('asset/js/intlTelInput.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/intlTelInput-jquery.min.js') }}"></script>
    <script type="text/javascript">
        //For mobile number with date
        var input = document.querySelector("#country_code");
        window.intlTelInput(input,({
            // separateDialCode:true,
        }));
        $(".country-name").click(function(){
            var myVar = $(this).closest('.country').find(".dial-code").text();
            $('#country_code').val(myVar);
        });
    </script>
@endsection
