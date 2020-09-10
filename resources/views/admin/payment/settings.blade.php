@extends('admin.layout.base')

@section('title')

@section('content')

    <div class="content-area py-1">

        <div class="container-fluid">
            <div class="box box-block bg-white">
                <div class="bd-example bd-example-tabs" role="tabpanel">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="paymentMode-tab" data-toggle="tab" href="#paymentMode"
                               role="tab" aria-controls="paymentMode"
                               aria-expanded="true">@lang('admin.payment.carments')</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " id="paymentSetting-tab" data-toggle="tab" href="#paymentSetting"
                               role="tab" aria-controls="paymentSetting"
                               aria-expanded="false">@lang('admin.payment.caents')</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " id="cardmanage-tab" data-toggle="tab" href="#card_manage"
                               role="tab" aria-controls="card_manage"
                               aria-expanded="false">@lang('admin.payment.add_card')</a>
                        </li>

                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div role="tabpanel" class="tab-pane fade active in" id="paymentMode" aria-labelledby="home-tab"
                             aria-expanded="true">
                            <div class="form-box row">
                                <div class="col-md-9">

                                    <form action="{{route('admin.settings.payment.store')}}" method="POST"
                                          enctype="multipart/form-data">
                                        {{csrf_field()}}

                                        <div class="card card-block card-inverse card-primary">
                                            <blockquote class="card-blockquote">
                                                <i class="fa fa-3x fa-money pull-right"></i>
                                                <div class="form-group row">
                                                    <div class="col-xs-4 arabic_right">
                                                        <label for="cash-payments" class="col-form-label">
                                                            @lang('admin.payment.cash_payments')
                                                        </label>
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <input @if(config('constants.cash') == 1) checked
                                                               @endif autocomplete="off" name="cash" id="cash-payments"
                                                               type="checkbox" class="js-switch" data-color="#43b968">
                                                    </div>
                                                </div>
                                            </blockquote>
                                        </div>
                                        <div class="card card-block card-inverse card-primary">
                                            <blockquote class="card-blockquote">
                                                <i class="fa fa-3x fa-cc-stripe pull-right"></i>
                                                <div class="form-group row">
                                                    <div class="col-xs-4 arabic_right">
                                                        <label for="stripe_secret_key" class="col-form-label">
                                                            @lang('admin.payment.card_payments')
                                                        </label>
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <input @if(config('constants.card') == 1) checked
                                                               @endif autocomplete="off" name="card" id="stripe_check"
                                                               type="checkbox" class="js-switch" data-color="#43b968">
                                                    </div>
                                                </div>
                                                <div class="payment_settings"
                                                     @if(config('constants.card') == 0) style="display: none;" @endif>
                                                    <div class="form-group row">
                                                        <label for="stripe_secret_key"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.stripe_secret_key')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.stripe_secret_key') }}"
                                                                   name="stripe_secret_key" id="stripe_secret_key"
                                                                   placeholder="@lang('admin.payment.stripe_secret_key')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="stripe_publishable_key"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.stripe_publishable_key')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.stripe_publishable_key') }}"
                                                                   name="stripe_publishable_key"
                                                                   id="stripe_publishable_key"
                                                                   placeholder="@lang('admin.payment.stripe_publishable_key')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="stripe_currency"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.currency')</label>
                                                        <div class="col-xs-8">
                                                            <select name="stripe_currency" class="form-control"
                                                                    required>
                                                                <option @if(config('constants.stripe_currency') == "USD") selected
                                                                        @endif value="USD">USD
                                                                </option>
                                                                <option @if(config('constants.stripe_currency') == "EUR") selected
                                                                        @endif value="EUR">EUR
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </blockquote>
                                        </div>

                                        <div class="card card-block card-inverse card-primary">
                                            <blockquote class="card-blockquote">
                                                <!--  <i class="fa fa-3x fa-cc-stripe pull-right"></i> -->
                                                <div class="form-group row">
                                                    <div class="col-xs-4 arabic_right">
                                                        <label for="card_payments" class="col-form-label">
                                                            @lang('admin.payment.payumoney')
                                                        </label>
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <input @if(config('constants.payumoney') == 1) checked
                                                               @endif autocomplete="off" name="payumoney"
                                                               type="checkbox" class="js-switch" data-color="#43b968">
                                                    </div>
                                                    <div class="col-xs-2 payumoney_icon">
                                                        <img src="{{asset('asset/img/payu.png')}}">
                                                    </div>
                                                </div>
                                                <div class="payment_settings"
                                                     @if(config('constants.payumoney') == 0) style="display: none;" @endif>
                                                    <div class="form-group row">
                                                        <label for="payumoney_environment"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.payumoney_environment')</label>
                                                        <div class="col-xs-8">
                                                            <select name="payumoney_environment" class="form-control"
                                                                    required>
                                                                <option @if(config('constants.payumoney_environment') == "test") selected
                                                                        @endif value="test">Development
                                                                </option>
                                                                <option @if(config('constants.payumoney_environment') == "secure") selected
                                                                        @endif value="secure">Production
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="payumoney_merchant_id"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.payumoney_merchant_id')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.payumoney_merchant_id') }}"
                                                                   name="payumoney_merchant_id"
                                                                   id="payumoney_merchant_id"
                                                                   placeholder="@lang('admin.payment.payumoney_merchant_id')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="payumoney_key"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.payumoney_key')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.payumoney_key') }}"
                                                                   name="payumoney_key" id="payumoney_key"
                                                                   placeholder="@lang('admin.payment.payumoney_key')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="payumoney_salt"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.payumoney_salt')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.payumoney_salt')  }}"
                                                                   name="payumoney_salt" id="payumoney_salt"
                                                                   placeholder="@lang('admin.payment.payumoney_salt')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="payumoney_auth"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.payumoney_auth')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.payumoney_auth') }}"
                                                                   name="payumoney_auth" id="payumoney_auth"
                                                                   placeholder="@lang('admin.payment.payumoney_auth')">
                                                        </div>
                                                    </div>
                                                </div>
                                            </blockquote>
                                        </div>

                                        <div class="card card-block card-inverse card-primary">
                                            <blockquote class="card-blockquote">
                                                <i class="fa fa-3x fa-paypal pull-right"></i>
                                                <div class="form-group row">
                                                    <div class="col-xs-4 arabic_right">
                                                        <label for="card_payments" class="col-form-label">
                                                            @lang('admin.payment.paypal')
                                                        </label>
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <input @if(config('constants.paypal') == 1) checked
                                                               @endif  autocomplete="off" name="paypal" type="checkbox"
                                                               class="js-switch" data-color="#43b968">
                                                    </div>
                                                </div>
                                                <div class="payment_settings"
                                                     @if(config('constants.paypal') == 0) style="display: none;" @endif>
                                                    <div class="form-group row">
                                                        <label for="paypal_environment"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paypal_environment')</label>
                                                        <div class="col-xs-8">
                                                            <select name="paypal_environment" class="form-control"
                                                                    required>
                                                                <option @if(config('constants.paypal_environment') == "sandbox") selected
                                                                        @endif value="sandbox">Development
                                                                </option>
                                                                <option @if(config('constants.paypal_environment') == "live") selected
                                                                        @endif value="live">Production
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paypal_client_id"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paypal_client_id')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.paypal_client_id') }}"
                                                                   name="paypal_client_id" id="paypal_client_id"
                                                                   placeholder="@lang('admin.payment.paypal_client_id')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paypal_client_secret"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paypal_client_secret')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.paypal_client_secret')  }}"
                                                                   name="paypal_client_secret" id="paypal_client_secret"
                                                                   placeholder="@lang('admin.payment.paypal_client_secret')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paypal_currency"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.currency')</label>
                                                        <div class="col-xs-8">
                                                            <select name="paypal_currency" class="form-control"
                                                                    required>
                                                                <option @if(config('constants.paypal_currency') == "USD") selected
                                                                        @endif value="USD">USD
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </blockquote>
                                        </div>

                                        <div class="card card-block card-inverse card-primary">
                                            <blockquote class="card-blockquote">
                                                <!--<i class="fa fa-3x fa-paypal pull-right"></i>-->
                                                <div class="form-group row">
                                                    <div class="col-xs-4 arabic_right">
                                                        <label for="card_payments" class="col-form-label">
                                                            @lang('admin.payment.paypal_adaptive')
                                                        </label>
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <input @if(config('constants.paypal_adaptive') == 1) checked
                                                               @endif  autocomplete="off" name="paypal_adaptive"
                                                               type="checkbox" class="js-switch" data-color="#43b968">
                                                    </div>
                                                    <div class="col-xs-2 paypal_adaptive_icon">
                                                        <img src="{{asset('asset/img/adaptation.png')}}">
                                                    </div>
                                                </div>
                                                <div class="payment_settings"
                                                     @if(config('constants.paypal_adaptive') == 0) style="display: none;" @endif>
                                                    <div class="form-group row">
                                                        <label for="paypal_adaptive_environment"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paypal_adaptive_environment')</label>
                                                        <div class="col-xs-8">
                                                            <select name="paypal_adaptive_environment"
                                                                    class="form-control" required>
                                                                <option @if(config('constants.payumoney_environment') == "sandbox") selected
                                                                        @endif value="sandbox">Development
                                                                </option>
                                                                <option @if(config('constants.payumoney_environment') == "live") selected
                                                                        @endif value="live">Production
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paypal_username"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paypal_username')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.paypal_username') }}"
                                                                   name="paypal_username" id="paypal_username"
                                                                   placeholder="@lang('admin.payment.paypal_username')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paypal_password"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paypal_password')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.paypal_password')  }}"
                                                                   name="paypal_password" id="paypal_password"
                                                                   placeholder="@lang('admin.payment.paypal_password')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paypal_secret"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paypal_secret')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.paypal_secret') }}"
                                                                   name="paypal_secret" id="paypal_secret"
                                                                   placeholder="@lang('admin.payment.paypal_secret')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paypal_certificate"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paypal_certificate')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="file"
                                                                   name="paypal_certificate" id="paypal_certificate"
                                                                   placeholder="@lang('admin.payment.paypal_certificate')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paypal_app_id"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paypal_app_id')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.paypal_app_id') }}"
                                                                   name="paypal_app_id" id="paypal_app_id"
                                                                   placeholder="@lang('admin.payment.paypal_app_id')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paypal_adaptive_currency"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.currency')</label>
                                                        <div class="col-xs-8">
                                                            <select name="paypal_adaptive_currency" class="form-control"
                                                                    required>
                                                                <option @if(config('constants.paypal_adaptive_currency') == "USD") selected
                                                                        @endif value="USD">USD
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </blockquote>
                                        </div>

                                        <div class="card card-block card-inverse card-primary">
                                            <blockquote class="card-blockquote">
                                                <!-- <i class="fa fa-3x fa-credit-card pull-right"></i> -->
                                                <div class="form-group row">
                                                    <div class="col-xs-4 arabic_right">
                                                        <label for="card_payments" class="col-form-label">
                                                            @lang('admin.payment.braintree')
                                                        </label>
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <input @if(config('constants.braintree') == 1) checked
                                                               @endif  autocomplete="off" name="braintree"
                                                               type="checkbox" class="js-switch" data-color="#43b968">
                                                    </div>
                                                    <div class="col-xs-2 braintree_icon">
                                                        <img src="{{asset('asset/img/tree-brain.png')}}">
                                                    </div>
                                                </div>
                                                <div class="payment_settings"
                                                     @if(config('constants.braintree') == 0) style="display: none;" @endif>
                                                    <div class="form-group row">
                                                        <label for="braintree_environment"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.braintree_environment')</label>
                                                        <div class="col-xs-8">
                                                            <select name="braintree_environment" class="form-control"
                                                                    required>
                                                                <option @if(config('constants.braintree_environment') == "sandbox") selected
                                                                        @endif value="sandbox">Development
                                                                </option>
                                                                <option @if(config('constants.braintree_environment') == "live") selected
                                                                        @endif value="live">Production
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="braintree_merchant_id"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.braintree_merchant_id')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.braintree_merchant_id')  }}"
                                                                   name="braintree_merchant_id"
                                                                   id="braintree_merchant_id"
                                                                   placeholder="@lang('admin.payment.braintree_merchant_id')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="braintree_public_key"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.braintree_public_key')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.braintree_public_key') }}"
                                                                   name="braintree_public_key" id="braintree_public_key"
                                                                   placeholder="@lang('admin.payment.braintree_public_key')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="braintree_private_key"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.braintree_private_key')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.braintree_private_key') }}"
                                                                   name="braintree_private_key"
                                                                   id="braintree_private_key"
                                                                   placeholder="@lang('admin.payment.braintree_private_key')">
                                                        </div>
                                                    </div>
                                                </div>
                                            </blockquote>
                                        </div>

                                        <div class="card card-block card-inverse card-primary">
                                            <blockquote class="card-blockquote">
                                                <!-- <i class="fa fa-3x fa-credit-card pull-right"></i> -->
                                                <div class="form-group row">
                                                    <div class="col-xs-4 arabic_right">
                                                        <label for="card_payments" class="col-form-label">
                                                            @lang('admin.payment.paytm')
                                                        </label>
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <input @if(config('constants.paytm') == 1) checked
                                                               @endif  autocomplete="off" name="paytm" type="checkbox"
                                                               class="js-switch" data-color="#43b968">
                                                    </div>
                                                    <div class="col-xs-2 braintree_icon">
                                                        <img width="110" src="{{asset('asset/img/paytm-logo.png')}}">
                                                    </div>
                                                </div>
                                                <div class="payment_settings"
                                                     @if(config('constants.paytm') == 0) style="display: none;" @endif>
                                                    <div class="form-group row">
                                                        <label for="paytm_environment"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paytm_environment')</label>
                                                        <div class="col-xs-8">
                                                            <select name="paytm_environment" class="form-control"
                                                                    required>
                                                                <option @if(config('constants.paytm_environment') == "local") selected
                                                                        @endif value="local">Development
                                                                </option>
                                                                <option @if(config('constants.paytm_environment') == "production") selected
                                                                        @endif value="production">Production
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paytm_merchant_id"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paytm_merchant_id')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.paytm_merchant_id')  }}"
                                                                   name="paytm_merchant_id" id="paytm_merchant_id"
                                                                   placeholder="@lang('admin.payment.paytm_merchant_id')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paytm_merchant_key"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paytm_merchant_key')</label>
                                                        <div class="col-xs-8">
                                                            <input class="form-control" type="text"
                                                                   value="{{ config('constants.paytm_merchant_key') }}"
                                                                   name="paytm_merchant_key" id="paytm_merchant_key"
                                                                   placeholder="@lang('admin.payment.paytm_merchant_key')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="paytm_website"
                                                               class="col-xs-4 col-form-label">@lang('admin.payment.paytm_website')</label>
                                                        <div class="col-xs-8">
                                                            <select name="paytm_website" class="form-control" required>
                                                                <option @if(config('constants.paytm_website') == "WEBSTAGING") selected
                                                                        @endif value="WEBSTAGING">Staging
                                                                </option>
                                                                <option @if(config('constants.paytm_website') == "WEBPROD") selected
                                                                        @endif value="WEBPROD">Production
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </blockquote>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-xs-4">
                                                <a href="{{ route('admin.index') }}"
                                                   class="btn btn-warning btn-block">@lang('admin.back')</a>
                                            </div>
                                            <div class="offset-xs-4 col-xs-4">
                                                <button type="submit"
                                                        class="btn btn-primary btn-block">@lang('admin.payment.update_site_settings')</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade " id="paymentSetting" role="tabpanel"
                             aria-labelledby="paymentSetting-tab" aria-expanded="false">
                            <div class="form-box row">
                                <div class="col-md-8">
                                    <form action="{{route('admin.settings.payment.store')}}" method="POST"
                                          enctype="multipart/form-data">
                                        {{csrf_field()}}
                                        <div class="card card-block card-inverse card-info">
                                            <blockquote class="card-blockquote">
                                                <div class="form-group row">
                                                    <label for="daily_target"
                                                           class="col-xs-4 col-form-label">@lang('admin.payment.daily_target')</label>
                                                    <div class="col-xs-8">
                                                        <input class="form-control"
                                                               type="number"
                                                               value="{{ config('constants.daily_target', '0')  }}"
                                                               id="daily_target"
                                                               name="daily_target"
                                                               min="0"
                                                               required
                                                               placeholder="Daily Target">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="tax_percentage"
                                                           class="col-xs-4 col-form-label">@lang('admin.payment.tax_percentage')</label>
                                                    <div class="col-xs-8">
                                                        <input class="form-control"
                                                               type="number"
                                                               value="{{ config('constants.tax_percentage', '0')  }}"
                                                               id="tax_percentage"
                                                               name="tax_percentage"
                                                               min="0"
                                                               max="100"
                                                               placeholder="Tax Percentage">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="commission_percentage"
                                                           class="col-xs-4 col-form-label">@lang('admin.payment.commission_percentage')</label>
                                                    <div class="col-xs-8">
                                                        <input class="form-control"
                                                               type="number"
                                                               value="{{ config('constants.commission_percentage', '0') }}"
                                                               id="commission_percentage"
                                                               name="commission_percentage"
                                                               min="0"
                                                               max="100"
                                                               placeholder="@lang('admin.payment.commission_percentage')">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="admin_commission_percentage"
                                                           class="col-xs-4 col-form-label">@lang('admin.payment.admin_commission_percentage')</label>
                                                    <div class="col-xs-8">
                                                        <input class="form-control"
                                                               type="number"
                                                               value="{{ config('constants.admin_commission_percentage', '0') }}"
                                                               id="admin_commission_percentage"
                                                               name="admin_commission_percentage"
                                                               min="0"
                                                               max="100"
                                                               placeholder="@lang('admin.payment.admin_commission_percentage')">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="pool_commission_percentage"
                                                           class="col-xs-4 col-form-label">@lang('admin.payment.pool_commission_percentage')</label>
                                                    <div class="col-xs-8">
                                                        <input class="form-control"
                                                               type="number"
                                                               value="{{ config('constants.pool_commission_percentage', '0') }}"
                                                               id="pool_commission_percentage"
                                                               name="pool_commission_percentage"
                                                               min="0"
                                                               max="100"
                                                               placeholder="@lang('admin.payment.pool_commission_percentage')">
                                                    </div>
                                                </div>

                                                {{-- <div class="form-group row">
                                                    <label for="fleet_commission_percentage"
                                                           class="col-xs-4 col-form-label">@lang('admin.payment.fleet_commission_percentage')
                                                        <span style="color:red">@lang('admin.payment.workcco')</span>
                                                    </label>
                                                    <div class="col-xs-8">
                                                        <input class="form-control"
                                                               type="number"
                                                               value="{{ config('constants.fleet_commission_percentage') }}"
                                                               id="fleet_commission_percentage"
                                                               name="fleet_commission_percentage"
                                                               min="0"
                                                               max="100"
                                                               placeholder="@lang('admin.payment.fleet_commission_percentage')">
                                                    </div>
                                                </div> --}}

                                                {{-- <div class="form-group row">
                                                    <label for="peak_percentage"
                                                           class="col-xs-4 col-form-label">@lang('admin.payment.peak_percentage')</label>
                                                    <div class="col-xs-8">
                                                        <input class="form-control"
                                                               type="number"
                                                               value="{{ config('constants.peak_percentage', '0') }}"
                                                               id="peak_percentage"
                                                               name="peak_percentage"
                                                               min="0"
                                                               max="100"
                                                               placeholder="@lang('admin.payment.peak_percentage')">
                                                    </div>
                                                </div> --}}

                                                {{-- <div class="form-group row">
                                                    <label for="waiting_percentage"
                                                           class="col-xs-4 col-form-label">@lang('admin.payment.waiting_percentage')</label>
                                                    <div class="col-xs-8">
                                                        <input class="form-control"
                                                               type="number"
                                                               value="{{ config('constants.waiting_percentage', '0') }}"
                                                               id="waiting_percentage"
                                                               name="waiting_percentage"
                                                               min="0"
                                                               max="100"
                                                               placeholder="@lang('admin.payment.waiting_percentage')">
                                                    </div>
                                                </div> --}}

                                                <div class="form-group row">
                                                    <label for="minimum_negative_balance"
                                                           class="col-xs-4 col-form-label">@lang('admin.payment.minimum_negative_balance')</label>
                                                    <div class="col-xs-8">
                                                        <input class="form-control"
                                                               type="number"
                                                               value="{{ config('constants.minimum_negative_balance') }}"
                                                               id="minimum_negative_balance"
                                                               name="minimum_negative_balance"
                                                               max='0'
                                                               placeholder="@lang('admin.payment.minimum_negative_balance')">
                                                    </div>
                                                </div>

                                            </blockquote>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-xs-4">
                                                <a href="{{ route('admin.index') }}"
                                                   class="btn btn-warning btn-block">@lang('admin.back')</a>
                                            </div>
                                            <div class="offset-xs-4 col-xs-4">
                                                <button type="submit"
                                                        class="btn btn-primary btn-block">@lang('admin.payment.update_site_settings')</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="card_manage" role="tabpanel" aria-labelledby="cardmanage-tab">
                            {{-- <div class="form-box row"> --}}
                                @include('common.notify')
                                <a href="#" class="sub-right pull-right" data-toggle="modal" data-target="#add-card-modal" style="margin-right: 10px;margin-bottom: 10px; font-weight:600; font-size:18px">@lang('provider.card.add_debit_card')</a>
                                <table class="table table-striped table-bordered dataTable" id="table-90">
                                    <thead>
                                        <tr>
                                            <th>@lang('provider.card.type')</th>
                                            <th>@lang('provider.card.four')</th>    
                                            <th>@lang('provider.card.action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($cards)!='0')    
                                        @foreach($cards as $each)
                                            <tr>
                                                <td>{{ $each->brand }}</td>
                                                <td>{{ $each->last_four }}</td>
                                                <td>
                                                    <a href="{{ route('admin.payment.card.delete', $each->id) }}" class="btn btn-danger">Delete</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @else
                                            <tr>
                                                <td colspan="2">@lang('provider.card.notfound')</td>
                                        </tr>
                                        @endif
                                    </tbody>

                                </table>
                                        
                            {{-- </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="add-card-modal" class="modal fade" role="dialog">
            <div class="modal-dialog">
      
              <!-- Modal content-->
              <div class="modal-content" >
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">@lang('provider.card.add_debit_card')</h4>
                </div>
                <form id="payment-form" action="{{ url('admin/card/store') }}" method="POST" >
                      {{ csrf_field() }}
      
                    <input type="hidden" data-stripe="currency" value="usd">
                    <input type="hidden" id="company_name" value="{{ Auth::user()->name }}" name="company_name" />
                    <input type="hidden" name="company_address" id="company_address" value="{{ Auth::user()->address }}" />

                    <input type="hidden" name="acc_token" id="acc_token" value="" />
                    <div class="modal-body">
                    <div class="row no-margin" id="card-payment">
                        <div class="payment-errors" style="display: none">
                            <div class="alert alert-danger">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <span id="errortxt"></span>
                            </div>
                        </div>
                        <input type="hidden" name="acc_token" id="acc_token" value="" />
                        <div class="form-group col-md-12 col-sm-12">
                            <label>@lang('provider.card.fullname')</label>
                            <input data-stripe="name" autocomplete="off" required type="text" class="form-control" placeholder="@lang('provider.card.fullname')">
                        </div>
                        <div class="form-group col-md-12 col-sm-12">
                            <label>@lang('provider.card.card_no')</label>
                            <input data-stripe="number" type="text" onkeypress="return isNumberKey(event);" required autocomplete="off" maxlength="16" class="form-control" placeholder="@lang('provider.card.card_no')">
                        </div>
                        <div class="form-group col-md-4 col-sm-12">
                            <label>@lang('provider.card.month')</label>
                            <input type="text" onkeypress="return isNumberKey(event);" maxlength="2" required autocomplete="off" class="form-control" data-stripe="exp-month" placeholder="MM">
                        </div>
                        <div class="form-group col-md-4 col-sm-12">
                            <label>@lang('provider.card.year')</label>
                            <input type="text" onkeypress="return isNumberKey(event);" maxlength="2" required autocomplete="off" data-stripe="exp-year" class="form-control" placeholder="YY">
                        </div>
                        <div class="form-group col-md-4 col-sm-12">
                            <label>@lang('provider.card.cvv')</label>
                            <input type="text" data-stripe="cvc" onkeypress="return isNumberKey(event);" required autocomplete="off" maxlength="3" class="form-control" placeholder="@lang('provider.card.cvv')">
                        </div>
                    </div>
                    </div>
                    
                    <div class="modal-footer">
                    <button type="submit" class="btn btn-default" >@lang('provider.card.add_card')</button>
                    </div>
                </form>
      
              </div>
      
            </div>
          </div> 
    </div>

@endsection

@section('scripts')
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script type="text/javascript">

        $('.js-switch').on('change', function () {
            if ($(this).is(':checked')) {
                // console.log($(this).closest('blockquote').find('.payment_settings'));
                $(this).closest('blockquote').find('.payment_settings').fadeIn(700);
            } else {
                $(this).closest('blockquote').find('.payment_settings').fadeOut(700);
            }

        });


        $(function () {
            var ad_com = "{{ config('constants.commission_percentage') }}";
            if (ad_com > 0) {
                $("#fleet_commission_percentage").val(0);
                $("#fleet_commission_percentage").prop('disabled', true);
                $("#fleet_commission_percentage").prop('required', false);
            } else {
                $("#fleet_commission_percentage").prop('required', true);
            }
            $("#commission_percentage").on('keyup', function () {
                var ad_ins = parseFloat($(this).val());
                // console.log(ad_ins);
                if (ad_ins > 0) {
                    $("#fleet_commission_percentage").val(0);
                    $("#fleet_commission_percentage").prop('disabled', true);
                    $("#fleet_commission_percentage").prop('required', false);
                } else {
                    $("#fleet_commission_percentage").val('');
                    $("#fleet_commission_percentage").prop('disabled', false);
                    $("#fleet_commission_percentage").prop('required', true);
                }

            });
        });
        // const stripe = Stripe("{{ config('constants.stripe_publishable_key') }}");
        // const myForm = document.querySelector('');
        // myForm.addEventListener('submit', handleForm);
        // async function handleForm(event) {
        //     event.preventDefault();
        //     const accountResult = await stripe.createToken('account', {
        //         business_type: 'company',
        //         company: {
        //         name: document.querySelector('#company_name').value,
        //         // address: document.querySelector('#company_address').value
        //         },
        //         tos_shown_and_accepted: true,
        //     });
        //     if(accountResult.token){
        //         document.querySelector('#acc_token').value = accountResult.token;
        //     }
        // }
        Stripe.setPublishableKey("{{ config('constants.stripe_publishable_key', '')}}");

        
         var stripeResponseHandler = function (status, response) {
            var $form = $('#payment-form');
    
            if (response.error) {
                // Show the errors on the form
                $form.find('.payment-errors').text(response.error.message);
                $form.find('button').prop('disabled', false);
                alert(response.error.message);
    
            } else {
                // token contains id, last4, and card type
                var token = response.id;
    
                // Insert the token into the form so it gets submitted to the server
                $form.append($('<input type="hidden" id="stripeToken" name="stripe_token" />').val(token));
                console.log(response);
                jQuery($form.get(0)).submit();
                $("#add-card-modal").modal('toggle');
            }
    
    
        };
        
        $('#payment-form').submit(function (e) {

            if ($('#stripeToken').length == 0)
            {
                
                var $form = $(this);
                $form.find('button').prop('disabled', true);                
                Stripe.card.createToken($form, stripeResponseHandler);
                return false;
            }
        });
    
        function isNumberKey(evt)
        {
            var charCode = (evt.which) ? evt.which : event.keyCode;
            if (charCode != 46 && charCode > 31 
            && (charCode < 48 || charCode > 57))
                return false;
    
            return true;
        }
    
        // function set_default(id)
        // {
        //     $.ajax({
        //         method : 'POST',
        //         url : '{{ url('provider/card/set') }}',
        //         data : '_token={{csrf_token()}}&id='+id,
        //         success:function(html)
        //         {
        //             if(html=='success')
        //             {
        //                 alert('Successfully made changes');
        //             }
        //             else{
        //                 alert('Something Went wrong');
        //             }
        //         }
        //     })
        // }
    </script>  
@endsection
