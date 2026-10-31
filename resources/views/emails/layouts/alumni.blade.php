<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>{{ $emailTitle ?? 'Alumni Tracer' }}</title>
</head>

<body style="margin:0;padding:0;background-color:#9f1021;
    font-family:Arial,Helvetica,sans-serif;color:#171717;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#9f1021;">
        <tr>
            <td align="center" style="padding:55px 16px 20px;">

                <!-- Logo -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:552px;">
                    <tr>
                        <td align="center" style="padding-bottom:30px;">
                            <img src="{{ asset('images/alumni-tracer-logo.png') }}" alt="Alumni Tracer" width="105"
                                style="display:block;width:105px;
                                    max-width:100%;height:auto;border:0;">
                        </td>
                    </tr>
                </table>

                <!-- Main card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:552px;background-color:#fffafa;
                        border-radius:36px;overflow:hidden;">

                    <!-- Gold illustration panel -->
                    <tr>
                        <td align="center" style="background-color:#ffc536;padding:28px 20px;">
                            @if (!empty($heroImage))
                                <img src="{{ asset($heroImage) }}" alt="" width="180"
                                    style="display:block;width:180px;
                                        max-width:100%;height:auto;border:0;">
                            @else
                                <div
                                    style="font-size:52px;
                                    line-height:100px;color:#9f1021;">
                                    AT
                                </div>
                            @endif
                        </td>
                    </tr>

                    <!-- Email content -->
                    <tr>
                        <td style="padding:26px 46px 32px;
                            font-size:14px;line-height:1.6;">

                            @yield('content')

                            <!-- Shared disclaimer -->
                            <p
                                style="margin:28px 0 0;color:#888888;
                                font-size:10px;line-height:1.6;">
                                {{ $footerMessage ??
                                    'This is an automated message from the Alumni Tracer System. Please do not reply to this email.' }}
                            </p>

                        </td>
                    </tr>
                </table>

                <!-- Footer -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:552px;color:#ffffff;">
                    <tr>
                        <td align="center" style="padding:38px 12px 12px;">

                            @if (!empty($facebookUrl))
                                <a href="{{ $facebookUrl }}" style="text-decoration:none;">
                                    <span
                                        style="display:inline-block;
                                        background-color:#1877f2;
                                        color:#ffffff;border-radius:50%;
                                        width:19px;height:19px;
                                        line-height:19px;font-weight:bold;
                                        font-size:14px;text-align:center;">
                                        f
                                    </span>
                                </a>
                            @endif

                            <p
                                style="font-size:10px;line-height:1.5;
                                margin:20px 0;color:#ffffff;">
                                Stay connected with your alumni community.
                                <br>
                                Alumni Tracer System
                            </p>

                            <p
                                style="font-size:8px;line-height:1.5;
                                color:#e8b8bd;margin:16px 0 0;">
                                &copy; {{ date('Y') }} Alumni Tracer.
                                <br>All rights reserved.
                            </p>

                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>

</html>
