<?php

namespace App\Http\Controllers;

use App\Enums\QuoteSource;
use App\Enums\QuoteStatus;
use App\Enums\TourCategory;
use App\Filament\Resources\Quotes\QuoteResource;
use App\Mail\QuoteMail;
use App\Models\Quote;
use App\Models\Setting;
use App\Models\TourPage;
use App\Models\User;
use App\Support\QuotePdf;
use Filament\Actions\Action;
use Filament\Notifications\Notification as AdminNotification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class InstantQuoteController extends Controller
{
    /** Errors go in their own bag so they only show in the instant quote dialog. */
    public const ERROR_BAG = 'instantQuote';

    /**
     * POST /{category}/{slug}/quote — the "Get an Instant Quote" dialog on a package page.
     */
    public function store(Request $request, string $category, string $slug): RedirectResponse
    {
        $page = TourPage::where('category', TourCategory::from($category))
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        abort_unless($page->price_from, 404);

        $back = $page->url().'#instant-quote';

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'travel_date' => ['required', 'date', 'after_or_equal:today'],
            'adults' => ['required', 'integer', 'min:1', 'max:30'],
            'children' => ['nullable', 'integer', 'min:0', 'max:30'],
            'website' => ['prohibited'], // honeypot: hidden from people, filled in by bots
        ], [
            'travel_date.after_or_equal' => 'Please choose a travel date from today onwards.',
            'website.prohibited' => 'Something went wrong — please try again.',
        ]);

        if ($validator->fails()) {
            return redirect()->to($back)->withErrors($validator, self::ERROR_BAG)->withInput($request->except('website'));
        }

        $data = $validator->validated();
        $adults = (int) $data['adults'];
        $children = (int) ($data['children'] ?? 0);
        $start = Carbon::parse($data['travel_date']);
        $days = $page->days();

        $quote = Quote::create([
            'source' => QuoteSource::Website,
            'status' => QuoteStatus::Sent,
            'package_id' => $page->getKey(),
            'customer_name' => $data['name'],
            'customer_email' => $data['email'],
            'customer_phone' => $data['phone'] ?? null,
            'customer_country' => $data['country'] ?? null,
            'adults' => $adults,
            'children' => $children,
            'travel_start' => $start,
            'travel_end' => $days ? $start->copy()->addDays($days - 1) : null,
            'items' => Quote::itemsForPackage($page, $adults, $children),
            'notes' => config('quotes.instant_note'),
            'valid_until' => now()->addDays(Setting::quoteValidityDays()),
        ]);

        $this->tellTheOffice($quote);

        return redirect()->to($back)->with('instant_quote', [
            'reference' => $quote->reference,
            'total' => Quote::money($quote->total),
            'download_url' => URL::temporarySignedRoute('quotes.download', now()->addDays(7), ['quote' => $quote]),
        ]);
    }

    /**
     * GET /quotes/{quote}/pdf — only through the signed link given after an instant quote.
     */
    public function download(Quote $quote): Response
    {
        return QuotePdf::make($quote)->download($quote->pdfFilename());
    }

    /**
     * Email the office the PDF and notify admins in the panel. Neither may stop the visitor
     * getting their quote, and both are sent now: shared hosting often has no queue worker.
     */
    private function tellTheOffice(Quote $quote): void
    {
        if (Setting::inquiryEmailsEnabled() && ($to = Setting::inquiryRecipients())) {
            try {
                Mail::to($to)->send(new QuoteMail($quote, forOffice: true));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            $notification = AdminNotification::make()
                ->title("New instant quote {$quote->reference}")
                ->body($quote->customer_name.' · '.($quote->package?->package_name ?? 'Package').' · '.Quote::money($quote->total))
                ->icon(Heroicon::OutlinedDocumentCurrencyDollar)
                ->actions([
                    Action::make('view')
                        ->label('Open quote')
                        ->url(QuoteResource::getUrl('edit', ['record' => $quote]))
                        ->markAsRead(),
                ]);

            Notification::sendNow(User::all(), $notification->toDatabase());
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
