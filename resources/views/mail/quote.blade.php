Jambo {!! $quote->customer_name !!},

Thank you for your interest in travelling with Serengeti Roaming. Please find your quotation {!! $quote->reference !!} attached as a PDF.

@if ($quote->package)
Trip: {!! $quote->package->package_name !!}
@endif
Total: {!! \App\Models\Quote::money($quote->total) !!}
@if ($quote->valid_until)
Valid until: {!! $quote->valid_until->format('j F Y') !!}
@endif

If you'd like to go ahead or change anything, just reply to this email or message us on WhatsApp: https://wa.me/{!! config('site.whatsapp') !!}

Kind regards,
Serengeti Roaming African Safaris
