
@extends('emails.layouts.alumni')

@section('content')
    <h3 style="font-size:19px;margin:0 0 16px;">
        Dear {{ $user->name }},
    </h3>

    <h2 style="text-align:center;color:#a20f23;
        font-size:19px;margin:0 0 14px;">
        Forgot Your Password?
    </h2>

    <p style="text-align:center;">
        You're receiving this email because a password reset
        was requested for your account.
    </p>

    <p style="text-align:center;margin:24px 0;">
        <a href="{{ $resetUrl }}"
           style="display:block;background-color:#a20f23;
               color:#ffffff;text-decoration:none;text-align:center;
               padding:14px 20px;border-radius:9px;font-weight:bold;">
            Reset Password
        </a>
    </p>

    <p style="font-size:12px;color:#666666;">
        If you did not request a password reset, no further action
        is required. Your password will remain unchanged.
    </p>
@endsection
