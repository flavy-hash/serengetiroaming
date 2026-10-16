<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value settings edited in the admin under Site Settings → Site settings.
 */
class Setting extends Model
{
    private const CACHE_KEY = 'settings.all';

    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'json',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());

        $value = $all[$key] ?? null;

        return $value === null || $value === '' ? $default : $value;
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Bank accounts with at least a bank, an account name and a number (incomplete ones are never printed).
     *
     * @return array<int, array{bank_name: string, account_name: string, account_number: string, branch: ?string, swift: ?string, currency: ?string}>
     */
    public static function bankAccounts(): array
    {
        return collect(static::get('bank_accounts', []))
            ->filter(fn ($a) => is_array($a) && filled($a['bank_name'] ?? null) && filled($a['account_name'] ?? null) && filled($a['account_number'] ?? null))
            ->map(fn (array $a) => [
                'bank_name' => (string) $a['bank_name'],
                'account_name' => (string) $a['account_name'],
                'account_number' => (string) $a['account_number'],
                'branch' => filled($a['branch'] ?? null) ? (string) $a['branch'] : null,
                'swift' => filled($a['swift'] ?? null) ? (string) $a['swift'] : null,
                'currency' => filled($a['currency'] ?? null) ? (string) $a['currency'] : null,
            ])
            ->values()
            ->all();
    }

    /**
     * Where new enquiries are emailed: the addresses set under Site settings, else the site email.
     *
     * @return array<int, string>
     */
    public static function inquiryRecipients(): array
    {
        $saved = static::get('inquiry_emails', []);
        $list = is_array($saved) ? $saved : preg_split('/[\s,;]+/', (string) $saved);

        $emails = collect($list)
            ->map(fn ($e) => strtolower(trim((string) $e)))
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        return $emails ?: array_values(array_filter([config('site.email')]));
    }

    public static function inquiryEmailsEnabled(): bool
    {
        return (bool) static::get('inquiry_email_enabled', true);
    }

    public static function quoteValidityDays(): int
    {
        return max(1, (int) static::get('quote_validity_days', config('quotes.validity_days')));
    }

    public static function childRate(): float
    {
        return max(0, min(100, (float) static::get('child_rate', config('quotes.child_rate')))) / 100;
    }

    public static function quoteTerms(): string
    {
        return (string) static::get('quote_terms', config('quotes.terms'));
    }

    public static function paymentInstructions(): ?string
    {
        return static::get('payment_instructions');
    }
}
