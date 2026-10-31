
@extends('emails.layouts.alumni')
@section('content')
    <h3 style="font-size:19px;margin:0 0 16px;">
        Dear {{ $user->name }},
    </h3>

    <p style="text-align:center;">
        Please use the verification code below to confirm your email address.
    </p>

    <h2 style="text-align:center;color:#a20f23;
        font-size:19px;margin:16px 0;">
        OTP CODE
    </h2>

    <table role="presentation" align="center" cellpadding="0"
           cellspacing="5" border="0" style="margin:0 auto;">
        <tr>
            @foreach (str_split((string) $otp) as $digit)
                <td align="center" valign="middle"
                    style="width:31px;height:41px;background-color:#e5e0e0;
                        border-radius:7px;font-size:21px;font-weight:bold;
                        color:#a20f23;">
                    {{ $digit }}
                </td>
            @endforeach
        </tr>
    </table>

    <p style="text-align:center;font-size:12px;color:#666666;
        margin-top:20px;">
        This code expires in {{ $expiresInMinutes }} minutes.
        Do not share it with anyone.
    </p>
@endsection
