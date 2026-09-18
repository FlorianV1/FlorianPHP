<?php

namespace App\Filament\Portfolio\Resources\Testimonials;

use App\Filament\Portfolio\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Portfolio\Resources\Testimonials\Pages\EditTestimonial;
use App\Filament\Portfolio\Resources\Testimonials\Pages\ListTestimonials;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Testimonials';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChatBubbleLeftRight;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quote')
                ->schema([
                    Textarea::make('quote')
                        ->required()
                        ->rows(4)
                        ->maxLength(1000)
                        ->helperText('Their words, not yours. Keep it short enough to read at a glance.')
                        ->columnSpanFull(),
                ]),

            Section::make('Who said it')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('author_name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('author_role')
                            ->label('Role')
                            ->maxLength(255)
                            ->placeholder('Operations Manager'),

                        TextInput::make('company')
                            ->maxLength(255)
                            ->placeholder('Roadtrip Events'),
                    ]),

                    Select::make('project_id')
                        ->label('Related project')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->helperText('Optional — links the quote to a case study.')
                        ->columnSpanFull(),
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
                TextColumn::make('author_name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('quote')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('—')
                    ->toggleable(),

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
            'index' => ListTestimonials::route('/'),
            'create' => CreateTestimonial::route('/create'),
            'edit' => EditTestimonial::route('/{record}/edit'),
        ];
    }
}
