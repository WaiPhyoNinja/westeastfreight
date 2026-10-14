<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Roles & Permissions';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('roles.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny() && ! in_array($record->name, ['admin', 'customer'], true);
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('guard_name', 'web');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('Role name')->required()->maxLength(125)
                ->unique(table: 'roles', column: 'name', ignoreRecord: true),
            TextInput::make('guard_name')->default('web')->hidden()->required(),
            CheckboxList::make('permissions')->label('Permissions')
                ->relationship('permissions', 'name', fn (Builder $query): Builder => $query->where('guard_name', 'web'))
                ->columns(2)->bulkToggleable()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Role')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('permissions_count')->label('Permissions')->counts('permissions'),
            Tables\Columns\TextColumn::make('users_count')->label('Users')->counts('users'),
        ])->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
