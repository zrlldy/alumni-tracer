@extends('emails.layouts.alumni')

@section('content')
    <h3 style="font-size:19px;margin:0 0 16px;">
        Dear {{ $user->name }},
    </h3>

    <h2 style="text-align:center;color:#a20f23;
        font-size:19px;margin:0 0 12px;">
        Verify Your Email
    </h2>

    <p style="text-align:center;">
        We're excited to have you join our alumni community.
        Please verify your email address to complete your registration
        and secure your account.
    </p>

    <p style="text-align:center;margin:24px 0;">
        <a href="{{ $verificationUrl }}"
            style="display:block;background-color:#a20f23;
               color:#ffffff;text-decoration:none;text-align:center;
               padding:14px 20px;border-radius:9px;font-weight:bold;">
            Verify Email
        </a>
    </p>

    <p style="font-size:12px;color:#666666;">
        If you did not create an account, you can safely ignore this email.
    </p>
@endsection
