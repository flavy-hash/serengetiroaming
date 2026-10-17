{!! strip_tags($inquiry['title']) !!}

@foreach ($inquiry['fields'] as $label => $value)
{!! $label !!}: {!! strip_tags((string) $value) !!}
@endforeach
@if (filled($inquiry['message'] ?? null))

{!! $inquiry['message_label'] !!}:
{!! strip_tags((string) $inquiry['message']) !!}
@endif

Open in the admin: {!! $inquiry['admin_url'] !!}

Reply to this email to answer the visitor directly.
