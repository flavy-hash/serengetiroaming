<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quote->reference }}</title>
    {{--
        Rendered by dompdf: tables instead of flex/grid, DejaVu fonts for full Unicode.
        Keep reference numbers inside sentences in normal weight — dompdf can draw a bold
        inline element over the space before it.
    --}}
    <style>
        @page { margin: 14mm 16mm 20mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5pt; line-height: 1.35; color: #2b2b26; }
        table { border-collapse: collapse; width: 100%; }
        td, th { vertical-align: top; }
        h1 { font-family: 'DejaVu Serif', serif; font-size: 22pt; letter-spacing: 2pt; color: #1d2713; margin: 0; }
        h2 { font-family: 'DejaVu Serif', serif; font-size: 11pt; color: #1d2713; margin: 0 0 5pt; }
        p { margin: 0 0 3pt; }
        .muted { color: #6b6b60; }
        .small { font-size: 8pt; }
        .gold { color: #b06e1f; }
        .eyebrow { font-size: 7.5pt; letter-spacing: 1.2pt; text-transform: uppercase; color: #b06e1f; font-weight: bold; }

        .head td.cell { padding-bottom: 8pt; border-bottom: 1.5pt solid #b06e1f; }
        .meta td { font-size: 8.5pt; padding: 1pt 0; }
        .meta td.k { color: #6b6b60; text-align: right; padding-right: 6pt; }
        .meta td.v { text-align: right; width: 32mm; }

        .box { border: 0.5pt solid #e2d5c1; background: #fbf7f0; padding: 7pt 10pt; }
        .gap { width: 6mm; }

        .items { margin-top: 12pt; }
        .items th { background: #1d2713; color: #f6efe4; font-size: 8pt; text-transform: uppercase; letter-spacing: .8pt; padding: 5pt 7pt; text-align: left; }
        .items td { padding: 5pt 7pt; border-bottom: 0.5pt solid #ebe1d0; }
        .num { text-align: right; white-space: nowrap; }
        .totals td { padding: 3pt 7pt; }
        .totals .grand td { border-top: 1pt solid #1d2713; font-size: 11pt; font-weight: bold; color: #1d2713; padding-top: 6pt; }

        .bank td.acct { width: 50%; padding: 5pt 8pt; border: 0.5pt solid #e2d5c1; font-size: 8.5pt; }
        .bank td.acct p { margin: 0 0 1pt; }
        .section { margin-top: 10pt; page-break-inside: avoid; }
        .terms p { margin: 0 0 1pt; font-size: 7.5pt; }

        footer { position: fixed; bottom: -13mm; left: 0; right: 0; height: 9mm; border-top: 0.5pt solid #d9c7ae; padding-top: 2mm; font-size: 7.5pt; color: #6b6b60; }
    </style>
</head>
<body>
@php
    $money = fn ($amount) => \App\Models\Quote::money($amount);
    $items = collect($quote->items ?? [])->filter(fn ($i) => is_array($i) && filled($i['description'] ?? null));
    $travellers = trim($quote->adults.' '.\Illuminate\Support\Str::plural('adult', $quote->adults)
        .($quote->children ? ', '.$quote->children.' '.\Illuminate\Support\Str::plural('child', $quote->children) : ''));
    $days = $quote->durationDays();
@endphp

<footer>
    <table>
        <tr>
            <td>Serengeti Roaming African Safaris · Arusha, Tanzania</td>
            <td style="text-align: right;">{{ config('site.email') }} · WhatsApp {{ config('site.whatsapp_display') }}</td>
        </tr>
    </table>
</footer>

<table class="head">
    <tr>
        <td class="cell">
            @if ($logo)<img src="{{ $logo }}" style="height: 16mm;">@endif
            <p style="margin-top: 4pt;"><span style="font-family: 'DejaVu Serif', serif; font-size: 11pt; color: #1d2713;">Serengeti Roaming African Safaris</span></p>
        </td>
        <td class="cell" style="text-align: right;">
            <h1>QUOTATION</h1>
            <table class="meta" style="margin-top: 6pt;">
                <tr><td class="k">Reference</td><td class="v">{{ $quote->reference }}</td></tr>
                <tr><td class="k">Date</td><td class="v">{{ ($quote->created_at ?? now())->format('j M Y') }}</td></tr>
                @if ($quote->valid_until)
                    <tr><td class="k">Valid until</td><td class="v">{{ $quote->valid_until->format('j M Y') }}</td></tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<table style="margin-top: 12pt;">
    <tr>
        <td class="box" style="width: 48%;">
            <p class="eyebrow">Prepared for</p>
            <p style="font-size: 10.5pt; color: #1d2713;">{{ $quote->customer_name }}</p>
            <p class="small">{{ $quote->customer_email }}</p>
            @if (filled($quote->customer_phone))<p class="small">{{ $quote->customer_phone }}</p>@endif
            @if (filled($quote->customer_country))<p class="small">{{ $quote->customer_country }}</p>@endif
        </td>
        <td class="gap"></td>
        <td class="box" style="width: 48%;">
            <p class="eyebrow">Your trip</p>
            @if ($quote->package)
                <p style="font-size: 10.5pt; color: #1d2713;">{{ $quote->package->package_name }}</p>
            @endif
            <p class="small">Travellers: {{ $travellers }}</p>
            @if ($quote->travel_start)
                <p class="small">Dates: {{ $quote->travel_start->format('j M Y') }}@if ($quote->travel_end) – {{ $quote->travel_end->format('j M Y') }}@endif</p>
            @endif
            @if ($days)
                <p class="small">Duration: {{ $days }} {{ \Illuminate\Support\Str::plural('day', $days) }}</p>
            @endif
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>Description</th>
            <th class="num" style="width: 14mm;">Qty</th>
            <th class="num" style="width: 30mm;">Unit price</th>
            <th class="num" style="width: 32mm;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td class="num">{{ rtrim(rtrim(number_format((float) ($item['quantity'] ?? 0), 2), '0'), '.') }}</td>
                <td class="num">{{ $money($item['unit_price'] ?? 0) }}</td>
                <td class="num">{{ $money((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">No costs added yet.</td></tr>
        @endforelse
    </tbody>
</table>

<table class="totals" style="margin-top: 4pt;">
    <tr><td style="width: 60%;"></td><td class="muted">Subtotal</td><td class="num">{{ $money($quote->subtotal) }}</td></tr>
    @if ((float) $quote->discount > 0)
        <tr><td></td><td class="muted">Discount</td><td class="num">− {{ $money($quote->discount) }}</td></tr>
    @endif
    <tr class="grand"><td></td><td>Total</td><td class="num">{{ $money($quote->total) }}</td></tr>
</table>

@if (filled($quote->notes))
    <div class="section">
        <h2>Notes</h2>
        @foreach (preg_split('/\R/', trim($quote->notes)) as $line)
            <p>{{ $line }}</p>
        @endforeach
    </div>
@endif

@if ($accounts || filled($instructions))
    <div class="section">
        <h2>Payment details</h2>
        @if (filled($instructions))
            @foreach (preg_split('/\R/', trim($instructions)) as $line)
                <p class="small">{{ $line }}</p>
            @endforeach
        @endif
        @if ($accounts)
            <table class="bank" style="margin-top: 4pt;">
                @foreach (array_chunk($accounts, 2) as $row)
                    <tr>
                        @foreach ($row as $account)
                            <td class="acct">
                                <p style="color: #1d2713;"><span style="font-weight: bold;">{{ $account['bank_name'] }}</span>@if ($account['currency']) ({{ $account['currency'] }})@endif</p>
                                <p>Account name: {{ $account['account_name'] }}</p>
                                <p>Account number: {{ $account['account_number'] }}</p>
                                @if ($account['branch'])<p>Branch: {{ $account['branch'] }}</p>@endif
                                @if ($account['swift'])<p>SWIFT: {{ $account['swift'] }}</p>@endif
                            </td>
                        @endforeach
                        @if (count($row) === 1)<td style="width: 50%;"></td>@endif
                    </tr>
                @endforeach
            </table>
        @endif
        <p class="small" style="margin-top: 5pt;">Payment reference: {{ $quote->reference }}</p>
    </div>
@endif

@if (filled($terms))
    <div class="section terms">
        <h2>Terms</h2>
        @foreach (preg_split('/\R/', trim($terms)) as $line)
            <p class="small muted">{{ $line }}</p>
        @endforeach
    </div>
@endif
</body>
</html>
