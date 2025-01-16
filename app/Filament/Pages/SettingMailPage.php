<?php

namespace App\Filament\Pages;

use App\Models\SettingMail;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class SettingMailPage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.setting-mail-page';

    public ?array $data = [];
    public SettingMail $setting;

    /**
     * @dev @victormsalatiel
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * @return void
     */
    public function mount(): void
    {
        $smtp = SettingMail::first();
        if(!empty($smtp)) {
            $this->setting = $smtp;
            $this->form->fill($this->setting->toArray());
        }else{
            $this->form->fill();
        }
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('SMTP')
                    ->description('Настройки учетных данных для SMTP')
                    ->schema([
                        TextInput::make('software_smtp_type')
                            ->label('Почтовая программа')
                            ->placeholder('Введите почтовую программу (smtp)')
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_host')
                            ->label('Хост')
                            ->placeholder('Введите ваш почтовый хост')
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_port')
                            ->label('Порт')
                            ->placeholder('Введите порт')
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_username')
                            ->label('Пользователь')
                            ->placeholder('Введите имя пользователя')
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_password')
                            ->label('Пароль')
                            ->placeholder('Введите пароль')
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_encryption')
                            ->label('Шифрование')
                            ->placeholder('Введите тип шифрования')
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_from_address')
                            ->label('Email заголовка')
                            ->placeholder('Введите email адрес заголовка')
                            ->maxLength(191),
                        TextInput::make('software_smtp_mail_from_name')
                            ->label('Имя заголовка')
                            ->placeholder('Введите имя заголовка')
                            ->maxLength(191)
                    ])->columns(4),
            ])
            ->statePath('data');
    }


    /**
     * @return void
     */
    public function submit(): void
    {
        try {
            if(env('APP_DEMO')) {
                Notification::make()
                    ->title('Внимание')
                    ->body('Вы не можете выполнить это изменение в демо-версии')
                    ->danger()
                    ->send();
                return;
            }

            $setting = SettingMail::first();
            if(!empty($setting)) {
                if(!empty($this->data['software_smtp_type'])) {
                    $envs = DotenvEditor::load(base_path('.env'));

                    $envs->setKeys([
                        'MAIL_MAILER' => $this->data['software_smtp_type'],
                        'MAIL_HOST' => $this->data['software_smtp_mail_host'],
                        'MAIL_PORT' => $this->data['software_smtp_mail_port'],
                        'MAIL_USERNAME' => $this->data['software_smtp_mail_username'],
                        'MAIL_PASSWORD' => $this->data['software_smtp_mail_password'],
                        'MAIL_ENCRYPTION' => $this->data['software_smtp_mail_encryption'],
                        'MAIL_FROM_ADDRESS' => $this->data['software_smtp_mail_from_address'],
                        'MAIL_FROM_NAME' => $this->data['software_smtp_mail_from_name'],
                    ]);

                    $envs->save();
                }

                if($setting->update($this->data)) {
                    Notification::make()
                        ->title('Ключи изменены')
                        ->body('Ваши ключи были успешно изменены!')
                        ->success()
                        ->send();
                }
            }else{
                if(SettingMail::create($this->data)) {
                    Notification::make()
                        ->title('Ключи созданы')
                        ->body('Ваши ключи были успешно созданы!')
                        ->success()
                        ->send();
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
