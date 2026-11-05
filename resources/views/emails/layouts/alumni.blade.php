<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $title ?? ($heading ?? 'Alumni Tracer') }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-shell { padding: 14px 10px !important; }
            .email-card { width: 100% !important; }
            .email-header { padding: 22px 24px !important; }
            .email-hero { padding: 27px 24px 30px !important; }
            .email-content { padding: 32px 24px 38px !important; }
            .email-heading { font-size: 28px !important; line-height: 35px !important; }
            .email-mascot { width: 64px !important; height: auto !important; }
            .email-footer { padding: 22px 24px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#F1ECE6;font-family:Arial,Helvetica,sans-serif;color:#26323B;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F1ECE6" style="width:100%;border-collapse:collapse;background-color:#F1ECE6;">
        <tr>
            <td class="email-shell" align="center" style="padding:38px 16px;">
                <table role="presentation" class="email-card" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;border-collapse:collapse;background-color:#FFFFFF;border:1px solid #E4D8D0;">
                    <tr>
                        <td class="email-header" bgcolor="#9D1424" style="padding:26px 44px;background-color:#9D1424;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                <tr>
                                    <td width="108" valign="middle" style="width:108px;padding:4px;">
                                        <img src="{{ $logoSource ?? $message->embed(public_path('images/alumni-whitelogo-email.png')) }}" width="100" height="58" alt="Alumni Tracer" style="display:block;width:100px;height:58px;border:0;">
                                    </td>
                                    {{-- <td valign="middle" style="padding-left:13px;color:#003049;">
                                        <span style="display:block;font-family:Georgia,Times,serif;font-size:20px;font-weight:700;line-height:24px;">Alumni Tracer</span>
                                        <span style="display:block;padding-top:3px;color:#9D1424;font-size:10px;font-weight:700;letter-spacing:1.6px;line-height:15px;">GRADUATE RECORDS</span>
                                    </td> --}}
                                    <td width="76" align="right" valign="middle" style="width:76px;">
                                        <img class="email-mascot" src="{{ $mascotSource ?? $message->embed(public_path('images/alumni-tracer-email-mascot.png')) }}" width="72" height="63" alt="Graduate mascot" style="display:block;width:72px;height:63px;border:0;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td height="5" bgcolor="#FCBF49" style="height:5px;line-height:5px;font-size:1px;background-color:#FCBF49;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="email-hero" bgcolor="#9D1424" style="padding:31px 46px 35px;background-color:#9D1424;">
                            @isset($eyebrow)
                                <p style="margin:0 0 10px;color:#FCBF49;font-size:11px;font-weight:700;letter-spacing:1.8px;line-height:16px;text-transform:uppercase;">{{ $eyebrow }}</p>
                            @endisset
                            @isset($heading)
                                <h1 class="email-heading" style="margin:0;color:#FFFFFF;font-family:Georgia,Times,serif;font-size:32px;font-weight:700;line-height:40px;">{{ $heading }}</h1>
                            @endisset
                        </td>
                    </tr>
                    <tr>
                        <td class="email-content" bgcolor="#FFFFFF" style="padding:36px 46px 44px;background-color:#FFFFFF;font-size:15px;line-height:24px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td class="email-footer" bgcolor="#F8F3F0" style="padding:26px 46px;border-top:1px solid #E8DDD5;background-color:#F8F3F0;color:#5F676C;font-size:12px;line-height:19px;">
                            {{ $footer ?? 'Alumni Tracer helps graduates keep their records and career details current.' }}
                            <br>
                            <span style="color:#7B8387;">This is an automated message from Alumni Tracer. Please do not reply.</span>
                        </td>
                    </tr>
                </table>
                <p style="max-width:600px;margin:19px 0 0;color:#6A6260;font-size:11px;line-height:17px;text-align:center;">© {{ date('Y') }} Alumni Tracer</p>
            </td>
        </tr>
    </table>
</body>
</html>
