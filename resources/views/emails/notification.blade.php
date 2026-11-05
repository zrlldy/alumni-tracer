<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $title ?? ($heading ?? 'Alumni Tracer notification') }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-shell { padding: 28px 12px !important; }
            .email-card { width: 100% !important; }
            .email-content { padding: 24px 22px 30px !important; }
            .email-footer { padding-right: 18px !important; padding-left: 18px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#9D1424;font-family:Arial,Helvetica,sans-serif;color:#151515;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#9D1424" style="width:100%;border-collapse:collapse;background-color:#9D1424;">
        <tr>
            <td class="email-shell" align="center" style="padding:40px 16px 30px;">
                <table role="presentation" class="email-card" width="552" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:552px;border-collapse:separate;border-spacing:0;">
                    <tr>
                        <td align="center" style="padding:0 0 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" style="margin:0 auto;border-collapse:separate;border-spacing:0;background-color:#FFFFFF;border-radius:8px;">
                                <tr>
                                    <td align="center" valign="middle" style="padding:5px;">
                                        <img src="{{ $logoSource ?? $message->embed(public_path('images/alumni-tracer-email-logo.png')) }}" width="58" height="58" alt="Alumni Tracer" style="display:block;width:58px;height:58px;border:0;">
                                    </td>
                                </tr>
                            </table>
                            <div style="padding-top:2px;color:#FFFFFF;font-size:10px;font-weight:bold;letter-spacing:1.4px;line-height:14px;">ALUMNI TRACER</div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" bgcolor="#FCBF49" style="height:204px;padding:0;background-color:#FCBF49;border-radius:30px 30px 0 0;">
                            <img src="{{ $mascotSource ?? $message->embed(public_path('images/alumni-tracer-email-mascot.png')) }}" width="188" height="164" alt="Pixel alumni mascot wearing a graduation cap" style="display:block;width:188px;height:164px;margin:0 auto;border:0;">
                        </td>
                    </tr>
                    <tr>
                        <td class="email-content" bgcolor="#FFFCFB" style="padding:24px 46px 30px;background-color:#FFFCFB;border-radius:0 0 36px 36px;">
                            <p style="margin:0 0 20px;font-size:18px;font-weight:700;line-height:26px;">{{ $greeting ?? 'Dear Alumni,' }}</p>

                            @isset($intro)
                                <p style="margin:0 0 16px;text-align:center;font-size:14px;line-height:21px;">{{ $intro }}</p>
                            @endisset

                            @isset($heading)
                                <h1 style="margin:0 0 16px;text-align:center;color:#A31625;font-size:20px;font-weight:700;line-height:28px;">{{ $heading }}</h1>
                            @endisset

                            @isset($bodyText)
                                <p style="margin:0 0 18px;text-align:center;font-size:14px;line-height:21px;">{!! nl2br(e($bodyText)) !!}</p>
                            @endisset

                            @isset($otpCode)
                                <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 22px;border-collapse:separate;border-spacing:5px;">
                                    <tr>
                                        <td bgcolor="#E9E5E4" style="padding:10px 16px;border-radius:8px;background-color:#E9E5E4;color:#A31625;font-size:24px;font-weight:700;letter-spacing:4px;line-height:32px;">{{ $otpCode }}</td>
                                    </tr>
                                </table>
                            @endisset

                            @isset($actionUrl, $actionText)
                                <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 22px;border-collapse:collapse;">
                                    <tr>
                                        <td align="center" bgcolor="#A31625" style="border-radius:9px;background-color:#A31625;">
                                            <a href="{{ $actionUrl }}" style="display:inline-block;min-width:230px;padding:13px 20px;color:#FFFFFF;text-align:center;text-decoration:none;font-size:14px;font-weight:700;line-height:20px;">{{ $actionText }}</a>
                                        </td>
                                    </tr>
                                </table>
                            @endisset

                            @isset($note)
                                <p style="margin:0;text-align:center;color:#5F5757;font-size:13px;line-height:20px;">{{ $note }}</p>
                            @endisset
                        </td>
                    </tr>
                </table>
                <table role="presentation" class="email-footer" width="552" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:552px;border-collapse:collapse;">
                    <tr>
                        <td align="center" style="padding:28px 42px 0;color:#FFFFFF;font-size:12px;line-height:18px;">
                            {{ $footer ?? 'Alumni Tracer — keeping our alumni community connected.' }}
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:20px 0 0;color:#F4C7C8;font-size:10px;line-height:15px;">
                            © {{ date('Y') }} {{ config('app.name', 'Alumni Tracer') }}<br>All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
