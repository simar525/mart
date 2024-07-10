@extends('themes.basic.layouts.auth')
@section('title', translate('Sign In'))
@section('content')
    <div class="sign-box">
        <div class="card-v">
            <div class="mb-4">
                <h2 class="sign-box-title">{{ translate('Sign In') }}</h2>
                <p class="sign-box-text">{{ translate('Enter your account details to sign in') }}</p>
            </div>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ translate('Email or Username') }}</label>
                    <input type="text" name="email_or_username" class="form-control form-control-md"
                        placeholder="{{ translate('Email or Username') }}" value="{{ old('email_or_username') }}"
                        required />
                </div>
                <div class="mb-3">
                    <div class="row row-cols-auto flex-nowrap justify-content-between align-items-center">
                        <div class="col">
                            <label class="form-label mb-0">{{ translate('Password') }}</label>
                        </div>
                        <div class="col">
                            <a href="{{ route('password.request') }}" class="mb-2 d-block">
                                {{ translate('Forgot Your Password?') }}
                            </a>
                        </div>
                    </div>
                    <input type="password" name="password" class="form-control form-control-md"
                        placeholder="{{ translate('Password') }}" required />
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                            {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">{{ translate('Remember Me') }}</label>
                    </div>
                </div>
                <x-captcha />
                <button class="btn btn-primary btn-md w-100">{{ translate('Sign In') }}</button>
            </form>
            <x-oauth-buttons />
        </div>
    </div>
@endsection
