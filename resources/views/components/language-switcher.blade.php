@php
    $languages = [
        ['code' => 'en', 'label' => 'English'],
        ['code' => 'zh-CN', 'label' => '中文'],
        ['code' => 'fr', 'label' => 'Français'],
        ['code' => 'it', 'label' => 'Italiano'],
        ['code' => 'es', 'label' => 'Español'],
        ['code' => 'ar', 'label' => 'العربية'],
    ];
@endphp

<div class="lang-fab notranslate" data-lang-fab>
    <div class="lang-fab__panel" data-lang-panel hidden role="menu">
        @foreach ($languages as $language)
            <button type="button" class="lang-fab__option" data-lang-option="{{ $language['code'] }}" role="menuitem">
                {{ $language['label'] }}
            </button>
        @endforeach
    </div>

    <button
        type="button"
        class="lang-fab__button"
        data-lang-toggle
        aria-haspopup="true"
        aria-expanded="false"
        aria-label="Change language"
    >
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path d="M3 12h18M12 3c2.5 2.7 3.8 6 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-6-3.8-9s1.3-6.3 3.8-9Z" />
        </svg>
    </button>
</div>
