A visitor requested an instant quote on the website. The PDF they received is attached.

Reference: {!! $quote->reference !!}
Name: {!! $quote->customer_name !!}
Email: {!! $quote->customer_email !!}
Phone: {!! $quote->customer_phone ?: '—' !!}
Country: {!! $quote->customer_country ?: '—' !!}
Trip: {!! $quote->package?->package_name ?: '—' !!}
Travellers: {!! $quote->adults !!} adults, {!! $quote->children !!} children
Travel date: {!! $quote->travel_start?->format('j F Y') ?: '—' !!}
Total: {!! \App\Models\Quote::money($quote->total) !!}

Open it in the admin: {!! \App\Filament\Resources\Quotes\QuoteResource::getUrl('edit', ['record' => $quote]) !!}

Reply to this email to answer the customer directly.
