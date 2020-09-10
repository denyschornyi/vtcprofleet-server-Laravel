@include('admin.newTheme.templates.frontend-header')

<div class="login-area">
    <div class="content container">
        <div class="row">
            <div class="col-sm-5">
                <div class="logo"><a class="signLogo" href="{{url('/admin')}}">
                        <img src="{{asset('newAssets/images/login-logo.png')}}" alt="" />
                    </a></div>
            </div>
            <div class="col-sm-7">
                <form id="login_form"  class="login-form grey" action="{{ url('/admin/login') }}" method="POST">
                    {{ csrf_field() }}
                    <h3 class="form-title grey offset-md-3">Hello and welcome, <br />Please Login</h3>
                    <div class="form-group row">
                        <label class="control-label col-sm-3 padding-right-0">Email Address</label>
                        <div class="input-icon col-sm-8">
                            <input class="form-control placeholder-no-fix logemail" type="email" autocomplete="off" name="email" required/>
                            @if ($errors->has('email'))
                                <p class="help-block text-danger">{{ $errors->first('email') }}</p>
                            @endif
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group row">
                        <label class="control-label col-sm-3 padding-right-0">Password</label>
                        <div class="input-icon col-sm-8 ">
                            <input class="form-control placeholder-no-fix pass" type="password" autocomplete="off" name="password" required/>
                            @if ($errors->has('password'))
                                <p class="help-block text-danger">{{ $errors->first('password') }}</p>
                            @endif
                            <input type="submit" name="submit" class="btn orange" value=" LOG IN ">
                            <div class="row" style="margin:0;">
                                <label class="checkbox col-lg-6">
                                    <input type="checkbox" name="remember" value="1" /> <span></span>Remember me
                                </label>
                                <div class="col-lg-6">
                                    <a href="forgot-password.php" id="forget-password" style="display:none"> Forgot your password?</a>
                                </div>
                            </div>
                            <ul class="user_type">
                                <li @if (!$errors->has('login_type')) class="active" @endif @if ($errors->has('login_type') && $errors->first('login_type') == 'admin')  class="active" @endif tab-name="admin">Admin</li>
                                <li @if ($errors->has('login_type') && $errors->first('login_type') == 'dispatcher')  class="active" @endif tab-name="dispatcher">Dispatcher</li>
                                <li @if ($errors->has('login_type') && $errors->first('login_type') == 'fleet')  class="active" @endif tab-name="fleet">Fleet</li>
                                <li @if ($errors->has('login_type') && $errors->first('login_type') == 'account')  class="active" @endif tab-name="account">Account</li>
                                <li @if ($errors->has('login_type') && $errors->first('login_type') == 'dispute')  class="active" @endif tab-name="dispute">Dispute</li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <input id="login_type" type="hidden" name="login_type" value="admin">

                </form>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div><!-- login-area-->
@include('admin.newTheme.templates.frontend-footer')
<script>
</script>
