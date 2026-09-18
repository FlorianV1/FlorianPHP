<?php

namespace App\Filament\Portfolio\Pages;

use App\Models\Profile;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use UnitEnum;

class ProfilePage extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.site-settings';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Profile';

    protected static ?string $title = 'Profile';

    public ?array $data = [];

    public function mount(): void
    {
        $profile = Profile::first();
        $this->form->fill($profile?->toArray() ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([

                // ── IDENTITY ──────────────────────────────────────────────────
                Section::make('Identity')
                    ->description('Your public-facing name, title and contact info.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Florian'),

                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->placeholder('dev@example.com')
                                ->prefixIcon('heroicon-o-envelope'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('role')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Software Developer')
                                ->prefixIcon('heroicon-o-briefcase'),

                            TextInput::make('location')
                                ->maxLength(255)
                                ->placeholder('Netherlands')
                                ->prefixIcon('heroicon-o-map-pin'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('location_timezone')
                                ->label('Timezone')
                                ->maxLength(100)
                                ->placeholder('UTC+1 / CET'),

                            TextInput::make('subtitle')
                                ->maxLength(255)
                                ->placeholder('Currently working on modern PHP/Laravel stacks...'),
                        ]),

                        Textarea::make('tagline')
                            ->required()
                            ->rows(2)
                            ->placeholder('I build web applications with a focus on reliability and clear code.')
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            Toggle::make('status_available')
                                ->label('Available for work')
                                ->default(true)
                                ->inline(false)
                                ->live(),

                            TextInput::make('status_text')
                                ->label('Status message')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('available for projects'),
                        ]),
                    ]),

                // ── HERO CTA BUTTONS ──────────────────────────────────────────
                Section::make('Hero — Call to Action Buttons')
                    ->description('The two buttons that appear below the headline in the hero section.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('hero_cta_primary_label')
                            ->label('Primary button label')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('View my work'),

                        TextInput::make('hero_cta_primary_url')
                            ->label('Primary button URL')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('#projects'),

                        TextInput::make('hero_cta_secondary_label')
                            ->label('Secondary button label')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Get in touch'),

                        TextInput::make('hero_cta_secondary_url')
                            ->label('Secondary button URL')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('#contact'),
                    ]),

                // ── ABOUT STATS ───────────────────────────────────────────────
                Section::make('About — Stats')
                    ->description('Three highlight numbers shown below the bio (e.g. 18k+ monthly players).')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('stat_1_value')
                                ->label('Stat 1 — Value')
                                ->maxLength(20)
                                ->placeholder('18k+'),
                            TextInput::make('stat_1_label')
                                ->label('Stat 1 — Label')
                                ->maxLength(50)
                                ->placeholder('monthly players'),

                            TextInput::make('stat_2_value')
                                ->label('Stat 2 — Value')
                                ->maxLength(20)
                                ->placeholder('2.2k+'),
                            TextInput::make('stat_2_label')
                                ->label('Stat 2 — Label')
                                ->maxLength(50)
                                ->placeholder('discord members'),

                            TextInput::make('stat_3_value')
                                ->label('Stat 3 — Value')
                                ->maxLength(20)
                                ->placeholder('2+'),
                            TextInput::make('stat_3_label')
                                ->label('Stat 3 — Label')
                                ->maxLength(50)
                                ->placeholder('years experience'),
                        ]),
                    ]),

                // ── ABOUT INFO CARDS ──────────────────────────────────────────
                Section::make('About — Info Cards')
                    ->description('The three side cards in the about section (stack values).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('about_stack_primary')
                            ->label('Primary stack')
                            ->maxLength(100)
                            ->placeholder('PHP / Laravel'),

                        TextInput::make('about_stack_secondary')
                            ->label('Supporting stack')
                            ->maxLength(100)
                            ->placeholder('+ Vue.js, MySQL, Docker'),
                    ]),

                // ── CONTACT ───────────────────────────────────────────────────
                Section::make('Contact — Intro')
                    ->description('Short paragraph shown on the left of the contact section.')
                    ->schema([
                        Textarea::make('contact_intro')
                            ->label('')
                            ->rows(3)
                            ->placeholder('If you want to talk about work, collaboration, or just an idea — I\'m all ears.')
                            ->columnSpanFull(),
                    ]),

                // ── FOOTER ────────────────────────────────────────────────────
                Section::make('Footer')
                    ->schema([
                        TextInput::make('footer_tagline')
                            ->label('Footer tagline')
                            ->maxLength(255)
                            ->placeholder('Built with Laravel & love.'),
                    ]),

                // ── ABOUT BIO ─────────────────────────────────────────────────
                Section::make('About — Bio')
                    ->description('Your full bio shown in the about section.')
                    ->schema([
                        RichEditor::make('about_text')
                            ->label('')
                            ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList'])
                            ->placeholder('Write 2–3 paragraphs about yourself...'),
                    ]),

                // ── SOCIAL LINKS ──────────────────────────────────────────────
                Section::make('Social Links')
                    ->description('Your profiles and socials shown in the hero and footer.')
                    ->schema([
                        Repeater::make('social_links')
                            ->label('')
                            ->schema([
                                TextInput::make('platform')
                                    ->required()
                                    ->placeholder('GitHub'),

                                TextInput::make('url')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://github.com/you'),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add link')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['platform'] ?? null),
                    ]),

            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $profile = Profile::first();
        if ($profile) {
            $profile->update($data);
        } else {
            Profile::create($data);
        }

        Notification::make()
            ->title('Profile saved')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->action('save')
                ->color('primary'),
        ];
    }
}
