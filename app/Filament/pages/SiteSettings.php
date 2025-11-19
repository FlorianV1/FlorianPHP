<?php

namespace App\Filament\Pages;

use App\Models\Settings;
use BackedEnum;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class SiteSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.site-settings';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 100;

    // Data for the form
    public ?array $data = [];

    public function mount(): void
    {
        // This MUST be called in v4
        $this->form->fill([
            'theme' => Settings::get('theme', 'default'),
            'custom_colors' => Settings::get('custom_colors', [
                'app_bg'         => '#0E0E10',
                'surface'        => '#111418',
                'accent'         => '#4A9FFF',
                'accent_hover'   => '#2D7CE8',
                'text_primary'   => '#E7EAF0',
                'text_secondary' => '#A8ACB3',
                'text_muted'     => '#6F737A',
            ]),
            'overlay' => Settings::get('overlay', 'none'),
            'overlay_intensity' => Settings::get('overlay_intensity', 50),
            'sections_order' => Settings::get('sections_order', [
                ['section' => 'hero',       'enabled' => true],
                ['section' => 'now',        'enabled' => true],
                ['section' => 'projects',   'enabled' => true],
                ['section' => 'experience', 'enabled' => true],
                ['section' => 'skills',     'enabled' => true],
                ['section' => 'about',      'enabled' => true],
                ['section' => 'contact',    'enabled' => true],
            ]),
            'navbar_links' => Settings::get('navbar_links', [
                ['label' => 'Projects',   'url' => '#projects',   'enabled' => true],
                ['label' => 'Experience', 'url' => '#experience', 'enabled' => true],
                ['label' => 'Skills',     'url' => '#skills',     'enabled' => true],
                ['label' => 'About',      'url' => '#about',      'enabled' => true],
                ['label' => 'Contact',    'url' => '#contact',    'enabled' => true],
            ]),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Color Theme')
                    ->description('Choose a preset theme or customize colors')
                    ->schema([
                        Select::make('theme')
                            ->options([
                                'default'  => '🌙 Default Dark',
                                'midnight' => '🌌 Midnight Blue',
                                'forest'   => '🌲 Forest Green',
                                'sunset'   => '🌅 Sunset Orange',
                                'lavender' => '💜 Lavender Purple',
                                'custom'   => '🎨 Custom Colors',
                            ])
                            ->default('default')
                            ->live()
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                ColorPicker::make('custom_colors.app_bg')
                                    ->label('Background'),
                                ColorPicker::make('custom_colors.surface')
                                    ->label('Surface'),
                                ColorPicker::make('custom_colors.accent')
                                    ->label('Accent'),
                                ColorPicker::make('custom_colors.accent_hover')
                                    ->label('Accent Hover'),
                                ColorPicker::make('custom_colors.text_primary')
                                    ->label('Text Primary'),
                                ColorPicker::make('custom_colors.text_secondary')
                                    ->label('Text Secondary'),
                            ])
                            ->visible(fn ($get) => $get('theme') === 'custom'),
                    ]),

                Section::make('Seasonal Overlay')
                    ->description('Add a festive overlay effect')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('overlay')
                                    ->options([
                                        'none'     => '❌ None',
                                        'snow'     => '❄️ Snow',
                                        'leaves'   => '🍂 Falling Leaves',
                                        'hearts'   => '💕 Hearts',
                                        'stars'    => '⭐ Stars',
                                        'confetti' => '🎉 Confetti',
                                    ])
                                    ->default('none'),

                                TextInput::make('overlay_intensity')
                                    ->numeric()
                                    ->minValue(10)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->default(50)
                                    ->helperText('Amount of particles'),
                            ]),
                    ]),

                Section::make('Page Sections')
                    ->description('Drag to reorder sections, toggle to show/hide')
                    ->schema([
                        Repeater::make('sections_order')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('section')
                                            ->options([
                                                'hero'       => 'Hero',
                                                'now'        => 'Now / Focus',
                                                'projects'   => 'Projects',
                                                'experience' => 'Experience',
                                                'skills'     => 'Skills',
                                                'about'      => 'About',
                                                'contact'    => 'Contact',
                                            ])
                                            ->disabled()
                                            ->dehydrated(),

                                        Toggle::make('enabled')
                                            ->label('Show')
                                            ->default(true),
                                    ]),
                            ])
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => ucfirst($state['section'] ?? ''))
                            ->addable(false)
                            ->deletable(false),
                    ]),

                Section::make('Navbar Links')
                    ->description('Customize navigation menu links')
                    ->schema([
                        Repeater::make('navbar_links')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('label')
                                            ->required()
                                            ->placeholder('Projects'),

                                        TextInput::make('url')
                                            ->required()
                                            ->placeholder('#projects'),

                                        Toggle::make('enabled')
                                            ->label('Show')
                                            ->default(true),
                                    ]),
                            ])
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'New Link')
                            ->addActionLabel('Add Link'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Settings::set('theme', $data['theme']);
        Settings::set('custom_colors', $data['custom_colors']);
        Settings::set('overlay', $data['overlay']);
        Settings::set('overlay_intensity', $data['overlay_intensity']);
        Settings::set('sections_order', $data['sections_order']);
        Settings::set('navbar_links', $data['navbar_links']);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();

        $this->dispatch('refresh-sidebar');
        $this->dispatch('refresh-topbar');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->action('save')
                ->color('primary'),
        ];
    }
}
