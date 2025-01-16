<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BonusSetting extends Page implements HasForms
{
    use HasPageSidebar, InteractsWithForms;

    protected static string $resource = SettingResource::class;

    protected static string $view = 'filament.resources.setting-resource.pages.bonus-setting';

    /**
     * @dev @victormsalatiel
     * @param Model $record
     * @return bool
     */
    public static function canView(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * @return string|Htmlable
     */
    public function getTitle(): string | Htmlable
    {
        return __('VIP Бонус');
    }

    public Setting $record;
    public ?array $data = [];

    /**
     * @dev victormsalatiel - Мой инстаграм
     * @return void
     */
    public function mount(): void
    {
        $setting = Setting::first();
        $this->record = $setting;
        $this->form->fill($setting->toArray());
    }

    /**
     * @return void
     */
    public function save()
    {
        try {
            if(env('APP_DEMO')) {
                Notification::make()
                    ->title('Внимание')
                    ->body('Вы не можете внести это изменение в демо-версии')
                    ->danger()
                    ->send();
                return;
            }

            $setting = Setting::find($this->record->id);

            if($setting->update($this->data)) {
                Cache::put('setting', $setting);

                Notification::make()
                    ->title('Данные изменены')
                    ->body('Данные успешно изменены!')
                    ->success()
                    ->send();

                redirect(route('filament.admin.resources.settings.bonus', ['record' => $this->record->id]));

            }
        } catch (Halt $exception) {
            return;
        }
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
                Section::make('Настройка бонуса')
                    ->description('Форма настройки бонусов платформы')
                    ->schema([
                        TextInput::make('bonus_vip')
                            ->label('VIP Бонус')
                            ->placeholder('Определите VIP бонус, сколько бонусов начисляется за каждый вложенный рубль/доллар.')
                            ->numeric()
                            ->helperText('Это значение бонуса, связанное с каждым депозитом в 1 рубль. Например, когда пользователь делает депозит в 1 рубль, он получает соответствующую сумму в VIP Бонусах.')
                            ->maxLength(191),
                        Toggle::make('activate_vip_bonus')
                            ->inline(false)
                            ->label('Включить/Отключить VIP Бонус'),
                    ])->columns(2)
            ])
            ->statePath('data') ;
    }
}
