<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use App\Models\Activity;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageActivities extends ManageRecords
{
    protected static string $resource = ActivityResource::class;

    public function getSubheading(): ?string
    {
        return 'Shown on the homepage below the reviews, in this order. Drag rows to reorder.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(url('/').'#activities', shouldOpenInNewTab: true),
            Action::make('sectionText')
                ->label('Section text')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('gray')
                ->modalHeading('Section heading and intro')
                ->fillForm(fn () => array_combine(
                    array_keys(Activity::SECTION_DEFAULTS),
                    array_values(Activity::sectionText()),
                ))
                ->schema([
                    TextInput::make('activities_eyebrow')->label('Small label')->required()->maxLength(60),
                    TextInput::make('activities_heading')->label('Heading')->required()->maxLength(120),
                    Textarea::make('activities_intro')->label('Intro')->rows(3)->maxLength(400),
                ])
                ->action(function (array $data) {
                    foreach (array_keys(Activity::SECTION_DEFAULTS) as $key) {
                        Setting::put($key, $data[$key] ?? null);
                    }

                    Notification::make()->title('Section text saved')->success()->send();
                }),
            CreateAction::make()
                ->label('Add activity')
                ->mutateDataUsing(function (array $data): array {
                    $data['sort_order'] = (int) Activity::max('sort_order') + 1;

                    return $data;
                }),
        ];
    }
}
