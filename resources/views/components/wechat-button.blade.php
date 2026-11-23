@php
    $wechat = \App\Models\Setting::wechat();
@endphp

{{-- Floating WeChat button (bottom left). WeChat has no "start a chat" link, so it opens a small panel with
     the WeChat ID (copy button) and the QR code. Hidden until an ID is set in Site settings. --}}
@if ($wechat['id'])
    <div class="wechat-fab notranslate" data-wechat-fab>
        <div class="wechat-fab__panel" data-wechat-panel hidden role="dialog" aria-label="Chat with us on WeChat">
            <p class="wechat-fab__title">Chat with us on WeChat</p>

            @if ($wechat['qr'])
                <img class="wechat-fab__qr" src="{{ $wechat['qr'] }}" alt="WeChat QR code for {{ $wechat['id'] }}" width="160" height="160" loading="lazy">
            @endif

            <div class="wechat-fab__id">
                <span>WeChat ID</span>
                <strong data-wechat-id>{{ $wechat['id'] }}</strong>
            </div>
            <button type="button" class="wechat-fab__copy" data-wechat-copy data-copied-label="Copied!">Copy ID</button>
            <p class="wechat-fab__hint">In WeChat tap <b>+</b> → <b>Add Contacts</b>, then {{ $wechat['qr'] ? 'scan the code or search' : 'search' }} for this ID.</p>
        </div>

        <button type="button" class="wechat-fab__button" data-wechat-toggle aria-haspopup="true" aria-expanded="false" aria-label="Chat with us on WeChat">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M9.2 3C5.2 3 2 5.7 2 9.1c0 1.9 1 3.6 2.6 4.7l-.7 2.1 2.5-1.3c.9.3 1.8.4 2.8.4h.5a5.6 5.6 0 0 1-.2-1.5c0-3.1 2.9-5.6 6.5-5.6h.5C16.4 5 13.1 3 9.2 3Zm-2.7 4.1a.9.9 0 1 1 0 1.8.9.9 0 0 1 0-1.8Zm5.4 0a.9.9 0 1 1 0 1.8.9.9 0 0 1 0-1.8Z" />
                <path d="M22 14.5c0-2.8-2.7-5-6-5s-6 2.2-6 5 2.7 5 6 5c.7 0 1.5-.1 2.1-.3l2 1.1-.6-1.8c1.5-.9 2.5-2.4 2.5-4Zm-7.9-.9a.8.8 0 1 1 0-1.6.8.8 0 0 1 0 1.6Zm3.8 0a.8.8 0 1 1 0-1.6.8.8 0 0 1 0 1.6Z" />
            </svg>
        </button>
    </div>
@endif
