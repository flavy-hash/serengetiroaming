<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Mail\InquiryReceived;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Where new enquiries are emailed, and the settings used on quotes: bank accounts, payment
 * instructions, validity, child rate and terms.
 * (The privacy policy and other legal texts are under Site Settings → Legal Pages.)
 *
 * @property-read Schema $form
 */
class SiteSettings extends Page
{
    /** Keys saved in the settings table. */
    private const KEYS = ['inquiry_email_enabled', 'inquiry_emails', 'bank_accounts', 'payment_instructions', 'quote_validity_days', 'child_rate', 'quote_terms'];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Site Settings';

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $title = 'Site settings';

    protected static ?string $slug = 'site-settings';

    protected static ?int $navigationSort = 30;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'inquiry_email_enabled' => Setting::inquiryEmailsEnabled(),
            'inquiry_emails' => Setting::inquiryRecipients(),
            'bank_accounts' => Setting::get('bank_accounts', []),
            'payment_instructions' => Setting::paymentInstructions(),
            'quote_validity_days' => Setting::quoteValidityDays(),
            'child_rate' => (int) round(Setting::childRate() * 100),
            'quote_terms' => Setting::quoteTerms(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Enquiry emails')
                    ->description('Every new booking enquiry, contact message, question and instant quote is saved in the admin, shows in the bell at the top right, and is emailed to these addresses. Replying to the email answers the visitor directly.')
                    ->schema([
                        Toggle::make('inquiry_email_enabled')
                            ->label('Email new enquiries')
                            ->live(),
                        TagsInput::make('inquiry_emails')
                            ->label('Send to')
                            ->placeholder('Type an email address and press Enter')
                            ->nestedRecursiveRules(['email:filter', 'max:255'])
                            ->helperText('Leave empty to use '.config('site.email').'. Save, then use "Send test email" at the top to check it arrives.')
                            ->visible(fn (Get $get) => (bool) $get('inquiry_email_enabled')),
                    ]),
                Section::make('Bank accounts')
                    ->description('Printed on quotes under "Payment details", two per row. Accounts without a bank, account name and number are left off.')
                    ->schema([
                        Repeater::make('bank_accounts')
                            ->hiddenLabel()
                            ->itemLabel(fn (array $state): ?string => collect([$state['bank_name'] ?? null, $state['currency'] ?? null])->filter()->implode(' · ') ?: null)
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('Add bank account')
                            ->columns(2)
                            ->schema([
                                TextInput::make('bank_name')->required()->maxLength(100)->placeholder('CRDB Bank'),
                                TextInput::make('account_name')->required()->maxLength(150)->placeholder('Serengeti Roaming African Safaris Ltd'),
                                TextInput::make('account_number')->required()->maxLength(60),
                                TextInput::make('branch')->maxLength(100)->placeholder('Arusha'),
                                TextInput::make('swift')->label('SWIFT')->maxLength(20),
                                TextInput::make('currency')->maxLength(10)->placeholder('USD'),
                            ]),
                        Textarea::make('payment_instructions')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Please use the quote reference as the payment reference. Bank charges are paid by the sender.'),
                    ]),
                Section::make('Quotes')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('quote_validity_days')
                                ->label('Quotes are valid for')
                                ->suffix('days')
                                ->numeric()
                                ->integer()
                                ->minValue(1)
                                ->maxValue(365)
                                ->required(),
                            TextInput::make('child_rate')
                                ->label('Child price')
                                ->helperText('Percentage of the adult package price, used when a quote is filled from a package.')
                                ->suffix('%')
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->maxValue(100)
                                ->required(),
                        ]),
                        Textarea::make('quote_terms')
                            ->label('Terms')
                            ->helperText('One per line. Printed at the bottom of every quote.')
                            ->rows(5)
                            ->maxLength(3000),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testEmail')
                ->label('Send test email')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Send a test enquiry email?')
                ->modalDescription(fn () => 'A sample enquiry email goes to '.implode(', ', Setting::inquiryRecipients()).' (the saved addresses — save first if you changed them).')
                ->modalSubmitActionLabel('Send')
                ->action(function () {
                    $to = Setting::inquiryRecipients();

                    try {
                        Mail::to($to)->send(new InquiryReceived([
                            'kind' => 'Test email',
                            'title' => 'Test: enquiry emails are working',
                            'summary' => 'Test',
                            'fields' => ['Sent from' => 'Admin → Site settings', 'Sent to' => implode(', ', $to)],
                            'message_label' => 'Message',
                            'message' => 'If you can read this, new website enquiries will reach this inbox.',
                            'reply_to' => ['email' => (string) config('mail.from.address'), 'name' => (string) config('mail.from.name')],
                            'admin_url' => BookingResource::getUrl(),
                            'icon' => Heroicon::OutlinedEnvelope,
                        ]));
                    } catch (\Throwable $e) {
                        report($e);

                        Notification::make()
                            ->title('The test email could not be sent')
                            ->body('The mail server said: '.Str::limit($e->getMessage(), 300).' — check the MAIL_ settings in the .env file.')
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Test email sent to '.implode(', ', $to))
                        ->body("If it doesn't arrive within a few minutes, check the spam folder.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save changes')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (self::KEYS as $key) {
            $value = $state[$key] ?? null;
            Setting::put($key, in_array($key, ['bank_accounts', 'inquiry_emails'], true) ? array_values((array) $value) : $value);
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
