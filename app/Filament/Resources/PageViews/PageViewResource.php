<?php

namespace App\Filament\Resources\PageViews;

use App\Filament\Resources\PageViews\Pages\ListPageViews;
use App\Models\PageView;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Read-only log of anonymous page views (written by the LogPageView middleware).
 */
class PageViewResource extends Resource
{
    protected static ?string $model = PageView::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?string $navigationLabel = 'Visitor activity';

    protected static ?string $modelLabel = 'page view';

    protected static ?string $slug = 'insights/visitors';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->since()
                    ->dateTimeTooltip('j M Y, H:i')
                    ->sortable(),
                TextColumn::make('page_type')
                    ->label('Page')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst(str_replace(['-', '_', '.'], ' ', $state)) : 'Page')
                    ->description(fn (PageView $record) => $record->path),
                TextColumn::make('package.package_name')
                    ->label('Tour viewed')
                    ->placeholder('—')
                    ->limit(32),
                IconColumn::make('device')
                    ->icon(fn (?string $state) => match ($state) {
                        'mobile' => Heroicon::OutlinedDevicePhoneMobile,
                        'tablet' => Heroicon::OutlinedDeviceTablet,
                        default => Heroicon::OutlinedComputerDesktop,
                    })
                    ->tooltip(fn (?string $state) => ucfirst((string) $state))
                    ->color('gray'),
                TextColumn::make('country')
                    ->formatStateUsing(fn (?string $state) => PageView::countryLabel($state) ?? '—')
                    ->placeholder('—'),
                TextColumn::make('referrer_host')
                    ->label('Came from')
                    ->placeholder('Direct / unknown'),
                TextColumn::make('visitor_hash')
                    ->label('Visitor ID')
                    ->fontFamily('mono')
                    ->tooltip('Changes every day; cannot be traced back to a person.')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('date')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))),
                SelectFilter::make('page_type')
                    ->label('Page')
                    ->options(fn () => PageView::query()->whereNotNull('page_type')->distinct()->orderBy('page_type')->pluck('page_type', 'page_type')->map(fn ($t) => ucfirst($t))->all()),
                SelectFilter::make('package_id')
                    ->label('Tour')
                    ->relationship('package', 'package_name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('device')
                    ->options(['mobile' => 'Mobile', 'tablet' => 'Tablet', 'desktop' => 'Desktop']),
                SelectFilter::make('country')
                    ->options(fn () => PageView::query()->whereNotNull('country')->distinct()->pluck('country')
                        ->mapWithKeys(fn (string $c) => [$c => PageView::countryLabel($c) ?? $c])->sort()->all())
                    ->searchable(),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPageViews::route('/'),
        ];
    }
}
