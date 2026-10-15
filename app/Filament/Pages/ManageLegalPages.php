<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\LegalDocument;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use UnitEnum;

/**
 * Privacy Policy, Booking Terms and Terms & Conditions (Markdown), shown on /policy and
 * /terms-and-conditions. Each "## Heading" becomes an entry in the page's "On this page" list.
 *
 * @property-read Schema $form
 */
class ManageLegalPages extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Site Settings';

    protected static ?string $navigationLabel = 'Legal Pages';

    protected static ?string $title = 'Legal Pages';

    protected static ?string $slug = 'legal-pages';

    protected static ?int $navigationSort = 31;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect(LegalDocument::DOCUMENTS)
            ->mapWithKeys(fn (array $doc, string $key) => [$doc['setting'] => LegalDocument::markdown($key)])
            ->all());
    }

    public function form(Schema $schema): Schema
    {
        $where = [
            'privacy' => ['policy', '/policy', 'Keep the section about anonymous visitor statistics.'],
            'booking' => ['policy', '/policy#booking', 'Make sure the deposit, balance and cancellation charges match what you actually use (and your quote terms under Site settings).'],
            'terms' => ['terms', '/terms-and-conditions', 'General terms for using the website and booking.'],
        ];

        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Legal')
                    ->persistTabInQueryString()
                    ->tabs(collect(LegalDocument::DOCUMENTS)->map(function (array $doc, string $key) use ($where) {
                        [$route, $path, $tip] = $where[$key];

                        return Tab::make($doc['title'])
                            ->schema([
                                Text::make(new HtmlString(
                                    'Shown at <a href="'.e(route($route).($key === 'booking' ? '#booking' : '')).'" target="_blank" class="underline">'.e($path).'</a>. '
                                    .'Start each section with <code>## Heading</code> — it is added to the "On this page" list. '.e($tip)
                                )),
                                MarkdownEditor::make($doc['setting'])
                                    ->hiddenLabel()
                                    ->required()
                                    ->maxLength(30000)
                                    ->disableToolbarButtons(['attachFiles', 'codeBlock', 'table']),
                            ]);
                    })->values()->all()),
            ]);
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewPolicy')
                ->label('View /policy')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(route('policy'), shouldOpenInNewTab: true),
            Action::make('viewTerms')
                ->label('View /terms-and-conditions')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(route('terms'), shouldOpenInNewTab: true),
            Action::make('reset')
                ->label('Reset to default text')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('All three texts go back to the original wording. Your edits are lost.')
                ->action(function () {
                    // One by one, so the settings cache is cleared (bulk deletes skip model events).
                    Setting::whereIn('key', array_column(LegalDocument::DOCUMENTS, 'setting'))->get()->each->delete();
                    $this->mount();

                    Notification::make()->title('Legal texts reset')->success()->send();
                }),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (LegalDocument::DOCUMENTS as $key => $doc) {
            $value = (string) ($state[$doc['setting']] ?? '');
            // Only store real changes, so "Last updated" reflects actual edits.
            if (trim($value) !== trim(LegalDocument::markdown($key))) {
                Setting::put($doc['setting'], $value);
            }
        }

        Notification::make()->title('Legal pages saved')->success()->send();
    }
}
