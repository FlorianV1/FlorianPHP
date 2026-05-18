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
use Filament\Forms\Components\FileUpload;

class SiteSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.site-settings';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|UnitEnum|null $navigationGroup = 'Portfolio';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Appearance';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'navbar_brand_text' => Settings::get('navbar_brand_text', 'Florian'),
            'navbar_brand_color' => Settings::get('navbar_brand_color', '#ffffff'),
            'favicon' => Settings::get('favicon', null),
            'theme' => Settings::get('theme', 'default'),
            'custom_colors' => Settings::get('custom_colors', [
                'app_bg' => '#0E0E10',
                'surface' => '#111418',
                'accent' => '#4A9FFF',
                'accent_hover' => '#2D7CE8',
                'text_primary' => '#E7EAF0',
                'text_secondary' => '#A8ACB3',
                'text_muted' => '#6F737A',
            ]),
            'sections_order' => Settings::get('sections_order', [
                ['section' => 'hero', 'enabled' => true],
                ['section' => 'now', 'enabled' => true],
                ['section' => 'projects', 'enabled' => true],
                ['section' => 'experience', 'enabled' => true],
                ['section' => 'skills', 'enabled' => true],
                ['section' => 'about', 'enabled' => true],
                ['section' => 'contact', 'enabled' => true],
            ]),
            'navbar_links' => Settings::get('navbar_links', [
                ['label' => 'Projects', 'url' => '#projects', 'enabled' => true],
                ['label' => 'Experience', 'url' => '#experience', 'enabled' => true],
                ['label' => 'Skills', 'url' => '#skills', 'enabled' => true],
                ['label' => 'About', 'url' => '#about', 'enabled' => true],
                ['label' => 'Contact', 'url' => '#contact', 'enabled' => true],
            ]),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Branding')
                    ->description('Navbar brand text, color, and favicon.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('navbar_brand_text')
                                ->label('Brand text')
                                ->placeholder('Florian')
                                ->required(),

                            ColorPicker::make('navbar_brand_color')
                                ->label('Brand text color')
                                ->default('#ffffff'),
                        ]),

                        FileUpload::make('favicon')
                            ->label('Favicon (PNG/ICO, ideally 32×32)')
                            ->image()
                            ->imageEditor()
                            ->directory('favicons')
                            ->preserveFilenames()
                            ->nullable(),
                    ]),

                Section::make('Color Theme')
                    ->description('Choose a preset or build a custom palette.')
                    ->schema([
                        Select::make('theme')
                            ->label('Theme')
                            ->options([
                                'default' => '🌙 Default Dark — Blue',
                                'midnight_red' => '🌙 Default Dark — Red',
                                'midnight' => '🌌 Midnight Blue',
                                'forest' => '🌲 Forest Green',
                                'sunset' => '🌅 Sunset Orange',
                                'lavender' => '💜 Lavender Purple',
                                'custom' => '🎨 Custom Colors',
                            ])
                            ->live()
                            ->default('default')
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                ColorPicker::make('custom_colors.app_bg')->label('Background'),
                                ColorPicker::make('custom_colors.surface')->label('Surface'),
                                ColorPicker::make('custom_colors.accent')->label('Accent'),
                                ColorPicker::make('custom_colors.accent_hover')->label('Accent Hover'),
                                ColorPicker::make('custom_colors.text_primary')->label('Text Primary'),
                                ColorPicker::make('custom_colors.text_secondary')->label('Text Secondary'),
                            ])
                            ->visible(fn($get) => $get('theme') === 'custom'),
                    ]),

                Grid::make(2)->schema([
                    Section::make('Page Sections')
                        ->description('Drag to reorder, toggle visibility.')
                        ->icon('heroicon-o-view-columns')
                        ->columnSpan(1)
                        ->schema([
                            Repeater::make('sections_order')
                                ->label('')
                                ->schema([
                                    Grid::make(2)->schema([
                                        Select::make('section')
                                            ->options([
                                                'hero' => 'Hero',
                                                'now' => 'Now / Focus',
                                                'projects' => 'Projects',
                                                'experience' => 'Experience',
                                                'skills' => 'Skills',
                                                'about' => 'About',
                                                'contact' => 'Contact',
                                            ])
                                            ->disabled()
                                            ->dehydrated(),

                                        Toggle::make('enabled')
                                            ->label('Visible')
                                            ->default(true),
                                    ]),
                                ])
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn(array $state): ?string => ucfirst($state['section'] ?? ''))
                                ->addable(false)
                                ->deletable(false),
                        ]),

                    Section::make('Navbar Links')
                        ->description('Manage and reorder navigation links.')
                        ->icon('heroicon-o-bars-3')
                        ->columnSpan(1)
                        ->schema([
                            Repeater::make('navbar_links')
                                ->label('')
                                ->schema([
                                    TextInput::make('label')
                                        ->required()
                                        ->placeholder('Projects'),

                                    TextInput::make('url')
                                        ->required()
                                        ->placeholder('#projects'),

                                    Toggle::make('enabled')
                                        ->label('Show')
                                        ->default(true)
                                        ->inline(false),
                                ])
                                ->columns(3)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'New Link')
                                ->addActionLabel('Add link'),
                        ]),
                ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $theme = $data['theme'] ?? 'default';

        $palette = $data['custom_colors'] ?? [];

        if ($theme !== 'custom') {
            $palette = match ($theme) {
                'midnight' => [
                    'app_bg' => '#020617', 'surface' => '#030712',
                    'accent' => '#38bdf8', 'accent_hover' => '#0ea5e9',
                    'text_primary' => '#e5e7eb', 'text_secondary' => '#9ca3af', 'text_muted' => '#6b7280',
                ],
                'forest' => [
                    'app_bg' => '#020617', 'surface' => '#022c22',
                    'accent' => '#22c55e', 'accent_hover' => '#16a34a',
                    'text_primary' => '#e5e7eb', 'text_secondary' => '#9ca3af', 'text_muted' => '#6b7280',
                ],
                'sunset' => [
                    'app_bg' => '#111827', 'surface' => '#1f2937',
                    'accent' => '#fb923c', 'accent_hover' => '#f97316',
                    'text_primary' => '#f9fafb', 'text_secondary' => '#e5e7eb', 'text_muted' => '#9ca3af',
                ],
                'lavender' => [
                    'app_bg' => '#020617', 'surface' => '#111827',
                    'accent' => '#a855f7', 'accent_hover' => '#7e22ce',
                    'text_primary' => '#f9fafb', 'text_secondary' => '#c4b5fd', 'text_muted' => '#9ca3af',
                ],
                'midnight_red' => [
                    'app_bg' => '#0E0E10', 'surface' => '#111418',
                    'accent' => '#f97373', 'accent_hover' => '#ef4444',
                    'text_primary' => '#E7EAF0', 'text_secondary' => '#A8ACB3', 'text_muted' => '#6F737A',
                ],
                default => [
                    'app_bg' => '#0E0E10', 'surface' => '#111418',
                    'accent' => '#4A9FFF', 'accent_hover' => '#2D7CE8',
                    'text_primary' => '#E7EAF0', 'text_secondary' => '#A8ACB3', 'text_muted' => '#6F737A',
                ],
            };
        }

        Settings::set('navbar_brand_text', $data['navbar_brand_text'] ?? 'Florian');
        Settings::set('navbar_brand_color', $data['navbar_brand_color'] ?? '#ffffff');
        Settings::set('favicon', $data['favicon'] ?? null);
        Settings::set('theme', $theme);
        Settings::set('custom_colors', $palette);
        Settings::set('sections_order', $data['sections_order'] ?? []);
        Settings::set('navbar_links', $data['navbar_links'] ?? []);

        Notification::make()
            ->title('Appearance saved')
            ->success()
            ->send();

        $this->dispatch('refresh-sidebar');
        $this->dispatch('refresh-topbar');
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
