<?php

namespace App\Filament\Resources\Destinations;

use App\Filament\Resources\Destinations\Pages\CreateDestination;
use App\Filament\Resources\Destinations\Pages\EditDestination;
use App\Filament\Resources\Destinations\Pages\ListDestinations;
use App\Models\Destination;
use App\Support\RouteMap;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Parks, mountains and islands: /destinations and a page for each (/destinations/{slug}).
 */
class DestinationResource extends Resource
{
    protected static ?string $model = Destination::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Website Pages';

    protected static ?string $navigationLabel = 'Destinations';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Card')
                    ->description('What visitors see on /destinations.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(150)
                                ->placeholder('Serengeti National Park')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) {
                                    if ($operation === 'create' && blank($get('slug'))) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->label('Web address')
                                ->prefix('/destinations/')
                                ->required()
                                ->maxLength(100)
                                ->alphaDash()
                                ->unique(ignoreRecord: true),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('region')
                                ->maxLength(60)
                                ->datalist(fn () => collect(Destination::REGIONS)->merge(array_values(Destination::regionOptions(false)))->unique()->values()->all())
                                ->placeholder('Northern Circuit')
                                ->helperText('Each region gets a filter button on /destinations.'),
                            TextInput::make('best_time')
                                ->label('Best time to visit')
                                ->maxLength(150)
                                ->placeholder('June – October'),
                        ]),
                        TextInput::make('tagline')
                            ->label('One-line description')
                            ->required()
                            ->maxLength(160)
                            ->placeholder('Home of the Great Migration and endless golden plains.'),
                        FileUpload::make('image')
                            ->label('Photo')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('destinations')
                            ->maxSize(6144)
                            ->helperText('Landscape photo, at least 1600px wide. Up to 6 MB.'),
                    ]),

                Section::make('Destination page')
                    ->schema([
                        Textarea::make('description')
                            ->rows(6)
                            ->maxLength(5000)
                            ->helperText('Leave an empty line between paragraphs.'),
                        Repeater::make('highlights')
                            ->simple(TextInput::make('highlight')->required()->maxLength(120)->placeholder('The Great Migration'))
                            ->defaultItems(0)
                            ->reorderable()
                            ->addActionLabel('Add highlight'),
                        Select::make('places')
                            ->label('Packages that visit')
                            ->multiple()
                            ->searchable()
                            ->options(fn () => collect(RouteMap::places())->map(fn (array $p) => $p['name'])->sort()->all())
                            ->helperText('Packages whose itinerary stops at any of these places are listed on the destination page automatically.'),
                        TextInput::make('meta_description')
                            ->label('Search engine description')
                            ->maxLength(160)
                            ->helperText('Optional. The one-line description is used if empty.'),
                        Toggle::make('is_published')
                            ->label('Show on website')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->state(fn (Destination $record) => $record->imageUrl())
                    ->imageHeight(44)
                    ->square(),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->wrap()
                    ->description(fn (Destination $record) => Str::limit((string) $record->tagline, 80)),
                TextColumn::make('region')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('packages_count')
                    ->label('Packages')
                    ->state(fn (Destination $record) => $record->packages()->count())
                    ->alignCenter(),
                ToggleColumn::make('is_published')
                    ->label('On website'),
            ])
            ->filters([
                SelectFilter::make('region')
                    ->options(fn () => collect(Destination::regionOptions(false))->mapWithKeys(fn ($r) => [$r => $r])->all()),
            ])
            ->recordActions([
                Action::make('viewOnSite')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Destination $record) => $record->url(), shouldOpenInNewTab: true)
                    ->visible(fn (Destination $record) => $record->is_published),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No destinations yet')
            ->emptyStateDescription('Add the parks, mountains and islands you visit. They appear on /destinations.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDestinations::route('/'),
            'create' => CreateDestination::route('/create'),
            'edit' => EditDestination::route('/{record}/edit'),
        ];
    }
}
