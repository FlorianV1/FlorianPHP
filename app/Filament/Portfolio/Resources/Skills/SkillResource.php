<?php

namespace App\Filament\Portfolio\Resources\Skills;

use App\Filament\Portfolio\Resources\Skills\Pages\CreateSkill;
use App\Filament\Portfolio\Resources\Skills\Pages\EditSkill;
use App\Filament\Portfolio\Resources\Skills\Pages\ListSkills;
use App\Filament\Portfolio\Resources\Skills\Schemas\SkillForm;
use App\Filament\Portfolio\Resources\Skills\Tables\SkillsTable;
use App\Models\Skill;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SkillResource extends Resource
{
    protected static ?string $model = Skill::class;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CpuChip;

    public static function form(Schema $schema): Schema
    {
        return SkillForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkillsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSkills::route('/'),
            'create' => CreateSkill::route('/create'),
            'edit' => EditSkill::route('/{record}/edit'),
        ];
    }
}
