<?php

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\ManageActivities;
use App\Models\Activity;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * The "Top activities" section on the homepage, shown below the reviews.
 */
class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Website Pages';

    protected static ?string $navigationLabel = 'Activities';

    protected static ?int $navigationSort = 11;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(3)->schema([
                    TextInput::make('kicker')
                        ->label('Small line above the title')
                        ->maxLength(60)
                        ->placeholder('06:45 EAT'),
                    TextInput::make('title')
                        ->required()
                        ->maxLength(120)
                        ->placeholder('Walking safaris')
                        ->columnSpan(2),
                ]),
                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(600),
                FileUpload::make('image')
                    ->label('Photo')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('activities')
                    ->maxSize(5120)
                    ->helperText('Landscape photo, at least 1200px wide. Up to 5 MB.'),
                TextInput::make('image_label')
                    ->label('Caption on the photo')
                    ->maxLength(80)
                    ->placeholder('Rufiji River · Nyerere'),
                Grid::make(2)->schema([
                    TextInput::make('link_url')
                        ->label('Link (optional)')
                        ->maxLength(255)
                        ->placeholder('/zanzibar')
                        ->rule('regex:#^(/(?!/)\S*|https?://\S+)$#i')
                        ->validationMessages(['regex' => 'Use a page on this site such as /zanzibar, or a full https:// address.']),
                    TextInput::make('link_label')
                        ->label('Link text')
                        ->maxLength(60)
                        ->placeholder('Zanzibar'),
                ]),
                Toggle::make('is_published')
                    ->label('Show on the homepage')
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
                    ->state(fn (Activity $record) => $record->imageUrl())
                    ->imageHeight(44)
                    ->square(),
                TextColumn::make('title')
                    ->searchable()
                    ->weight('semibold')
                    ->wrap()
                    ->description(fn (Activity $record) => Str::limit((string) $record->description, 90)),
                TextColumn::make('kicker')
                    ->label('Small line')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                ToggleColumn::make('is_published')
                    ->label('On homepage'),
            ])
            ->recordActions([
                EditAction::make()
                    // Keep a photo URL when no new file was uploaded (FileUpload drops non-disk values).
                    ->mutateDataUsing(function (array $data, Activity $record): array {
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
            ->emptyStateHeading('No activities yet')
            ->emptyStateDescription('Add the experiences you offer. They appear on the homepage below the reviews; the section is hidden while there are none.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageActivities::route('/'),
        ];
    }
}
