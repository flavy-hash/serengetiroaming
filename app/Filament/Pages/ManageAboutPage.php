<?php

namespace App\Filament\Pages;

use App\Filament\Support\HeroImage;
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
        // Every photo on this page (banner, Our Story, team): up to 30 MB, shrunk to 2560px wide while uploading.
        $image = fn (string $name) => HeroImage::apply(FileUpload::make($name)
            ->disk('public')
            ->directory('about'))
            ->helperText('Up to 30 MB. Photos wider than 2560px are shrunk automatically so the page stays fast.');

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
                                HeroImage::apply($image('banner_image'))
                                    ->label('Banner image')
                                    ->helperText(HeroImage::help('The photo behind the title at the top of the page.')),
                                Textarea::make('meta_description')
                                    ->label('Search engine description')
                                    ->rows(2)
                                    ->maxLength(255),
                            ]),
                        Tab::make('Our Story')
                            ->icon(Heroicon::OutlinedBookOpen)
                            ->schema([
                                $image('intro_image')
                                    ->label('Photo')
                                    ->helperText(HeroImage::help('The framed photo under the banner.')),
                                TextInput::make('intro_heading')
                                    ->label('Heading')
                                    ->placeholder('Who Are We?')
                                    ->maxLength(255),
                                Textarea::make('intro_body')
                                    ->label('Text')
                                    ->rows(8)
                                    ->helperText('Leave a blank line between paragraphs.'),
                                Textarea::make('mission')
                                    ->label('Our Mission (optional)')
                                    ->rows(4),
                                Textarea::make('vision')
                                    ->label('Our Vision (optional)')
                                    ->rows(4),
                            ]),
                        Tab::make('Why Choose Us')
                            ->icon(Heroicon::OutlinedHeart)
                            ->schema([
                                TextInput::make('why_heading')
                                    ->label('Heading')
                                    ->placeholder('Why Travel With Us?')
                                    ->maxLength(255),
                                Textarea::make('why_intro')
                                    ->label('Text above the list')
                                    ->rows(5)
                                    ->helperText('Leave a blank line between paragraphs.'),
                                Repeater::make('highlights')
                                    ->label('The list')
                                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                                    ->schema([
                                        TextInput::make('title')->required()->placeholder('Personalized Safari Experiences'),
                                        Textarea::make('copy')->rows(2),
                                    ])
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add a point'),
                                Textarea::make('closing_text')
                                    ->label('Closing text (optional)')
                                    ->rows(4)
                                    ->helperText('Shown under the list.'),
                            ]),
                        Tab::make('Community')
                            ->icon(Heroicon::OutlinedHomeModern)
                            ->schema([
                                TextInput::make('community_heading')
                                    ->label('Heading')
                                    ->placeholder('Supporting Local Communities')
                                    ->maxLength(255),
                                Textarea::make('community_text')
                                    ->label('Text')
                                    ->rows(8)
                                    ->helperText('Shown on the home page under "Why choose us" and on the About page. Leave a blank line between paragraphs.'),
                                $image('community_image')
                                    ->label('Photo')
                                    ->helperText(HeroImage::help('Shown beside the text on the home page. A photo of local people, crafts or a village visit works well. Until you upload one, a Maasai community photo is used.')),
                            ]),
                        Tab::make('Team')
                            ->icon(Heroicon::OutlinedUsers)
                            ->badge(fn () => count($this->data['team_groups'] ?? []) ?: null)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('team_title')
                                        ->label('Banner title')
                                        ->placeholder('Meet Our Experts’ Team')
                                        ->maxLength(255),
                                    TextInput::make('team_heading')
                                        ->label('Heading')
                                        ->placeholder('Meet Our Professional Team')
                                        ->maxLength(255),
                                ]),
                                HeroImage::apply($image('team_banner_image'))
                                    ->label('Banner image')
                                    ->helperText(HeroImage::help('The photo behind the title on the Our Team page.')),
                                Textarea::make('team_intro')
                                    ->label('Introduction')
                                    ->rows(4)
                                    ->helperText('Shown under the heading. Leave a blank line between paragraphs.'),
                                Repeater::make('team_groups')
                                    ->label('Groups')
                                    ->itemLabel(fn (array $state) => filled($state['title'] ?? null) ? $state['title'] : 'Leadership (no heading)')
                                    ->schema([
                                        TextInput::make('title')
                                            ->placeholder('Our Sales Team')
                                            ->helperText('Leave empty for the first group (the managing director) to show it straight under the introduction.'),
                                        Textarea::make('description')->rows(3),
                                        Repeater::make('members')
                                            ->itemLabel(fn (array $state) => $state['name'] ?? null)
                                            ->schema([
                                                TextInput::make('name')->required(),
                                                TextInput::make('role')->placeholder('Head Guide'),
                                                $image('photo'),
                                                Textarea::make('bio')->rows(3),
                                                TextInput::make('link')->label('Link (LinkedIn, etc.)')->url()->maxLength(255),
                                            ])
                                            ->grid(2)
                                            ->collapsible()
                                            ->defaultItems(0)
                                            ->addActionLabel('Add person'),
                                    ])
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add group'),
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
