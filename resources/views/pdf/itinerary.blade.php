<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $page->package_name }} — Itinerary</title>
    {{-- Rendered by dompdf: tables instead of flex/grid, DejaVu fonts for full Unicode (·, –, ’). --}}
    <style>
        @page { margin: 22mm 16mm 20mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; line-height: 1.5; color: #2b2b26; }
        h1, h2, h3 { font-family: 'DejaVu Serif', serif; color: #1d2713; margin: 0; }
        h1 { font-size: 22pt; line-height: 1.2; }
        h2 { font-size: 14pt; margin: 18pt 0 8pt; padding-bottom: 4pt; border-bottom: 1.5pt solid #b06e1f; }
        h3 { font-size: 11.5pt; }
        p { margin: 0 0 6pt; }
        .muted { color: #6b6b60; }
        .gold { color: #b06e1f; }
        .eyebrow { font-size: 8pt; letter-spacing: 1.5pt; text-transform: uppercase; color: #b06e1f; font-weight: bold; }

        header.page-header { position: fixed; top: -15mm; left: 0; right: 0; height: 10mm; border-bottom: 0.5pt solid #d9c7ae; }
        header.page-header td { font-size: 8pt; color: #6b6b60; vertical-align: middle; }
        footer.page-footer { position: fixed; bottom: -13mm; left: 0; right: 30mm; height: 8mm; font-size: 7.5pt; color: #6b6b60; border-top: 0.5pt solid #d9c7ae; padding-top: 2mm; }

        .cover { width: 100%; height: 70mm; margin-bottom: 8pt; }
        .cover img { width: 100%; height: 70mm; }
        table { border-collapse: collapse; width: 100%; }

        .price-box { background: #1d2713; color: #f6efe4; padding: 10pt 12pt; }
        .price-box .amount { font-family: 'DejaVu Serif', serif; font-size: 18pt; font-weight: bold; color: #f6efe4; }
        .price-box td { color: #f6efe4; padding: 2pt 0; font-size: 9pt; }
        .price-box .label { color: #c89a62; }

        .day { margin-bottom: 10pt; border: 0.5pt solid #e2d5c1; }
        .keep { page-break-inside: avoid; }
        h2 { page-break-after: avoid; }
        .day-head { background: #f6efe4; padding: 6pt 9pt; }
        .day-head .num { font-size: 8pt; font-weight: bold; color: #b06e1f; letter-spacing: 1pt; text-transform: uppercase; }
        .day-body { padding: 8pt 9pt; }
        .day-photo { width: 52mm; vertical-align: top; padding-right: 9pt; }
        .day-photo img { width: 52mm; height: 36mm; }
        .activity { border-left: 2pt solid #b06e1f; background: #fbf7f0; padding: 5pt 8pt; margin: 6pt 0; }
        .meta td { font-size: 8.5pt; padding-top: 4pt; border-top: 0.5pt solid #ebe1d0; }
        .chip { background: #ebe1d0; padding: 1pt 5pt; font-size: 8pt; }

        ul.list { margin: 0; padding-left: 12pt; }
        ul.list li { margin-bottom: 3pt; }
        .cta { margin-top: 16pt; background: #f6efe4; padding: 10pt 12pt; page-break-inside: avoid; }
    </style>
</head>
<body>
@php
    $logo = public_path('images/Logo-2-croped.png');
    $img = fn (?string $path) => \App\Models\TourPage::imagePath($path);
    $facts = collect($page->facts)->filter(fn ($fact) => filled($fact['value'] ?? null));
@endphp

<header class="page-header">
    <table>
        <tr>
            <td>
                @if (is_file($logo))<img src="{{ $logo }}" style="height: 8mm; vertical-align: middle;">@endif
                <strong style="color: #1d2713; margin-left: 4pt;">Serengeti Roaming African Safaris</strong>
            </td>
            <td style="text-align: right;">{{ $page->package_name }} · Itinerary</td>
        </tr>
    </table>
</header>

<footer class="page-footer">
    <table>
        <tr>
            <td>{{ config('site.email') }} · WhatsApp +{{ config('site.whatsapp') }} · {{ preg_replace('#^https?://#', '', $page->url()) }}</td>
        </tr>
    </table>
</footer>

<main>
    @if ($cover = $img($page->banner_image))
        <div class="cover"><img src="{{ $cover }}" alt=""></div>
    @endif

    <table>
        <tr>
            <td style="vertical-align: top; padding-right: 12pt;">
                <p class="eyebrow">{{ collect([$page->category->getLabel(), $page->tier?->getLabel(), $page->duration])->filter()->implode(' · ') }}</p>
                <h1>{{ $page->package_name }}</h1>
                @if ($page->location)
                    <p class="muted" style="margin-top: 3pt;">{{ $page->location }}</p>
                @endif
                @if ($page->banner_text)
                    <p style="margin-top: 6pt;">{{ $page->banner_text }}</p>
                @endif
            </td>
            <td style="width: 58mm; vertical-align: top;">
                <div class="price-box">
                    @if ($page->price_from)
                        <div class="label" style="font-size: 7.5pt; letter-spacing: 1pt;">FROM</div>
                        <div class="amount">${{ number_format($page->price_from) }}</div>
                    @else
                        <div class="amount" style="font-size: 13pt;">Price on request</div>
                    @endif
                    @if ($page->price_note)
                        <div style="font-size: 7.5pt; margin-bottom: 4pt;">{{ $page->price_note }}</div>
                    @endif
                    @if ($facts->isNotEmpty())
                        <table style="margin-top: 4pt;">
                            @foreach ($facts as $fact)
                                <tr>
                                    <td class="label">{{ $fact['label'] ?? '' }}</td>
                                    <td style="text-align: right; font-weight: bold;">{{ $fact['value'] }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @if ($page->overview_body)
        <h2>{{ $page->overview_heading ?: 'Package Overview' }}</h2>
        @foreach (preg_split('/\R{2,}/', trim($page->overview_body)) as $paragraph)
            <p>{{ $paragraph }}</p>
        @endforeach
    @endif

    @if (! empty($page->itinerary))
        <h2>Day-by-Day Itinerary</h2>
        @foreach ($page->itinerary as $day)
            <div class="day">
                <div class="keep">
                    <div class="day-head">
                        <span class="num">{{ $day['day'] ?? '' }}</span>
                        <h3>{{ $day['title'] ?? '' }}</h3>
                    </div>
                    <div class="day-body" style="padding-bottom: 0;">
                        <table>
                            <tr>
                                @if ($dayImage = $img($day['hero_image'] ?? null))
                                    <td class="day-photo"><img src="{{ $dayImage }}" alt=""></td>
                                @endif
                                <td style="vertical-align: top;">
                                    @if (! empty($day['copy']))
                                        <p>{{ $day['copy'] }}</p>
                                    @endif
                                    @if (! empty($day['note']))
                                        <p><strong>Note:</strong> {{ $day['note'] }}</p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="day-body" style="padding-top: 0;">

                    @if (! empty($day['activity_title']))
                        <div class="activity">
                            <p class="gold" style="margin-bottom: 2pt;"><strong>Activity · {{ $day['activity_title'] }}</strong></p>
                            @if (! empty($day['activity_copy']))
                                <p style="margin: 0;">{{ $day['activity_copy'] }}</p>
                            @endif
                        </div>
                    @endif

                    @if (! empty($day['options']))
                        <p style="margin: 6pt 0 2pt;"><strong>Options based on your package:</strong></p>
                        <ul class="list">
                            @foreach ($day['options'] as $option)
                                <li><strong>{{ $option['tier'] ?? '' }}</strong> — <span class="gold">{{ $option['label'] ?? '' }}</span></li>
                            @endforeach
                        </ul>
                    @endif

                    @if (! empty($day['meal_plan']) || ! empty($day['accommodation']))
                        <table class="meta" style="margin-top: 6pt;">
                            <tr>
                                <td>@if (! empty($day['accommodation']))<strong>Accommodation:</strong> {{ $day['accommodation'] }}@endif</td>
                                <td style="text-align: right;">@if (! empty($day['meal_plan']))<span class="chip">Meal plan: <strong>{{ $day['meal_plan'] }}</strong></span>@endif</td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    @if (! empty($page->included) || ! empty($page->excluded))
        <div style="page-break-inside: avoid;">
            <h2>What's Included</h2>
            <table>
                <tr>
                    <td style="width: 50%; vertical-align: top; padding-right: 10pt;">
                        <p class="eyebrow">Included</p>
                        <ul class="list">
                            @foreach ($page->included ?? [] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td style="width: 50%; vertical-align: top;">
                        <p class="eyebrow" style="color: #6b6b60;">Not Included</p>
                        <ul class="list">
                            @foreach ($page->excluded ?? [] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <div class="cta">
        <h3>Ready to book or tailor this trip?</h3>
        <p style="margin: 4pt 0 0;">
            Every itinerary can be adjusted to your dates, budget and interests. No payment is taken until we confirm availability.<br>
            Email <strong>{{ config('site.email') }}</strong> · WhatsApp <strong>+{{ config('site.whatsapp') }}</strong><br>
            View online: <span class="gold">{{ $page->url() }}</span>
        </p>
    </div>

    <p class="muted" style="font-size: 7.5pt; margin-top: 10pt;">
        Prices and itineraries are a guide and may change with season and availability. Generated {{ now()->format('j F Y') }}.
    </p>
</main>
</body>
</html>
