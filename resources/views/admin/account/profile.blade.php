@extends('admin.layout.base')

@section('title')

@section('styles')
<style>
</style>
@endsection

@section('content')

<div class="add-client">
<form class="form-horizontal" action="{{route('admin.profile.update')}}" method="POST" enctype="multipart/form-data" role="form">
    {{csrf_field()}}
        <div class="row">
            <div class="col-md-4 upload-profile-pic">
                <div class="upload-pro-pic">
                    <div class="img-uploadwrap">
                        <img src="{{ Auth::guard('admin')->user()->picture ? asset('storage/'.Auth::guard('admin')->user()->picture) : asset('asset/img/provider.jpg') }}" class="img-fluid" id="blah" />
                    </div>
                    <div class="input-btn">
                        +
                        <input type="file" name="picture" data-clientid="1" class="pro-pic" id="imgInp" accept="image/*"/>
                    </div>
                    <h4>@lang('admin.account.Proima')</h4>
                    <div style="display:none;" class="imageupates col-md-12">
                        <p class='alert alert-success'><i class='fa fa-check'></i>@lang('admin.account.Proup')</p>
                    </div>
                    <div style="display:none;" class="imageupatesfail col-md-12">
                        <p class='alert alert-danger'><i class='fa fa-close'></i>@lang('admin.account.Protoo')</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8 user-info">
                <div class="row">
                <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.name')</label></div>
								<input class="form-control" style="border-radius: 5px;height: 55px !important;" type="text" value="{{ Auth::guard('admin')->user()->name }}" name="name" required id="name" placeholder=" @lang('admin.name')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.email')</label></div>
								<input class="form-control" type="email" style="border-radius: 5px;height: 55px !important;" required name="email" value="{{ isset(Auth::guard('admin')->user()->email) ? Auth::guard('admin')->user()->email : '' }}" id="email" placeholder="@lang('admin.email')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.account.Adrrs')</label></div>
								<input class="form-control" style="border-radius: 5px;height: 55px !important;" type="text" value="{{ Auth::guard('admin')->user()->address }}" name="address" id="address" placeholder="@lang('admin.account.Adrrs')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.account.Zcoo')</label></div>
								<input class="form-control" type="text" style="border-radius: 5px;height: 55px !important;" name="zip_code" value="{{ isset(Auth::guard('admin')->user()->zip_code) ? Auth::guard('admin')->user()->zip_code : '' }}" id="email" placeholder="@lang('admin.account.Zcoo')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.account.Cytt')</label></div>
								<input class="form-control" style="border-radius: 5px;height: 55px !important;" type="text" value="{{ Auth::guard('admin')->user()->city }}" name="city" id="city" placeholder=" @lang('admin.account.Cytt')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.account.Countss')</label></div>
                                <input class="form-control" type="text" style="border-radius: 5px;height: 55px !important;" name="country" value="{{ isset(Auth::guard('admin')->user()->country) ? Auth::guard('admin')->user()->country : '' }}" id="country" placeholder="@lang('admin.account.Countss')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.account.rcs')</label></div>
								<input class="form-control" type="text" style="border-radius: 5px;height: 55px !important;" name="rcs" value="{{ isset(Auth::guard('admin')->user()->rcs) ? Auth::guard('admin')->user()->rcs : '' }}" id="rcs" placeholder="@lang('admin.account.rcs')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.account.siret')</label></div>
                                <input class="form-control" type="text" style="border-radius: 5px;height: 55px !important;" name="siret" value="{{ isset(Auth::guard('admin')->user()->siret) ? Auth::guard('admin')->user()->siret : '' }}" id="siret" placeholder="@lang('admin.account.siret')">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>@lang('admin.account.intracommunautaire')</label></div>
                                <input class="form-control" type="text" style="border-radius: 5px;height: 55px !important;" name="intracommunautaire" value="{{ isset(Auth::guard('admin')->user()->intracommunautaire) ? Auth::guard('admin')->user()->intracommunautaire : '' }}" id="intracommunautaire" placeholder="@lang('admin.account.intracommunautaire')">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="field-label"><label>Note</label></div>
								<input class="form-control" type="text" style="border-radius: 5px;height: 55px !important;" name="note" value="{{ isset(Auth::guard('admin')->user()->note) ? Auth::guard('admin')->user()->note : '' }}" id="note" placeholder="Note">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
						<div class="form-group row">
								<div class="col-md-12">
									<div class="field-label"><label>@lang('user.profile.language')</label></div>
									@php($language=get_all_language())
									<select class="form-control" name="language" id="language" style="border-radius: 5px;height: 55px !important; padding:15px;" >
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
