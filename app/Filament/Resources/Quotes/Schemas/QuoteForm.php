<?php

namespace App\Filament\Resources\Quotes\Schemas;

use App\Enums\QuoteStatus;
use App\Filament\Pages\SiteSettings;
use App\Models\Quote;
use App\Models\Setting;
use App\Models\TourPage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('customer_name')->label('Name')->required()->maxLength(255),
                        TextInput::make('customer_email')->label('Email')->email()->required()->maxLength(255),
                        TextInput::make('customer_phone')->label('Phone / WhatsApp')->tel()->maxLength(50),
                        TextInput::make('customer_country')->label('Country')->maxLength(100),
                    ]),

                Section::make('Quote')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('reference')
                            ->state(fn (?Quote $record) => $record?->reference)
                            ->placeholder('Given when the quote is saved')
                            ->fontFamily('mono'),
                        Select::make('status')
                            ->options(QuoteStatus::class)
                            ->default(QuoteStatus::Draft)
                            ->selectablePlaceholder(false)
                            ->required(),
                        DatePicker::make('valid_until')
                            ->default(fn () => now()->addDays(Setting::quoteValidityDays()))
                            ->native(false)
                            ->displayFormat('j M Y'),
                        Toggle::make('show_bank_details')
                            ->label('Show bank details on the PDF')
                            ->default(true)
                            ->inline(false)
                            ->helperText(fn () => self::bankHelperText()),
                    ]),

                Section::make('Trip')
                    ->columns(2)
                    ->schema([
                        Select::make('package_id')
                            ->label('Package')
                            ->relationship('package', 'package_name', fn ($query) => $query->ordered())
                            ->getOptionLabelFromRecordUsing(fn (TourPage $p) => collect([$p->package_name, $p->duration, $p->price_from ? 'From $'.number_format($p->price_from) : null])->filter()->implode(' · '))
                            ->searchable(['package_name', 'title'])
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::fillItemsFromPackage($get, $set))
                            ->helperText('When the cost list is empty, picking a package fills it from the package price.')
                            ->columnSpanFull(),
                        TextInput::make('adults')->numeric()->integer()->minValue(1)->maxValue(99)->default(2)->required()->live(onBlur: true),
                        TextInput::make('children')->numeric()->integer()->minValue(0)->maxValue(99)->default(0)->required()->live(onBlur: true),
                        DatePicker::make('travel_start')->label('Start date')->native(false)->displayFormat('j M Y'),
                        DatePicker::make('travel_end')->label('End date')->native(false)->displayFormat('j M Y')->afterOrEqual('travel_start'),
                    ]),

                Section::make('Totals')
                    ->columns(3)
                    ->schema([
                        TextInput::make('discount')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('US$')
                            ->default(0)
                            ->live(onBlur: true),
                        TextEntry::make('subtotal_preview')
                            ->label('Subtotal')
                            ->state(fn (Get $get) => Quote::money(Quote::subtotalOf($get('items')))),
                        TextEntry::make('total_preview')
                            ->label('Total')
                            ->weight('bold')
                            ->state(fn (Get $get) => Quote::money(max(0, Quote::subtotalOf($get('items')) - (float) $get('discount')))),
                    ]),

                Section::make('Itemised costs')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->columns(6)
                            ->defaultItems(0)
                            ->reorderable()
                            ->addActionLabel('Add cost')
                            ->live()
                            ->schema([
                                TextInput::make('description')->required()->maxLength(255)->columnSpan(4),
                                TextInput::make('quantity')->label('Qty')->numeric()->minValue(0)->default(1)->required()->live(onBlur: true),
                                TextInput::make('unit_price')->label('Unit price')->numeric()->minValue(0)->prefix('US$')->required()->live(onBlur: true),
                            ]),
                    ]),

                Section::make('Notes')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('notes')
                            ->hiddenLabel()
                            ->rows(4)
                            ->maxLength(3000)
                            ->placeholder('Printed on the quote, e.g. what is included or optional extras.'),
                    ]),
            ]);
    }

    /**
     * Fill the cost list from the package price, but never overwrite costs already entered.
     */
    public static function fillItemsFromPackage(Get $get, Set $set): void
    {
        $existing = collect($get('items') ?? [])->filter(fn ($i) => is_array($i) && (filled($i['description'] ?? null) || filled($i['unit_price'] ?? null)));
        if ($existing->isNotEmpty()) {
            return;
        }

        $package = TourPage::find($get('package_id'));
        $set('items', Quote::itemsForPackage($package, (int) $get('adults'), (int) $get('children')));
    }

    private static function bankHelperText(): HtmlString
    {
        $accounts = Setting::bankAccounts();
        $link = '<a href="'.e(SiteSettings::getUrl()).'" class="underline">Site settings</a>';

        if (! $accounts) {
            return new HtmlString('No bank accounts are set up yet — add them in '.$link.'.');
        }

        $names = collect($accounts)->map(fn (array $a) => e($a['bank_name'].($a['currency'] ? ' ('.$a['currency'].')' : '')))->implode(', ');

        return new HtmlString('Prints: '.$names.'. Change them in '.$link.'.');
    }
}
