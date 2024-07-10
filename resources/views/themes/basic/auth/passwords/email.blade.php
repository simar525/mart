@extends('themes.basic.layouts.auth')
@section('title', translate('Reset Password'))
@section('content')
    <div class="sign-box">
        <div class="card-v">
            <div class="mb-4">
                <h2 class="sign-box-title">{{ translate('Reset Password') }}</h2>
                <p class="sign-box-text">{{ translate('You will receive an email with a link to reset your password') }}</p>
            </div>
            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ translate('Email address') }}</label>
                    <input type="email" name="email" class="form-control form-control-md"
                        placeholder="{{ translate('Email address') }}" value="{{ old('email') }}" required />
                </div>
                <x-captcha />
                <button class="btn btn-primary btn-md w-100">{{ translate('Reset') }}</button>
            </form>
        </div>
    </div>
@endsection
