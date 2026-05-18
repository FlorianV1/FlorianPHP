<?php

namespace App\Filament\Pages;

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
                Grid::make(2)->schema([
                    Section::make('Identity')
                        ->description('Your public-facing name, title and contact info')
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Florian'),

                            Grid::make(2)->schema([
                                TextInput::make('role')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Software Developer')
                                    ->prefixIcon('heroicon-o-briefcase'),

                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('dev@example.com')
                                    ->prefixIcon('heroicon-o-envelope'),
                            ]),

                            Textarea::make('tagline')
                                ->required()
                                ->rows(2)
                                ->placeholder('I build web applications with a focus on reliability and clear code.')
                                ->columnSpanFull(),

                            Textarea::make('subtitle')
                                ->rows(2)
                                ->placeholder('Currently working on modern PHP/Laravel stacks...')
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
                                    ->placeholder('Available for freelance opportunities'),
                            ]),
                        ]),
                ]),

                Grid::make(2)->schema([
                    Section::make('About')
                        ->description('Your full bio shown in the about section')
                        ->columnSpan(2)
                        ->schema([
                            RichEditor::make('about_text')
                                ->label('')
                                ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList'])
                                ->extraAttributes(['style' => 'min-height: 200px;'])
                                ->placeholder('Write 2–3 paragraphs about yourself, your approach to work, and interests...'),
                        ]),

                    Section::make('Social Links')
                        ->description('Your profiles and socials')
                        ->columnSpan(2)
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
