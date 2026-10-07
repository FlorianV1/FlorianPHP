@php
    /**
     * The sender block is read off the invoice's own snapshot, never off live
     * settings: a KvK number or btw-id filled in next year must not rewrite a
     * document already sent. `issuerDetails()` falls back to the settings only
     * for invoices created before the snapshot column existed.
     */
    $issuer = $invoice->issuerDetails();
    $regime = $invoice->vat_regime;
@endphp
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; padding: 40px; }
        .header { width: 100%; margin-bottom: 36px; }
        .header td { vertical-align: top; }
        .agency { font-size: 20px; font-weight: bold; color: #0f172a; }
        .agency-details { color: #64748b; margin-top: 6px; line-height: 1.5; }
        .invoice-title { font-size: 26px; font-weight: bold; text-align: right; color: #334155; }
        .meta { text-align: right; color: #64748b; margin-top: 4px; line-height: 1.5; }
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
        .vat-note { margin-top: 10px; font-size: 11px; color: #475569; text-align: right; }
        .payment { margin-top: 36px; padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0; line-height: 1.6; }
        .notes { margin-top: 24px; color: #64748b; }
        .footer { margin-top: 36px; font-size: 10px; color: #94a3b8; line-height: 1.6; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <div class="agency">{{ $issuer['name'] }}</div>
                <div class="agency-details">
                    @if (! empty($issuer['trade_name'])){{ $issuer['trade_name'] }}<br>@endif
                    @foreach ($issuer['address_lines'] ?? [] as $line)
                        {{ $line }}<br>
                    @endforeach
                    @if (! empty($issuer['email'])){{ $issuer['email'] }}<br>@endif
                    @if (! empty($issuer['phone'])){{ $issuer['phone'] }}<br>@endif

                    {{-- Each identifier prints only once it exists. Before a
                         Handelsregister entry there is no KvK number to state,
                         and a labelled blank is worse than no line at all. --}}
                    @if (! empty($issuer['kvk_number']))KvK {{ $issuer['kvk_number'] }}<br>@endif
                    @if (! empty($issuer['vat_number']))Btw-id {{ $issuer['vat_number'] }}@endif
                </div>
            </td>
            <td>
                <div class="invoice-title">{{ mb_strtoupper($regime->documentTitle()) }}</div>
                <div class="meta">
                    {{ $invoice->number }}<br>
                    Datum {{ $invoice->issue_date->format('d-m-Y') }}<br>
                    Vervaldatum {{ $invoice->due_date->format('d-m-Y') }}<br>
                    {{-- A service billed over a period has to state the period
                         it covers; this is that date of supply. --}}
                    @if ($invoice->period_start && $invoice->period_end)
                        Periode {{ $invoice->period_start->format('d-m-Y') }} t/m {{ $invoice->period_end->format('d-m-Y') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="addresses">
        <tr>
            <td>
                <div class="label">Factuuradres</div>
                <strong>{{ $invoice->client->company_name }}</strong><br>
                @if ($invoice->client->contact_name){{ $invoice->client->contact_name }}<br>@endif
                @if ($invoice->client->billing_address){!! nl2br(e($invoice->client->billing_address)) !!}<br>@endif
                @if ($invoice->client->vat_number)Btw-id {{ $invoice->client->vat_number }}@endif
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
                <th>Omschrijving</th>
                <th class="num">Aantal</th>
                <th class="num">Stukprijs</th>
                <th class="num">Bedrag</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format((float) $line->quantity, 2), '0'), '.') }}</td>
                    <td class="num">&euro; {{ number_format((float) $line->unit_price, 2) }}</td>
                    <td class="num">&euro; {{ number_format((float) $line->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        {{-- With no VAT registration there is no rate to charge under, so the
             row is omitted rather than printed as 0% — "0%" claims an exempt
             supply, which is a different thing from not being able to charge. --}}
        @if ($invoice->chargesVat())
            <tr>
                <td>Subtotaal</td>
                <td class="num">&euro; {{ number_format((float) $invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Btw {{ rtrim(rtrim(number_format((float) $invoice->vat_rate, 2), '0'), '.') }}%</td>
                <td class="num">&euro; {{ number_format((float) $invoice->vat_amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td class="grand">Totaal</td>
            <td class="num grand">&euro; {{ number_format((float) $invoice->total, 2) }}</td>
        </tr>
    </table>

    @if ($invoice->vat_note)
        <div class="vat-note">{{ $invoice->vat_note }}</div>
    @endif

    @if (! empty($issuer['iban']))
        <div class="payment">
            <div class="label">Betaling</div>
            Over te maken op <strong>{{ $issuer['iban'] }}</strong>@if (! empty($issuer['bic'])) (BIC {{ $issuer['bic'] }})@endif
            ten name van {{ $issuer['name'] }}, onder vermelding van <strong>{{ $invoice->number }}</strong>.
        </div>
    @endif

    @if ($invoice->notes)
        <div class="notes">
            <div class="label">Opmerkingen</div>
            {!! nl2br(e($invoice->notes)) !!}
        </div>
    @endif

    <div class="footer">
        Gelieve te betalen binnen {{ $invoice->issue_date->diffInDays($invoice->due_date) }} dagen na factuurdatum.
    </div>
</body>
</html>
