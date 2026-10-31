@extends('emails.layouts.alumni')

@section('content')
    <h2 style="text-align:center;font-size:16px;margin:0 0 22px;">
        Welcome to the Alumni Tracer System! 🎓
    </h2>

    <p style="font-size:14px;font-weight:bold;margin:0 0 12px;">
        Hi {{ $user->name }},
    </p>

    <p>
        We're glad to have you as part of our growing alumni community.
        This platform is designed to help you stay connected with your
        alma mater, share your career journey, and keep your alumni
        information up to date.
    </p>

    <p style="margin-bottom:4px;">
        Here's what you can do with your account:
    </p>

    <ul style="padding-left:16px;margin-top:4px;">
        <li>Update your personal and contact information.</li>
        <li>Share your employment status and career achievements.</li>
        <li>Participate in graduate tracer surveys.</li>
        <li>Stay connected with your alumni community.</li>
    </ul>

    <p style="text-align:center;">
        Ready to get started? Complete your alumni profile today!
    </p>

    <p>
        <a href="{{ $profileUrl }}"
            style="display:block;background-color:#a20f23;
               color:#ffffff;text-align:center;text-decoration:none;
               padding:13px 20px;border-radius:9px;font-weight:bold;">
            Complete My Profile
        </a>
    </p>

    <p>Best Regards,<br>Alumni-Tracer</p>
@endsection
