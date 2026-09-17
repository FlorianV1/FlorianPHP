<?php

namespace App\Filament\Website\Resources\Stats;

use App\Filament\Website\Resources\Stats\Pages\CreateStat;
use App\Filament\Website\Resources\Stats\Pages\EditStat;
use App\Filament\Website\Resources\Stats\Pages\ListStats;
use App\Models\Stat;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class StatResource extends Resource
{
    protected static ?string $model = Stat::class;
    protected static string|UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationLabel = 'Stats';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Number')
                ->description('Whole numbers only. The suffix is where "+" or "k+" goes.')
                ->schema([
                    Select::make('auto_source')
                        ->label('Source')
                        ->options(Stat::SOURCES)
                        ->default('manual')
                        ->native(false)
                        ->live()
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        TextInput::make('value')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText(fn ($get) => $get('auto_source') === 'manual'
                                ? 'Shown as typed.'
                                : 'Only used as a fallback — the live number is calculated.'),

                        TextInput::make('suffix')
                            ->maxLength(10)
                            ->placeholder('+')
                            ->helperText('Appended to the number, e.g. "+" or "k+"'),
                    ]),
                ]),

            Section::make('Label')
                ->description('Both forms are required so the label always agrees with the number.')
                ->columns(2)
                ->schema([
                    TextInput::make('label_singular')
                        ->label('Singular')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('current project'),

                    TextInput::make('label_plural')
                        ->label('Plural')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('current projects'),
                ]),

            Section::make('Display')
                ->columns(2)
                ->schema([
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower = shown first'),

                    Toggle::make('is_active')
                        ->label('Show on portfolio')
                        ->default(true)
                        ->inline(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_value')
                    ->label('Value')
                    ->state(fn (Stat $record) => $record->display_value),

                TextColumn::make('display_label')
                    ->label('Label')
                    ->state(fn (Stat $record) => $record->display_label),

                TextColumn::make('auto_source')
                    ->label('Source')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'projects_count' => 'auto: projects',
                        'years_experience' => 'auto: years',
                        default => 'manual',
                    })
                    ->color(fn (string $state) => $state === 'manual' ? 'gray' : 'info'),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->width('80px'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStats::route('/'),
            'create' => CreateStat::route('/create'),
            'edit' => EditStat::route('/{record}/edit'),
        ];
    }
}
