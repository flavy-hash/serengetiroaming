<?php

namespace App\Filament\Resources\Accommodations;

use App\Enums\AccommodationLevel;
use App\Filament\Resources\Accommodations\Pages\ManageAccommodations;
use App\Models\Accommodation;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
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
 * Lodges, camps and hotels. Shown on /accommodations and offered as a picker in each
 * package itinerary day ("Overnight & meals").
 */
class AccommodationResource extends Resource
{
    protected static ?string $model = Accommodation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static string|UnitEnum|null $navigationGroup = 'Website Pages';

    protected static ?string $navigationLabel = 'Accommodation';

    protected static ?string $modelLabel = 'stay';

    protected static ?string $pluralModelLabel = 'accommodation';

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Serengeti Serena Safari Lodge'),
                Grid::make(3)->schema([
                    TextInput::make('location')
                        ->label('Area')
                        ->required()
                        ->maxLength(100)
                        // Suggestions only: admins can type a new area, which then gets its own filter chip.
                        ->datalist(fn () => collect(Accommodation::AREAS)->merge(array_values(Accommodation::locationOptions(false)))->unique()->sort()->values()->all())
                        ->helperText('Type a new area if it isn\'t suggested.'),
                    TextInput::make('type')
                        ->maxLength(60)
                        ->datalist(Accommodation::TYPES)
                        ->placeholder('Lodge'),
                    Select::make('level')
                        ->options(AccommodationLevel::class)
                        ->placeholder('—'),
                ]),
                Textarea::make('description')
                    ->rows(4)
                    ->maxLength(2000),
                FileUpload::make('image')
                    ->label('Photo')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('accommodations')
                    ->maxSize(5120)
                    ->helperText('Landscape photo, at least 1200px wide. Up to 5 MB.'),
                Toggle::make('is_published')
                    ->label('Show on website')
                    ->default(true),
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
                    ->state(fn (Accommodation $record) => $record->imageUrl())
                    ->imageHeight(44)
                    ->square(),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (Accommodation $record) => collect([$record->type, $record->level?->getLabel()])->filter()->implode(' · ')),
                TextColumn::make('location')
                    ->label('Area')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('On website'),
            ])
            ->filters([
                SelectFilter::make('location')
                    ->label('Area')
                    ->options(fn () => collect(Accommodation::locationOptions(false))->mapWithKeys(fn ($name) => [$name => $name])->all()),
                SelectFilter::make('level')
                    ->options(AccommodationLevel::class),
            ])
            ->recordActions([
                Action::make('viewOnSite')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Accommodation $record) => route('accommodations', ['location' => $record->location_key]), shouldOpenInNewTab: true)
                    ->visible(fn (Accommodation $record) => $record->is_published),
                EditAction::make()
                    // FileUpload drops values that aren't files on the disk (e.g. an image URL):
                    // keep the existing URL when no new photo was uploaded.
                    ->mutateDataUsing(function (array $data, Accommodation $record): array {
                        if (blank($data['image'] ?? null) && Str::startsWith((string) $record->image, ['http://', 'https://'])) {
                            $data['image'] = $record->image;
                        }

                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No stays yet')
            ->emptyStateDescription('Add the lodges, camps and hotels you use. They appear on /accommodations and can be picked in package itineraries.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAccommodations::route('/'),
        ];
    }
}
