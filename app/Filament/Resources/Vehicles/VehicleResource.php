<?php

namespace App\Filament\Resources\Vehicles;

use App\Filament\Resources\Vehicles\Pages\ManageVehicles;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
 * The company's vehicles, shown on /vehicles (About → Our Vehicles in the menu).
 */
class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'Website Pages';

    protected static ?string $navigationLabel = 'Vehicles';

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(3)->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(150)
                        ->placeholder('Toyota Land Cruiser 4×4')
                        ->columnSpan(2),
                    TextInput::make('seats')
                        ->label('Passenger seats')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->maxValue(60)
                        ->placeholder('7'),
                ]),
                TextInput::make('category')
                    ->label('Type')
                    ->maxLength(60)
                    ->datalist(fn () => collect(Vehicle::CATEGORIES)->merge(Vehicle::query()->whereNotNull('category')->distinct()->pluck('category'))->unique()->sort()->values()->all())
                    ->placeholder('Safari 4×4')
                    ->helperText('e.g. Safari 4×4, Airport transfer — type your own if it isn\'t suggested.'),
                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(1500),
                Repeater::make('features')
                    ->label('Features')
                    ->simple(TextInput::make('feature')->required()->maxLength(120)->placeholder('Pop-up roof for game viewing'))
                    ->defaultItems(0)
                    ->reorderable()
                    ->addActionLabel('Add feature')
                    ->helperText('Shown as a checklist on the vehicle card.'),
                FileUpload::make('image')
                    ->label('Photo')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('vehicles')
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
                    ->state(fn (Vehicle $record) => $record->imageUrl())
                    ->imageHeight(44)
                    ->square(),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->wrap()
                    ->description(fn (Vehicle $record) => Str::limit(implode(' · ', $record->featureList()), 80) ?: null),
                TextColumn::make('category')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('seats')
                    ->placeholder('—')
                    ->alignCenter(),
                ToggleColumn::make('is_published')
                    ->label('On website'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Type')
                    ->options(fn () => Vehicle::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category', 'category')->all()),
            ])
            ->recordActions([
                Action::make('viewOnSite')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Vehicle $record) => route('vehicles').'#'.$record->anchor(), shouldOpenInNewTab: true)
                    ->visible(fn (Vehicle $record) => $record->is_published),
                EditAction::make()
                    // Keep a photo URL when no new file was uploaded (FileUpload drops non-disk values).
                    ->mutateDataUsing(function (array $data, Vehicle $record): array {
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
            ->emptyStateHeading('No vehicles yet')
            ->emptyStateDescription('Add your safari 4×4s and transfer vehicles. They appear on /vehicles (About → Our Vehicles).');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageVehicles::route('/'),
        ];
    }
}
