@extends('dispatcher.layout.dispatcher_base')

@section("title") Dashboard - Dashboard
@stop
@section("css")

@stop
@section('content')
    <div class="content-body">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Change Password</h4>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{route('dispatcher.password.update')}}" method="POST" role="form"
                      novalidate>
                    {{csrf_field()}}
                    <div class="col-md-8 col-12">
                        <div class="form-group">
                            <label for="first-name-vertical">@lang('admin.account.old_password')</label>
                            <div class="controls">
                                <input type="password" name="old_password" class="form-control" name="fname"
                                       placeholder="@lang('admin.account.old_password')" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 col-12">
                        <div class="form-group">
                            <label for="first-name-vertical">@lang('admin.account.new_password')</label>
                            <div class="controls">
                                <input type="password" name="password" class="form-control" name="fname"
                                       placeholder="@lang('admin.account.new_password')" required
                                       data-validation-required-message="The password field is required">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 col-12">
                        <div class="form-group">
                            <label for="first-name-vertical">@lang('admin.account.retype_password')</label>
                            <div class="controls">
                                <input type="password" name="password_confirmation" class="form-control"
                                       name="password_confirmation"
                                       placeholder="@lang('admin.account.retype_password')"
                                       required data-validation-match-match="password"
                                       data-validation-required-message="The Confirm password field is required">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary ml-1">@lang('admin.account.change_password')</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@stop
@section('script')
@endsection








