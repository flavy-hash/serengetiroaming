<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * The legal texts (/policy and /terms-and-conditions). Each is Markdown edited in the admin
 * under Site Settings → Legal Pages, with a default in resources/content until it is edited.
 */
class LegalDocument
{
    public const DOCUMENTS = [
        'privacy' => [
            'setting' => 'privacy_policy',
            'file' => 'privacy-policy.md',
            'title' => 'Privacy Policy',
            'eyebrow' => 'Policy',
        ],
        'booking' => [
            'setting' => 'booking_terms',
            'file' => 'booking-terms.md',
            'title' => 'Booking Terms & Conditions',
            'eyebrow' => 'Terms',
        ],
        'terms' => [
            'setting' => 'terms_conditions',
            'file' => 'terms-and-conditions.md',
            'title' => 'Terms & Conditions',
            'eyebrow' => 'Legal',
        ],
    ];

    public static function markdown(string $key): string
    {
        $doc = self::DOCUMENTS[$key];

        return (string) Setting::get($doc['setting'], self::defaultMarkdown($key));
    }

    public static function defaultMarkdown(string $key): string
    {
        return (string) file_get_contents(resource_path('content/'.self::DOCUMENTS[$key]['file']));
    }

    /**
     * When the text last changed: the admin's last save, or the default file's date.
     */
    public static function updatedAt(string $key): Carbon
    {
        $saved = Setting::where('key', self::DOCUMENTS[$key]['setting'])->value('updated_at');

        return $saved
            ? Carbon::parse($saved)
            : Carbon::createFromTimestamp(filemtime(resource_path('content/'.self::DOCUMENTS[$key]['file'])));
    }

    /**
     * Markdown to HTML with raw HTML stripped and unsafe links (javascript: …) removed.
     */
    public static function toHtml(string $markdown): string
    {
        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10,
        ]);
    }

    /**
     * Everything a page needs to show one document: its HTML (with an id on every "##"
     * heading) and the "On this page" list built from those headings.
     *
     * @return array{key: string, id: string, title: string, eyebrow: string, html: string, toc: array<int, array{id: string, title: string}>, updated_at: Carbon}
     */
    public static function render(string $key): array
    {
        $doc = self::DOCUMENTS[$key];
        $toc = [];
        $used = [];

        $html = preg_replace_callback('#<h2>(.*?)</h2>#s', function (array $m) use ($key, &$toc, &$used) {
            $title = html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5);
            $id = $key.'-'.(Str::slug($title) ?: 'section');
            $base = $id;
            for ($n = 2; isset($used[$id]); $n++) {
                $id = $base.'-'.$n;
            }
            $used[$id] = true;
            $toc[] = ['id' => $id, 'title' => $title];

            return '<h2 id="'.e($id).'">'.$m[1].'</h2>';
        }, self::toHtml(self::markdown($key)));

        return [
            'key' => $key,
            'id' => $key,
            'title' => $doc['title'],
            'eyebrow' => $doc['eyebrow'],
            'html' => (string) $html,
            'toc' => $toc,
            'updated_at' => self::updatedAt($key),
        ];
    }
}
