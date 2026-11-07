@extends('emails.layouts.alumni')

@section('content')
    <p style="margin:0 0 16px;color:#26323B;font-size:15px;font-weight:700;line-height:23px;">{{ $greeting ?? 'Dear Alumni,' }}</p>

    @isset($intro)
        <p style="margin:0 0 17px;color:#26323B;line-height:24px;">{{ $intro }}</p>
    @endisset

    @isset($bodyText)
        <p style="margin:0 0 25px;color:#26323B;line-height:24px;">{!! nl2br(e($bodyText)) !!}</p>
    @endisset

    @isset($otpCode)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFF9EB" style="width:100%;margin:5px 0 25px;border-collapse:collapse;background-color:#FFF9EB;border:1px solid #E4D8D0;">
            <tr>
                <td align="center" style="padding:22px 12px;border-left:4px solid #FCBF49;color:#003049;font-family:'Courier New',Courier,monospace;font-size:31px;font-weight:700;letter-spacing:6px;line-height:40px;">
                    {{ $otpCode }}
                </td>
            </tr>
        </table>
    @endisset

    @isset($actionUrl, $actionText)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:4px 0 25px;border-collapse:collapse;">
            <tr>
                <td align="center" bgcolor="#9D1424" style="background-color:#9D1424;">
                    <a href="{{ $actionUrl }}" style="display:block;padding:15px 22px;color:#FFFFFF;text-align:center;text-decoration:none;font-size:15px;font-weight:700;line-height:22px;">{{ $actionText }}</a>
                </td>
            </tr>
        </table>

        @if ($showActionLink ?? false)
            <p style="margin:0 0 8px;color:#5F676C;font-size:13px;line-height:20px;">If the button does not work, use this verification link:</p>
            <p style="margin:0 0 25px;font-size:13px;line-height:20px;word-break:break-all;overflow-wrap:anywhere;">
                <a href="{{ $actionUrl }}" style="color:#9D1424;text-decoration:underline;">{{ $actionUrl }}</a>
            </p>
        @endif
    @endisset

    @isset($note)
        <p style="margin:0;padding-top:20px;border-top:1px solid #E8DDD5;color:#5F676C;font-size:13px;line-height:21px;">{!! nl2br(e($note)) !!}</p>
    @endisset
@endsection
