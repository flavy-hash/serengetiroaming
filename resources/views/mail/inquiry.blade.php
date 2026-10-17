<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $inquiry['title'] }}</title>
</head>
{{-- Plain HTML with inline styles for email clients. Every visitor value is escaped with {{ }}. --}}
<body style="margin:0; padding:0; background:#f6efe4; font-family:Helvetica, Arial, sans-serif; color:#1d2713;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6efe4; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#ffffff; border-radius:14px; overflow:hidden; border:1px solid #e2d5c1;">
                    <tr>
                        <td style="background:#1d2713; padding:20px 28px;">
                            <p style="margin:0; font-size:12px; letter-spacing:2px; text-transform:uppercase; color:#c89a62;">{{ $inquiry['kind'] }}</p>
                            <h1 style="margin:6px 0 0; font-size:20px; line-height:1.3; color:#f6efe4; font-family:Georgia, serif;">{{ $inquiry['title'] }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                @foreach ($inquiry['fields'] as $label => $value)
                                    <tr>
                                        <td style="padding:8px 12px 8px 0; width:38%; color:#6b7a5e; border-bottom:1px solid #f0e8da; vertical-align:top;">{{ $label }}</td>
                                        <td style="padding:8px 0; border-bottom:1px solid #f0e8da; vertical-align:top; font-weight:bold;">
                                            @if ($label === 'Email' && filter_var($value, FILTER_VALIDATE_EMAIL))
                                                <a href="mailto:{{ $value }}" style="color:#965e1a;">{{ $value }}</a>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            @if (filled($inquiry['message'] ?? null))
                                <p style="margin:22px 0 6px; font-size:12px; letter-spacing:1.5px; text-transform:uppercase; color:#6b7a5e;">{{ $inquiry['message_label'] }}</p>
                                <div style="padding:14px 16px; background:#fbf7f0; border-left:3px solid #b06e1f; border-radius:6px; font-size:14px; line-height:1.6; white-space:pre-line;">{{ $inquiry['message'] }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px 28px;">
                            <a href="{{ $inquiry['admin_url'] }}" style="display:inline-block; padding:12px 22px; background:#344723; color:#f6efe4; text-decoration:none; border-radius:999px; font-size:14px; font-weight:bold;">Open in the admin</a>
                            <p style="margin:16px 0 0; font-size:13px; color:#6b7a5e;">Reply to this email to answer {{ $inquiry['reply_to']['name'] ?? 'the visitor' }} directly.</p>
                        </td>
                    </tr>
                </table>
                <p style="margin:14px 0 0; font-size:12px; color:#6b7a5e;">Sent by the {{ config('site.name') }} website · {{ now()->format('j M Y, H:i') }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
