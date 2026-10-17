Hi {!! $faq->name ?: 'there' !!},

Thanks for your question{!! $faq->tourPage ? ' about '.$faq->tourPage->package_name : '' !!}:

"{!! $faq->question !!}"

{!! $faq->answer !!}

If you have anything else to ask, just reply to this email or message us on WhatsApp: https://wa.me/{!! config('site.whatsapp') !!}

Serengeti Roaming African Safaris
{!! url('/') !!}
