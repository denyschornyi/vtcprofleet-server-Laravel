@extends('admin.layout.auth')

<!-- Main Content -->
@section('content')
<div class="sign-form">
    <div class="row">
        <div class="col-md-4 offset-md-4 px-3">
            <div class="box b-a-0">
                <div class="p-2 text-xs-center">
                    <h5>@lang('admin.auth.reset_password')</h5>
                </div>
                <form class="form-material mb-1" role="form" method="POST" action="{{ url('/admin/password/email') }}" >
                {{ csrf_field() }}
                    <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                        <input type="email" name="email" value="{{ old('email') }}" required="true" class="form-control" id="email" placeholder="@lang('admin.email')">
                        @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="px-2 form-group mb-0">
                        <button type="submit" class="btn btn-purple btn-block text-uppercase">@lang('admin.auth.send_password_reset_link')</button>
                    </div>
                </form>
                <div class="p-2 text-xs-center text-muted">
                    <a class="text-black" href="{{ url('/admin/login') }}"><span class="underline">@lang('admin.auth.login_here')!</span></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
