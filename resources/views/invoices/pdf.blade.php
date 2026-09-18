<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; padding: 40px; }
        .header { width: 100%; margin-bottom: 40px; }
        .header td { vertical-align: top; }
        .agency { font-size: 20px; font-weight: bold; color: #0f172a; }
        .invoice-title { font-size: 26px; font-weight: bold; text-align: right; color: #334155; }
        .meta { text-align: right; color: #64748b; margin-top: 4px; }
        .addresses { width: 100%; margin-bottom: 32px; }
        .addresses td { vertical-align: top; width: 50%; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; margin-bottom: 4px; }
        .lines { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .lines th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #64748b; border-bottom: 2px solid #e2e8f0; padding: 8px 6px; }
        .lines td { padding: 8px 6px; border-bottom: 1px solid #f1f5f9; }
        .lines .num, .lines th.num { text-align: right; }
        .totals { width: 40%; margin-left: 60%; border-collapse: collapse; }
        .totals td { padding: 5px 6px; }
        .totals .num { text-align: right; }
        .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #0f172a; }
        .notes { margin-top: 32px; color: #64748b; }
        .footer { margin-top: 48px; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="agency">{{ config('app.name') }}</td>
            <td>
                <div class="invoice-title">INVOICE</div>
                <div class="meta">
                    {{ $invoice->number }}<br>
                    Issued {{ $invoice->issue_date->format('d M Y') }}<br>
                    Due {{ $invoice->due_date->format('d M Y') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="addresses">
        <tr>
            <td>
                <div class="label">Billed to</div>
                <strong>{{ $invoice->client->company_name }}</strong><br>
                @if ($invoice->client->contact_name){{ $invoice->client->contact_name }}<br>@endif
                @if ($invoice->client->billing_address){!! nl2br(e($invoice->client->billing_address)) !!}<br>@endif
                @if ($invoice->client->vat_number)VAT: {{ $invoice->client->vat_number }}@endif
            </td>
            <td>
                @if ($invoice->website)
                    <div class="label">Project</div>
                    {{ $invoice->website->label }}<br>{{ $invoice->website->url }}
                @endif
            </td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th>Description</th>
                <th class="num">Qty</th>
                <th class="num">Unit price</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format((float) $line->quantity, 2), '0'), '.') }}</td>
                    <td class="num">€ {{ number_format((float) $line->unit_price, 2) }}</td>
                    <td class="num">€ {{ number_format((float) $line->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="num">€ {{ number_format((float) $invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>VAT {{ rtrim(rtrim(number_format((float) $invoice->vat_rate, 2), '0'), '.') }}%</td>
            <td class="num">€ {{ number_format((float) $invoice->vat_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="grand">Total due</td>
            <td class="num grand">€ {{ number_format((float) $invoice->total, 2) }}</td>
        </tr>
    </table>

    @if ($invoice->notes)
        <div class="notes">
            <div class="label">Notes</div>
            {!! nl2br(e($invoice->notes)) !!}
        </div>
    @endif

    <div class="footer">
        Please pay within {{ $invoice->issue_date->diffInDays($invoice->due_date) }} days of the issue date, referencing {{ $invoice->number }}.
    </div>
</body>
</html>
