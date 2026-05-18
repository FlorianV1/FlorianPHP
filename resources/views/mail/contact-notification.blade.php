<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 40px 20px; }
        .card { background: #ffffff; border-radius: 8px; max-width: 560px; margin: 0 auto; padding: 32px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        h2 { margin: 0 0 24px; font-size: 20px; color: #111; }
        .field { margin-bottom: 16px; }
        .label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #888; margin-bottom: 4px; }
        .value { color: #222; font-size: 15px; }
        .message-body { white-space: pre-wrap; line-height: 1.6; }
        .divider { border: none; border-top: 1px solid #eee; margin: 24px 0; }
        .footer { font-size: 12px; color: #aaa; }
    </style>
</head>
<body>
    <div class="card">
        <h2>New Contact Message</h2>

        <div class="field">
            <div class="label">Name</div>
            <div class="value">{{ $contactMessage->name }}</div>
        </div>

        <div class="field">
            <div class="label">Email</div>
            <div class="value"><a href="mailto:{{ $contactMessage->email }}" style="color:#4A9FFF;">{{ $contactMessage->email }}</a></div>
        </div>

        <hr class="divider">

        <div class="field">
            <div class="label">Message</div>
            <div class="value message-body">{{ $contactMessage->message }}</div>
        </div>

        <hr class="divider">

        <div class="footer">
            Received {{ $contactMessage->created_at->format('d M Y \a\t H:i') }}
        </div>
    </div>
</body>
</html>
