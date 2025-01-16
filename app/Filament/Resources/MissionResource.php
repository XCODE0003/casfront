<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MissionResource\Pages;
use App\Filament\Resources\MissionResource\RelationManagers;
use App\Models\Currency;
use App\Models\GameProvider;
use App\Models\Mission;
use App\Models\Provider;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MissionResource extends Resource
{
    protected static ?string $model = Mission::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'Миссии';

    protected static ?string $modelLabel = 'Миссии';

    protected static ?string $slug = 'центр-миссий';

    /**
     * @dev @victormsalatiel
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                ->schema([
                    Forms\Components\TextInput::make('challenge_name')
                        ->required()
                        ->label('Название миссии')
                        ->placeholder('Введите название миссии')
                        ->columnSpanFull()
                        ->maxLength(191),
                    RichEditor::make('challenge_description')
                        ->label('Описание')
                        ->columnSpanFull()
                        ->placeholder('Введите описание миссии'),

                    RichEditor::make('challenge_rules')
                        ->label('Правила')
                        ->columnSpanFull()
                        ->placeholder('Введите правила миссии'),
                    Select::make('challenge_type')
                        ->default('game')
                        ->label('Тип миссии')
                        ->options([
                            'game' => 'Игра',
                            'wallet' => 'Кошелек',
                            'deposit' => 'Депозит',
                            'affiliate' => 'Партнер',
                        ]),
                    Forms\Components\TextInput::make('challenge_link')
                        ->label('Ссылка миссии')
                        ->maxLength(191),
                    Forms\Components\DateTimePicker::make('challenge_start_date')
                        ->label('Дата начала миссии')
                        ->required(),
                    Forms\Components\DateTimePicker::make('challenge_end_date')
                        ->label('Дата окончания миссии')
                        ->required(),
                    Forms\Components\TextInput::make('challenge_bonus')
                        ->label('Размер бонуса')
                        ->required()
                        ->numeric()
                        ->default(0.00),
                    Forms\Components\TextInput::make('challenge_total')
                        ->label('Всего миссий')
                        ->required()
                        ->numeric()
                        ->default(1),
                    Select::make('challenge_currency')
                        ->label('Валюта по умолчанию')
                        ->required()
                        ->options(Currency::all()->pluck('code', 'id'))
                        ->reactive()
                        ->default(Wallet::where('active', 1)->first()->currency)
                        ->searchable(),
                    Select::make('challenge_provider')
                        ->label('Провайдер')
                        ->options(Provider::all()->pluck('name', 'id'))
                        ->reactive()
                        ->searchable(),
                    Forms\Components\TextInput::make('challenge_gameid')
                        ->label('ID игры')
                        ->placeholder('Введите ID игры, вы можете найти ID в списке игр')
                        ->columnSpanFull()
                        ->maxLength(191),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('challenge_name')
                    ->label('Название')
                    ->searchable(),
                Tables\Columns\TextColumn::make('challenge_type')
                    ->label('Тип')
                    ->searchable(),
                Tables\Columns\TextColumn::make('challenge_link')
                    ->label('Ссылка')
                    ->searchable(),
                Tables\Columns\TextColumn::make('challenge_start_date')
                    ->label('Дата начала')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('challenge_end_date')
                    ->label('Дата окончания')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('challenge_bonus')
                    ->label('Размер приза')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('challenge_total')
                    ->label('Всего')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('challenge_currency')
                    ->label('Валюта')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return string[]
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMissions::route('/'),
            'create' => Pages\CreateMission::route('/create'),
            'edit' => Pages\EditMission::route('/{record}/edit'),
        ];
    }
}
