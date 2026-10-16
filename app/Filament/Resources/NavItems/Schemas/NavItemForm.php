<?php

namespace App\Filament\Resources\NavItems\Schemas;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Models\TourPage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NavItemForm
{
    public static function configure(Schema $schema): Schema
    {
        $suggestions = self::linkSuggestions();

        return $schema
            ->components([
                Section::make('Menu button')
                    ->columns(2)
                    ->schema([
                        TextInput::make('label')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Safaris'),
                        TextInput::make('href')
                            ->label('Link')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('/safaris')
                            ->datalist($suggestions)
                            ->helperText('A page on this site like /safaris, or a full https:// address.'),
                        Toggle::make('is_visible')
                            ->label('Show in navbar')
                            ->default(true),
                    ]),

                Section::make('Dropdown links')
                    ->description('Leave empty to show a plain link (like Contact) instead of a dropdown menu.')
                    ->schema([
                        Repeater::make('links')
                            ->hiddenLabel()
                            ->itemLabel(fn (array $state) => $state['label'] ?? null)
                            ->collapsible()
                            ->schema([
                                TextInput::make('label')->required()->maxLength(80),
                                TextInput::make('href')
                                    ->label('Link')
                                    ->required()
                                    ->maxLength(255)
                                    ->datalist($suggestions)
                                    ->helperText('Add a filter to open the page already filtered, e.g. /safaris?tier=luxury or /accommodations?location=serengeti.'),
                                Section::make('Hover preview (optional)')
                                    ->description('Shown in the dropdown while a visitor points at this link. Left empty, it is filled in from the page the link opens (package photo and summary, or the style description).')
                                    ->collapsible()
                                    ->collapsed()
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->schema([
                                        TextInput::make('preview_title')->label('Heading')->maxLength(120),
                                        TextInput::make('preview_cta')->label('Button text')->maxLength(50),
                                        Textarea::make('preview_text')->label('Text')->rows(2)->maxLength(300)->columnSpanFull(),
                                        FileUpload::make('preview_image')
                                            ->label('Photo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('menus')
                                            ->maxSize(5120)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->reorderable()
                            ->addActionLabel('Add link'),
                    ]),

                Section::make('Dropdown panel')
                    ->description('The heading, text and photo shown next to the dropdown links.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Heading')
                            ->maxLength(255)
                            ->placeholder('Tanzania Safari Adventures'),
                        TextInput::make('cta_label')
                            ->label('Button text')
                            ->maxLength(50)
                            ->placeholder('Explore Safaris')
                            ->helperText('The button links to the menu link above.'),
                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        FileUpload::make('image')
                            ->label('Photo')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('menus')
                            ->maxSize(5120)
                            ->helperText('Landscape photo, at least 960px wide.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Addresses that exist on the site, offered as suggestions while typing a link.
     *
     * @return array<int, string>
     */
    private static function linkSuggestions(): array
    {
        $links = collect(['/', '/accommodations', '/about', '/about#team', '/reviews', '/faq', '/contact']);

        foreach (TourCategory::cases() as $category) {
            $links->push('/'.$category->value);
        }

        foreach (SafariTier::cases() as $tier) {
            $links->push('/safaris?tier='.$tier->value);
        }

        foreach (array_keys(\App\Models\Accommodation::locationOptions()) as $area) {
            $links->push('/accommodations?location='.$area);
        }

        foreach (TourPage::published()->ordered()->get() as $page) {
            $path = parse_url($page->url(), PHP_URL_PATH) ?: '/';
            $links->push($path);

            foreach ($page->experiences ?? [] as $experience) {
                if (filled($experience['anchor'] ?? null)) {
                    $links->push($path.'#'.$experience['anchor']);
                }
            }
        }

        return $links->unique()->values()->all();
    }
}
