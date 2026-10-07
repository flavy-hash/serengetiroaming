<?php

namespace App\Filament\Resources\TourPages\Schemas;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Support\Seo;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

/**
 * One form for every tour section. The fields mirror the sections of the
 * public page (resources/views/pages/tour.blade.php), top to bottom.
 */
class TourPageForm
{
    public static function configure(Schema $schema, TourCategory $category): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Page')
                    ->persistTabInQueryString()
                    ->tabs([
                        self::generalTab($category),
                        self::bannerTab($category),
                        self::overviewTab(),
                        self::itineraryTab($category),
                        self::includedTab(),
                        self::exploreTab($category),
                    ]),
            ]);
    }

    private static function image(string $name, TourCategory $category): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->disk('public')
            ->directory("tour-pages/{$category->value}")
            ->maxSize(5120);
    }

    private static function generalTab(TourCategory $category): Tab
    {
        return Tab::make('General')
            ->icon(Heroicon::OutlinedCog6Tooth)
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('title')
                        ->label('Page title')
                        ->helperText("Shown in the banner and browser tab, e.g. \"{$category->getLabel()}\".")
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) {
                            if ($operation === 'create' && blank($get('slug'))) {
                                $set('slug', Str::slug($state));
                            }
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->alphaDash()
                        ->prefix("/{$category->value}/")
                        ->helperText($category->hasListingPage()
                            ? "Every safari has its own page at this address; /{$category->value} lists all packages."
                            : "The first page in the list is served at /{$category->value}; the others at this address.")
                        ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->where('category', $category->value)),
                    ToggleButtons::make('tier')
                        ->label('Style')
                        ->options(SafariTier::class)
                        ->inline()
                        ->required()
                        ->helperText('Used by the Style filter on /safaris, where every package is listed.')
                        ->columnSpanFull(),
                    TextInput::make('package_name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Zanzibar Beach Escape')
                        ->helperText('Used in the overview and the booking form.'),
                    TextInput::make('duration')
                        ->maxLength(100)
                        ->placeholder('4 Days')
                        ->helperText('Start with the number of days — it powers the Duration filter.'),
                ]),
                Section::make('Package card')
                    ->description('How this package looks on /safaris and in "More packages". Duration and style show as tags.')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('card_tagline')
                            ->label('Tagline')
                            ->placeholder('Stone Town & shores')
                            ->helperText('Leave empty to show the location.')
                            ->maxLength(255),
                        TextInput::make('card_highlight')
                            ->label('Highlight')
                            ->placeholder('Free cancellation 48h')
                            ->maxLength(255),
                        Textarea::make('card_summary')
                            ->label('Short description')
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('One or two sentences. Leave empty to use the start of the overview.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Google search result')
                    ->description('How this page appears in Google. Leave empty to use the package name, duration and short description.')
                    ->collapsible()
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('Search engine title')
                            ->maxLength(70)
                            ->placeholder(fn (Get $get) => collect([$get('package_name'), $get('duration')])->filter()->implode(' – ') ?: 'Machame Route – 7 Days')
                            ->live(debounce: 500)
                            ->hint(fn (?string $state) => mb_strlen((string) $state).' / '.Seo::TITLE_LIMIT)
                            ->hintColor(fn (?string $state) => mb_strlen((string) $state) > Seo::TITLE_LIMIT ? 'danger' : 'gray')
                            ->helperText('"| Serengeti Roaming" is added automatically when it fits.'),
                        Textarea::make('meta_description')
                            ->label('Search engine description')
                            ->rows(2)
                            ->maxLength(255)
                            ->live(debounce: 500)
                            ->hint(fn (?string $state) => mb_strlen((string) $state).' / '.Seo::DESCRIPTION_LIMIT)
                            ->hintColor(fn (?string $state) => mb_strlen((string) $state) > Seo::DESCRIPTION_LIMIT ? 'danger' : 'gray')
                            ->helperText('One or two sentences that make people want to click, e.g. what is included and from what price.'),
                    ]),
                Grid::make(2)->schema([
                    Toggle::make('is_published')
                        ->label('Published')
                        ->helperText('Drafts are only visible to logged-in admins.'),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->placeholder('Added to the end')
                        ->helperText('Lowest number is the main page. You can also drag rows in the list.'),
                ]),
            ]);
    }

    private static function bannerTab(TourCategory $category): Tab
    {
        return Tab::make('Banner')
            ->icon(Heroicon::OutlinedPhoto)
            ->schema([
                self::image('banner_image', $category)
                    ->label('Banner image')
                    ->helperText('Wide landscape photo, ideally 1920px wide.'),
                Grid::make(2)->schema([
                    TextInput::make('banner_badge')
                        ->label('Badge')
                        ->placeholder('Beach Holiday')
                        ->maxLength(100),
                    TextInput::make('location')
                        ->placeholder('Zanzibar Archipelago, Tanzania')
                        ->maxLength(255),
                ]),
                TextInput::make('banner_text')
                    ->label('Banner text')
                    ->maxLength(255),
            ]);
    }

    private static function overviewTab(): Tab
    {
        return Tab::make('Overview & Price')
            ->icon(Heroicon::OutlinedCurrencyDollar)
            ->schema([
                Section::make('Package overview')->schema([
                    TextInput::make('overview_heading')
                        ->default('Package Overview')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('overview_body')
                        ->rows(6)
                        ->helperText('Leave a blank line between paragraphs.'),
                ]),
                Section::make('Price box')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('price_from')
                            ->label('Price from')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->helperText('Leave empty to show "Price on request".'),
                        TextInput::make('price_note')
                            ->default('per person sharing, excluding international flights')
                            ->maxLength(255),
                    ]),
                    Repeater::make('facts')
                        ->label('Trip facts')
                        ->schema([
                            TextInput::make('label')->required()->placeholder('Duration'),
                            TextInput::make('value')->placeholder('4 Days · 3 Nights')->helperText('Leave empty to hide.'),
                        ])
                        ->columns(2)
                        ->default([
                            ['label' => 'Duration', 'value' => ''],
                            ['label' => 'Group size', 'value' => ''],
                            ['label' => 'Difficulty', 'value' => ''],
                            ['label' => 'Best time', 'value' => ''],
                        ])
                        ->addActionLabel('Add fact'),
                ]),
            ]);
    }

    private static function itineraryTab(TourCategory $category): Tab
    {
        return Tab::make('Itinerary')
            ->icon(Heroicon::OutlinedCalendarDays)
            ->badge(fn (Get $get) => count($get('itinerary') ?? []) ?: null)
            ->schema([
                Repeater::make('itinerary')
                    ->hiddenLabel()
                    ->itemLabel(fn (array $state): string => trim(($state['day'] ?? '').' • '.($state['title'] ?? ''), ' •') ?: 'New day')
                    ->collapsible()
                    ->collapsed()
                    ->cloneable()
                    ->defaultItems(0)
                    ->addActionLabel('Add day')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('day')->required()->placeholder('Day 1'),
                            TextInput::make('title')->required()->placeholder('Arrival')->columnSpan(2),
                        ]),
                        Toggle::make('open')->label('Expanded when the page loads'),
                        Grid::make(2)->schema([
                            self::image('hero_image', $category)->label('Top image'),
                            TextInput::make('hero_caption')->label('Top image caption'),
                        ]),
                        Textarea::make('copy')->label('Description')->rows(4),
                        TextInput::make('note')->placeholder('Check-in starts at 2:00 PM.'),

                        Section::make('Activity')
                            ->description('Optional highlighted activity for the day.')
                            ->collapsible()
                            ->collapsed(fn (Get $get) => blank($get('activity_title')))
                            ->schema([
                                TextInput::make('activity_title')->label('Title'),
                                self::image('activity_image', $category)->label('Image'),
                                Textarea::make('activity_copy')->label('Description')->rows(3),
                            ]),

                        Grid::make(2)->schema([
                            TextInput::make('meal_plan')->placeholder('Bed and Breakfast'),
                            TextInput::make('accommodation')->placeholder('Stone Town'),
                        ]),

                        Section::make('Accommodation options')
                            ->collapsible()
                            ->collapsed(fn (Get $get) => empty($get('options')))
                            ->schema([
                                Repeater::make('options')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextInput::make('tier')->required()->placeholder('Comfort'),
                                        TextInput::make('label')->required()->placeholder('3-star hotel, Stone Town'),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->addActionLabel('Add option'),
                                Grid::make(2)->schema([
                                    self::image('image', $category)->label('Accommodation image'),
                                    TextInput::make('image_caption')->label('Image caption'),
                                ]),
                            ]),
                    ]),
            ]);
    }

    private static function includedTab(): Tab
    {
        return Tab::make("What's Included")
            ->icon(Heroicon::OutlinedCheckCircle)
            ->schema([
                Grid::make(2)->schema([
                    Repeater::make('included')
                        ->simple(TextInput::make('item')->required())
                        ->defaultItems(0)
                        ->addActionLabel('Add item'),
                    Repeater::make('excluded')
                        ->label('Not included')
                        ->simple(TextInput::make('item')->required())
                        ->defaultItems(0)
                        ->addActionLabel('Add item'),
                ]),
            ]);
    }

    private static function exploreTab(TourCategory $category): Tab
    {
        return Tab::make('Explore Cards')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->badge(fn (Get $get) => count($get('experiences') ?? []) ?: null)
            ->schema([
                TextInput::make('experiences_heading')
                    ->label('Section heading')
                    ->placeholder("Explore {$category->getLabel()}"),
                Repeater::make('experiences')
                    ->label('Cards')
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->collapsible()
                    ->cloneable()
                    ->grid(2)
                    ->defaultItems(0)
                    ->addActionLabel('Add card')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if (blank($get('anchor'))) {
                                    $set('anchor', Str::slug($state));
                                }
                            }),
                        TextInput::make('anchor')
                            ->alphaDash()
                            ->prefix('#')
                            ->helperText('Navbar menu links jump to this, e.g. /'.$category->value.'#stone-town.'),
                        self::image('image', $category),
                        Textarea::make('copy')->rows(3),
                        TextInput::make('price')->placeholder('From $45'),
                    ]),
            ]);
    }
}
