<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Filament\Pages;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Forms\Components\Actions\Action;


class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationLabel = 'Настройки';

    protected static ?string $modelLabel = 'Настройки';

    protected static ?string $title = 'Настройки';

    protected static ?string $slug = 'nastroyki';

    /**
     * @dev @victormsalatiel
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public ?array $data = [];
    public Setting $setting;

    /**
     * @dev victormsalatiel - Мой инстаграм
     * @return void
     */
    public function mount(): void
    {
        $this->setting = Setting::first();
        $this->form->fill($this->setting->toArray());
    }

    /**
     * @dev victormsalatiel - Мой инстаграм
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Детали сайта')
                    ->schema([
                        TextInput::make('software_name')
                            ->label('Название')
                            ->required()
                            ->maxLength(191),
                        TextInput::make('software_description')
                            ->label('Описание')
                            ->maxLength(191),
                    ])->columns(2),

                Section::make('Логотипы')
                    ->schema([
                        FileUpload::make('software_favicon')
                            ->label('Фавикон')
                            ->placeholder('Загрузите фавикон')
                            ->image(),
                        FileUpload::make('software_logo_white')
                            ->label('Белый логотип')
                            ->placeholder('Загрузите белый логотип')
                            ->image(),
                        FileUpload::make('software_logo_black')
                            ->label('Темный логотип')
                            ->placeholder('Загрузите темный логотип')
                            ->image(),
                    ])->columns(3),

                Section::make('Фон')
                    ->schema([
                        FileUpload::make('software_background')
                            ->label('Фон')
                            ->placeholder('Загрузите фон')
                            ->image()
                        ->columnSpanFull(),
                    ]),

                Section::make('Депозиты и выводы')
                    ->schema([
                        TextInput::make('min_deposit')
                            ->label('Мин. депозит')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('max_deposit')
                            ->label('Макс. депозит')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('min_withdrawal')
                            ->label('Мин. вывод')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('max_withdrawal')
                            ->label('Макс. вывод')
                            ->numeric()
                            ->maxLength(191),
                        TextInput::make('rollover')
                            ->label('Оборот')
                            ->numeric()
                            ->maxLength(191),
                    ])->columns(5),

                Section::make('Футбол')
                    ->description('Настройки футбола')
                    ->schema([
                        TextInput::make('soccer_percentage')
                            ->label('Комиссия футбола (%)')
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),

                        Toggle::make('turn_on_football')
                            ->inline(false)
                            ->label('Включить футбол'),
                    ])->columns(2),

                Section::make('Комиссии')
                    ->description('Настройки доходов платформы')
                    ->schema([
                        TextInput::make('revshare_percentage')
                            ->label('RevShare (%)')
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),
                        Toggle::make('revshare_reverse')
                            ->inline(false)
                            ->label('Включить отрицательный RevShare')
                            ->helperText('Эта опция позволяет партнеру накапливать отрицательный баланс из-за потерь, созданных его рефералами.')
                        ,
                        TextInput::make('ngr_percent')
                            ->label('NGR (%)')
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),
                    ])->columns(3),
                Section::make('Общие данные')
                    ->schema([
                        TextInput::make('initial_bonus')
                            ->label('Начальный бонус (%)')
                            ->numeric()
                            ->suffix('%')
                            ->maxLength(191),
                        TextInput::make('currency_code')
                            ->label('Валюта')
                            ->maxLength(191),
                        Select::make('decimal_format')->options([
                            'dot' => 'Точка',
                        ]),
                        Select::make('currency_position')->options([
                            'left' => 'Слева',
                            'right' => 'Справа',
                        ]),
                        Toggle::make('disable_spin')
                            ->label('Отключить спины')
                        ,
                        Toggle::make('suitpay_is_enable')
                            ->label('SuitPay активен')
                        ,
                        Toggle::make('stripe_is_enable')
                            ->label('Stripe активен')
                        ,
                    ])->columns(4),
            ])
            ->statePath('data');
    }

    /**
     * @dev victormsalatiel - Мой инстаграм
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    /**
     *
     * @dev victormsalatiel - Мой инстаграм
     * @return array
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Отправить'))
                ->action(fn () => $this->submit())
                ->submit('submit')
            //->url(route('filament.admin.pages.dashboard'))
            ,
        ];
    }

    /**
     * @dev victormsalatiel - Мой инстаграм
     * @param $array
     * @return mixed|void
     */
    private function uploadFile($array)
    {
        if(!empty($array) && is_array($array) || !empty($array) && is_object($array)) {
            foreach ($array as $k => $temporaryFile) {
                if ($temporaryFile instanceof TemporaryUploadedFile) {
                    $path = \Helper::upload($temporaryFile);
                    if($path) {
                        return $path['path'];
                    }
                }else{
                    return $temporaryFile;
                }
            }
        }
    }


    /**
     * @dev victormsalatiel - Мой инстаграм
     * @return void
     */
    public function submit(): void
    {
        try {
            if(env('APP_DEMO')) {
                Notification::make()
                    ->title('Внимание')
                    ->body('Вы не можете сделать это изменение в демо-версии')
                    ->danger()
                    ->send();
                return;
            }


            $setting = Setting::first();

            if(!empty($setting)) {

                $favicon   = $this->data['software_favicon'];
                $logoWhite = $this->data['software_logo_white'];
                $logoBlack = $this->data['software_logo_black'];
                $softwareBackground = $this->data['software_background'];

                if (is_array($softwareBackground) || is_object($softwareBackground)) {
                    if(!empty($softwareBackground)) {
                        $this->data['software_background'] = $this->uploadFile($softwareBackground);
                    }
                }

                if (is_array($favicon) || is_object($favicon)) {
                    if(!empty($favicon)) {
                        $this->data['software_favicon'] = $this->uploadFile($favicon);
                    }
                }

                if (is_array($logoWhite) || is_object($logoWhite)) {
                    if(!empty($logoWhite)) {
                        $this->data['software_logo_white'] = $this->uploadFile($logoWhite);
                    }
                }

                if (is_array($logoBlack) || is_object($logoBlack)) {
                    if(!empty($logoBlack)) {
                        $this->data['software_logo_black'] = $this->uploadFile($logoBlack);
                    }
                }

                $envs = DotenvEditor::load(base_path('.env'));

                $envs->setKeys([
                    'APP_NAME' => $this->data['software_name'],
                ]);

                $envs->save();

                if($setting->update($this->data)) {

                    Cache::put('setting', $setting);

                    Notification::make()
                        ->title('Данные изменены')
                        ->body('Данные успешно изменены!')
                        ->success()
                        ->send();

                    redirect(route('filament.admin.pages.dashboard-admin'));

                }
            }


        } catch (Halt $exception) {
            Notification::make()
                ->title('Ошибка при изменении данных!')
                ->body('Ошибка при изменении данных!')
                ->danger()
                ->send();
        }
    }


}
