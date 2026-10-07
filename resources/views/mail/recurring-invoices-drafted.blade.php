<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retainer billing</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 40px 20px; }
        .card { background: #ffffff; border-radius: 8px; max-width: 560px; margin: 0 auto; padding: 32px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        h2 { margin: 0 0 8px; font-size: 20px; color: #111; }
        .lede { color: #666; font-size: 14px; margin: 0 0 24px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #888; padding: 0 0 8px; border-bottom: 1px solid #eee; }
        td { padding: 10px 0; border-bottom: 1px solid #f3f3f3; color: #222; }
        td.num { text-align: right; }
        .warn { margin-top: 24px; padding: 16px; border-radius: 6px; background: #fff7ed; color: #9a3412; font-size: 14px; }
        .warn ul { margin: 8px 0 0; padding-left: 20px; }
        .cta { display: inline-block; margin-top: 28px; background: #059669; color: #ffffff; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-size: 14px; font-weight: 600; }
        .footer { margin-top: 28px; font-size: 12px; color: #aaa; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Retainer billing</h2>

        @if (count($invoices) > 0)
            <p class="lede">
                {{ count($invoices) }} draft {{ \Illuminate\Support\Str::plural('invoice', count($invoices)) }}
                created. Nothing has been sent to a client yet.
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Period</th>
                        <th class="num">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->number }}</td>
                            <td>{{ $invoice->client->company_name }}</td>
                            <td>{{ $invoice->period_start?->format('d M Y') ?? '—' }}</td>
                            <td class="num">€ {{ number_format((float) $invoice->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="lede">No invoices were drafted this run.</p>
        @endif

        @if (count($stalled) > 0)
            <div class="warn">
                <strong>Left alone — too far behind to bill unattended:</strong>
                <ul>
                    @foreach ($stalled as $retainer)
                        <li>
                            {{ $retainer->description }} for {{ $retainer->client->company_name }},
                            due since {{ $retainer->next_due_date->format('d M Y') }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <a class="cta" href="{{ $invoicesUrl }}">Review the drafts</a>

        <div class="footer">Sent by the scheduled <code>invoices:issue-recurring</code> run.</div>
    </div>
</body>
</html>
