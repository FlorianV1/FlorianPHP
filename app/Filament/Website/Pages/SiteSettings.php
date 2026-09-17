<?php

namespace App\Filament\Website\Pages;

use App\Http\Controllers\PortfolioController;
use App\Models\Settings;
use App\Support\SiteBranding;
use BackedEnum;
use Filament\Forms\Components\Textarea;
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
            'site_branding' => SiteBranding::all(),
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
            // Sections added after this site was first configured are missing
            // from the stored order; append them so they are manageable here
            // exactly as the frontend appends them when rendering.
            'sections_order' => (function () {
                $stored = Settings::get('sections_order', []);
                $stored = is_array($stored) ? $stored : [];
                $keys = collect($stored)->pluck('section')->all();

                foreach (PortfolioController::defaultSectionsOrder() as $default) {
                    if (! in_array($default['section'], $keys, true)) {
                        $stored[] = $default;
                    }
                }

                return $stored;
            })(),
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
                Section::make('Identity')
                    ->description('The name, logo and terminal prompt used across the whole site. These used to be hardcoded in three different Blade files.')
                    ->schema([
                        TextInput::make('site_branding.full_name')
                            ->label('Full name')
                            ->required()
                            ->helperText('Used in the hero headline, the page title, the footer and the JSON-LD Person schema.')
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('site_branding.logo_prefix')
                                ->label('Logo prefix')
                                ->placeholder('~/')
                                ->helperText('The dimmed part of the logo.'),

                            TextInput::make('site_branding.logo_text')
                                ->label('Logo text')
                                ->placeholder('florian.dev')
                                ->helperText('Navbar and footer read the same value.'),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('site_branding.terminal_user')
                                ->label('Terminal user')
                                ->placeholder('florian'),

                            TextInput::make('site_branding.terminal_host')
                                ->label('Terminal host')
                                ->placeholder('dev'),

                            TextInput::make('site_branding.terminal_path')
                                ->label('Terminal path')
                                ->placeholder('~/portfolio'),
                        ]),
                    ]),

                Section::make('Search & social')
                    ->description('How the site looks in Google results and when a link is shared.')
                    ->schema([
                        TextInput::make('site_branding.site_title')
                            ->label('Page title')
                            ->maxLength(70)
                            ->helperText('Leave blank to use "Full name — role". Aim for under 60 characters.')
                            ->columnSpanFull(),

                        Textarea::make('site_branding.meta_description')
                            ->label('Meta description')
                            ->rows(2)
                            ->maxLength(200)
                            ->helperText('Leave blank to use the profile tagline. Aim for 150–160 characters.')
                            ->columnSpanFull(),

                        FileUpload::make('site_branding.og_image')
                            ->label('Social share image')
                            ->image()
                            ->imageEditor()
                            ->directory('og')
                            ->helperText('1200 × 630 px. Used for og:image and the Twitter card.')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Navbar')
                    ->description('Navbar brand text, color, and favicon.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('navbar_brand_text')
                                ->label('Brand text override')
                                ->placeholder('Florian')
                                ->helperText('Leave blank to use the logo text above.'),

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
                                                'services' => 'Services',
                                                'now' => 'Now / Focus',
                                                'projects' => 'Projects',
                                                'testimonials' => 'Testimonials',
                                                'experience' => 'Experience',
                                                'education' => 'Education',
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

        Settings::set(SiteBranding::KEY, array_merge(
            SiteBranding::defaults(),
            array_filter($data['site_branding'] ?? [], fn ($v) => $v !== null && $v !== '')
        ));
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
