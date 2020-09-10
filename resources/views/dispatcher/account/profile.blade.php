@extends('dispatcher.layout.dispatcher_base')

@section("title") Dashboard - Dashboard
@stop
@section("css")
    <style>
        .upload-pro-pic .img-uploadwrap {
            width: 260px;
            height: 260px;
            overflow: hidden;
            border-radius: 50%;
            position: relative;
            margin: 0 auto 20px;
            border: 5px solid #fff;

        }

        .pro-pic {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            opacity: 0;
        }

        .add-client .upload-profile-pic {
            padding-top: 60px;
            text-align: center;
        }

        .upload-pro-pic {
            padding-bottom: 60px;
        }

        .upload-pro-pic .input-btn {
            display: inline-block;
            color: #fff;
            padding: 0 5px;
            margin: 0 auto;
            position: relative;
            cursor: pointer;
            width: 60px;
            height: 60px;
            line-height: 58px;
            font-size: 30px;
            border-radius: 50%;
            background: #2596fd;
            top: -52px;
        }

        .upload-pro-pic h4 {
            color: #636363;
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 18px;
            margin-top: -30px;
        }

    </style>
@stop
@section('content')
    <div class="content-body">
        <section id="column-selectors">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1 col-md-6 pl-0"> Account Settings</h4>
                        </div>
                        <div class="card-content">
                            <div class="add-client">
                                <form class="form-vertical" action="{{route('admin.profile.update')}}" method="POST"
                                      enctype="multipart/form-data" role="form" novalidate>
                                    {{csrf_field()}}
                                    <div class="row">
                                        <div class="col-md-4 upload-profile-pic">
                                            <div class="upload-pro-pic">
                                                <div class="img-uploadwrap">
                                                    <img src="{{ Auth::guard('admin')->user()->picture ? asset('storage/'.Auth::guard('admin')->user()->picture) : asset('asset/img/provider.jpg') }}"
                                                         class="img-fluid" id="blah"/>
                                                </div>
                                                <div class="input-btn">
                                                    +
                                                    <input type="file" name="picture" data-clientid="1" class="pro-pic" id="imgInp"
                                                           accept="image/*"/>
                                                </div>
                                                <h4>@lang('admin.account.Proima')</h4>
                                                <div style="display:none;" class="imageupates col-md-12">
                                                    <p class='alert alert-success'><i class='fa fa-check'></i>@lang('admin.account.Proup')
                                                    </p>
                                                </div>
                                                <div style="display:none;" class="imageupatesfail col-md-12">
                                                    <p class='alert alert-danger'><i class='fa fa-close'></i>@lang('admin.account.Protoo')
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 user-info">
                                            <div class="row">
                                                <div class="col-sm-5 col-12">
                                                    <div class="form-group">
                                                        <label for="first-name-vertical">@lang('admin.name')</label>
                                                        <div class="controls">
                                                            <input type="text" id="first-name-vertical" class="form-control"
                                                                   value="{{ Auth::guard('admin')->user()->name }}" name="name"
                                                                   placeholder=" @lang('admin.name')" required
                                                                   data-validation-required-message="The name field is required">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5 col-12">
                                                    <div class="form-group">
                                                        <label for="first-name-vertical">@lang('admin.email')</label>
                                                        <div class="controls">
                                                            <input type="email" id="first-name-vertical" class="form-control form-control-lg"
                                                                   value="{{ isset(Auth::guard('admin')->user()->email) ? Auth::guard('admin')->user()->email : '' }}" name="email"
                                                                   placeholder=" @lang('admin.email')" required
                                                                   data-validation-required-message="The email field is required">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5 col-12">
                                                    <div class="form-group">
                                                        <label for="first-name-vertical">@lang('admin.account.Adrrs')</label>
                                                        <div class="controls">
                                                            <input type="text" id="first-name-vertical" class="form-control"
                                                                   value="{{ Auth::guard('admin')->user()->address }}" name="address"
                                                                   placeholder=" @lang('admin.account.Adrrs')">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5 col-12">
                                                    <div class="form-group">
                                                        <label for="first-name-vertical">@lang('admin.account.Zcoo')</label>
                                                        <div class="controls">
                                                            <input type="text" id="first-name-vertical" class="form-control"
                                                                   value="{{ isset(Auth::guard('admin')->user()->zip_code) ? Auth::guard('admin')->user()->zip_code : '' }}" name="zip_code"
                                                                   placeholder="@lang('admin.account.Zcoo')">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-5 col-12">
                                                    <div class="form-group">
                                                        <label for="first-name-vertical">@lang('admin.account.Cytt')</label>
                                                        <div class="controls">
                                                            <input type="text" id="first-name-vertical" class="form-control"
                                                                   value="{{ Auth::guard('admin')->user()->city }}" name="city"
                                                                   placeholder="@lang('admin.account.Cytt')">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-5 col-12">
                                                    <div class="form-group">
                                                        <label for="first-name-vertical">@lang('admin.account.Countss')</label>
                                                        <div class="controls">
                                                            <input type="text" id="first-name-vertical" class="form-control"
                                                                   value="{{ isset(Auth::guard('admin')->user()->country) ? Auth::guard('admin')->user()->country : '' }}" name="country"
                                                                   placeholder="@lang('admin.account.Countss')">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5 col-12">
                                                    <div class="form-group">
                                                        <label for="first-name-vertical">@lang('admin.account.Countss')</label>
                                                        <div class="controls">
                                                            <input type="text" id="first-name-vertical" class="form-control"
                                                                   value="{{ isset(Auth::guard('admin')->user()->note) ? Auth::guard('admin')->user()->note : '' }}" name="note"
                                                                   placeholder="Note">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-5 col-12">
                                                    <div class="form-group">
                                                        <div class="field-label"><label>@lang('user.profile.language')</label></div>
                                                        @php($language=get_all_language())
                                                        <select class="form-control" id="basicSelect" name="language">
                                                            @foreach($language as $lkey=>$lang)
                                                                <option value="{{$lkey}}"
                                                                        @if(Auth::user()->language==$lkey) selected @endif>{{$lang}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="col-md-12 col-sm-12 text-center">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn bg-purple text-center waves-effect waves-light text-white font-medium-3 mt-2" name="add-client"
                                                                value="@lang('admin.account.update_profile')">Update Profile
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@stop
@section('script')
    <script>
        $('#imgInp').change(function (event) {
            console.log(2);
            var tmppath = URL.createObjectURL(event.target.files[0]);
            $(".upload-pro-pic .img-uploadwrap img").attr('src', URL.createObjectURL(event.target.files[0]));
            $(".upload-pro-pic .img-uploadwrap img").css('opacity', '0.4');
            var formData = new FormData();
            formData.append('pro-pic', $(this)[0].files[0]);
            formData.append('editClient', $(this).data('clientid'));
        });
    </script>

@endsection





