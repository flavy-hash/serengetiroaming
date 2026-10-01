<?php

namespace App\Filament\Pages;

use App\Models\AboutPage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * The About page is a single page, so it is edited here directly rather than
 * through a list of records.
 *
 * @property-read Schema $form
 */
class ManageAboutPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Website Pages';

    protected static ?string $navigationLabel = 'About Us';

    protected static ?string $title = 'About Us';

    protected static ?string $slug = 'pages/about';

    protected static ?int $navigationSort = 10;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $page = AboutPage::current();

        $this->form->fill($page->exists ? $page->attributesToArray() : [
            'title' => 'About Us',
            'banner_text' => 'Locally owned, globally trusted, best prices.',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $image = fn (string $name) => FileUpload::make($name)
            ->image()
            ->disk('public')
            ->directory('about')
            ->maxSize(5120);

        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('About')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Page')
                            ->icon(Heroicon::OutlinedCog6Tooth)
                            ->schema([
                                Toggle::make('is_published')
                                    ->label('Published')
                                    ->helperText('Until published, visitors see a "coming soon" page at /about.'),
                                Grid::make(2)->schema([
                                    TextInput::make('title')->required()->maxLength(255),
                                    TextInput::make('banner_text')->maxLength(255),
                                ]),
                                $image('banner_image')->label('Banner image'),
                                Textarea::make('meta_description')
                                    ->label('Search engine description')
                                    ->rows(2)
                                    ->maxLength(255),
                            ]),
                        Tab::make('Our Story')
                            ->icon(Heroicon::OutlinedBookOpen)
                            ->schema([
                                TextInput::make('intro_heading')
                                    ->label('Heading')
                                    ->placeholder('We are Serengeti Roaming')
                                    ->maxLength(255),
                                Textarea::make('intro_body')
                                    ->label('Text')
                                    ->rows(8)
                                    ->helperText('Leave a blank line between paragraphs.'),
                                $image('intro_image')->label('Image'),
                                Repeater::make('highlights')
                                    ->schema([
                                        TextInput::make('title')->required()->placeholder('Locally owned'),
                                        Textarea::make('copy')->rows(2),
                                    ])
                                    ->grid(3)
                                    ->defaultItems(0)
                                    ->addActionLabel('Add highlight'),
                            ]),
                        Tab::make('Team')
                            ->icon(Heroicon::OutlinedUsers)
                            ->badge(fn () => count($this->data['team'] ?? []) ?: null)
                            ->schema([
                                Repeater::make('team')
                                    ->hiddenLabel()
                                    ->itemLabel(fn (array $state) => $state['name'] ?? null)
                                    ->schema([
                                        TextInput::make('name')->required(),
                                        TextInput::make('role')->placeholder('Head Guide'),
                                        $image('photo'),
                                        Textarea::make('bio')->rows(3),
                                    ])
                                    ->grid(2)
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add team member'),
                            ]),
                        Tab::make('Reviews')
                            ->icon(Heroicon::OutlinedStar)
                            ->badge(fn () => count($this->data['reviews'] ?? []) ?: null)
                            ->schema([
                                Repeater::make('reviews')
                                    ->hiddenLabel()
                                    ->itemLabel(fn (array $state) => $state['name'] ?? null)
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('name')->required(),
                                            TextInput::make('country'),
                                            Select::make('rating')
                                                ->options([5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★'])
                                                ->default(5)
                                                ->required(),
                                        ]),
                                        Textarea::make('quote')->required()->rows(3),
                                    ])
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add review'),
                            ]),
                        Tab::make('FAQ')
                            ->icon(Heroicon::OutlinedQuestionMarkCircle)
                            ->badge(fn () => count($this->data['faqs'] ?? []) ?: null)
                            ->schema([
                                Repeater::make('faqs')
                                    ->hiddenLabel()
                                    ->itemLabel(fn (array $state) => $state['question'] ?? null)
                                    ->schema([
                                        TextInput::make('question')->required(),
                                        Textarea::make('answer')->required()->rows(3),
                                    ])
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add question'),
                            ]),
                    ]),
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
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(url('/about'), shouldOpenInNewTab: true),
        ];
    }

    public function save(): void
    {
        $page = AboutPage::current();
        $page->fill($this->form->getState())->save();

        Notification::make()
            ->title('About page saved')
            ->success()
            ->send();
    }
}
