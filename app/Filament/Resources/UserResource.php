<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Widgets\UserOverview;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Администрирование';

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $modelLabel = 'Пользователи';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * @dev @victormsalatiel
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * @param Model $record
     * @return string
     */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    /**
     * @return string[]
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    /**
     * @param Model $record
     * @return FilamentPageSidebar
     */
    public static function sidebar(Model $record): FilamentPageSidebar
    {
        return FilamentPageSidebar::make()
            ->setTitle($record->name)
            ->setDescription($record->created_at)
            ->setNavigationItems([
                PageNavigationItem::make(__('base.list_user'))
                    ->translateLabel()
                    ->url(static::getUrl('index'))->icon('heroicon-o-user-group')
                    ->isActiveWhen(function () {
                        return request()->routeIs(static::getRouteBaseName() . '.list-users');
                    }),
                PageNavigationItem::make(__('base.view_user'))
                    ->translateLabel()
                    ->url(static::getUrl('view', ['record' => $record->id]))->icon('heroicon-o-user')
                    ->isActiveWhen(function () {
                        return request()->routeIs(static::getRouteBaseName() . '.view');
                    }),
                PageNavigationItem::make(__('base.edit_user'))
                    ->translateLabel()
                    ->url(static::getUrl('edit', ['record' => $record->id]))
                    ->icon('heroicon-o-pencil-square')
                    ->isActiveWhen(function () {
                        return request()->routeIs(static::getRouteBaseName() . '.edit');
                    }),
                PageNavigationItem::make(__('base.change_password'))
                    ->translateLabel()
                    ->url(static::getUrl('password.change', ['record' => $record->id]))
                    ->icon('heroicon-o-key')
                    ->isActiveWhen(function () {
                        return request()->routeIs(static::getRouteBaseName() . '.password.change');
                    }),

            ]);
    }


    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->placeholder('Введите имя')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('email')
                            ->label('Эл. почта')
                            ->placeholder('Введите эл. почту')
                            ->email()
                            ->required()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('cpf')
                            ->label('ИНН')
                            ->placeholder('Введите ИНН')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->placeholder('Введите телефон')
                            ->maxLength(191),
                        Forms\Components\Select::make('inviter')
                            ->label('Партнер')
                            ->placeholder('Выберите партнера')
                            ->relationship(name: 'affiliate', titleAttribute: 'name')
                            ->options(
                                fn($get) => User::query()->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Подтверждение эл. почты'),
                    ])->columns(3),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('affiliate_revenue_share')
                            ->label('Доля выручки (%)')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('affiliate_revenue_share_fake')
                            ->label('Фиктивная доля выручки (%)')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('affiliate_cpa')
                            ->label('CPA')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('affiliate_baseline')
                            ->label('Базовая линия')
                            ->required()
                            ->numeric(),
                    ])->columns(4),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Toggle::make('banned')
                            ->label('Заблокирован')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_demo_agent')
                            ->label('Инфлюенсер')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('status')
                            ->label('Статус')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('wallet.total_balance')
                    ->label('Баланс')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновление')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Создано с'),
                        DatePicker::make('created_until')->label('Создано по'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Создано с ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Создано по ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('details')
                    ->label('Подробности')
                    ->icon('heroicon-o-chart-bar')
                    ->color('gray')
                    ->action(function (User $user) {
                        return redirect()->to(route('filament.admin.resources.users.detail', ['record' => $user]));
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * @return string[]
     */
    public static function getWidgets(): array
    {
        return [
            UserOverview::class,
        ];
    }

    /**
     * @return array|\Filament\Resources\Pages\PageRegistration[]
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}/view'),
            'detail' => Pages\DetailUser::route('/{record}/detail'),
            'password.change' => Pages\ChangePasswordUser::route('/{record}/password/change'),
        ];
    }
}
