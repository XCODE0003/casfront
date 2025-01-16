<?php

namespace App\Filament\Pages;

use App\Models\CustomLayout;
use App\Traits\Providers\WorldSlotTrait;
use Filament\Forms\Components\ColorPicker;
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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Forms\Components\Actions\Action;
use Creagia\FilamentCodeField\CodeField;
use Livewire\WithFileUploads;

class AdvancedPage extends Page implements HasForms
{
    use InteractsWithForms, WorldSlotTrait, WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.advanced-page';

    protected static ?string $navigationLabel = 'Расширенные настройки';

    protected static ?string $modelLabel = 'Расширенные настройки';

    protected static ?string $title = 'Расширенные настройки';

    protected static ?string $slug = 'advanced-options';

    public ?array $data = [];
    public $output;

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

    }

    /**
     * @param $type
     * @return void
     */
    public function loadProvider($type)
    {
        self::getProviderWorldslot($type);
        Notification::make()
            ->title('Успешно')
            ->body('Провайдеры успешно загружены')
            ->success()
            ->send();
    }

    /**
     * @return void
     */
    public function loadGames()
    {
        self::getGamesWorldslot();
        Notification::make()
            ->title('Успешно')
            ->body('Игры успешно загружены')
            ->success()
            ->send();
    }

    /**
     * @return void
     */
    public function upload()
    {

    }

    /**
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {

        return $data;
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Обновление')
                    ->description('Загрузите здесь файл обновления в формате zip')
                    ->schema([
                        FileUpload::make('update')
                    ])

            ])
            ->statePath('data');
    }

    /**
     * @return void
     */
    public function submit(): void
    {
        try {
            foreach ($this->data['update'] as $file) {
                $extension  = $file->extension();
                if($extension === "zip") {
                    $filePath = $file->storeAs('updates', $file->getClientOriginalName());

                    $zip = new \ZipArchive;
                    $zipPath = storage_path("app/{$filePath}"); // Полный путь к zip файлу
                    $extractPath = base_path(); // Измените на нужную директорию

                    if ($zip->open($zipPath) === true) {
                        $zip->extractTo($extractPath);
                        $zip->close();

                        // Удалить zip файл после извлечения
                        \Storage::delete($filePath);
                    }
                }

                Notification::make()
                    ->title('Успешно')
                    ->body('Обновление успешно выполнено')
                    ->success()
                    ->send();
            }
        } catch (Halt $exception) {
            Notification::make()
                ->title('Ошибка при изменении данных!')
                ->body('Ошибка при изменении данных!')
                ->danger()
                ->send();
        }
    }

    /**
     * @return void
     */
    public function runMigrate()
    {
        // Выполнить команду Artisan для запуска миграций
        Artisan::call('migrate');

        // Вы также можете добавить опцию '--seed' для запуска сидеров, если необходимо
        // Artisan::call('migrate --seed');

        // Получить вывод команды, если необходимо
        $this->output = Artisan::output();
        Notification::make()
            ->title('Успешно')
            ->body('Миграции успешно загружены')
            ->success()
            ->send();
    }

    /**
     * @return void
     */
    public function runMigrateWithSeed()
    {
        // Выполнить команду Artisan для запуска миграций
        Artisan::call('migrate --seed');

        // Вы также можете добавить опцию '--seed' для запуска сидеров, если необходимо
        // Artisan::call('migrate --seed');

        // Получить вывод команды, если необходимо
        $this->output = Artisan::output();
        Notification::make()
            ->title('Успешно')
            ->body('Миграции успешно загружены')
            ->success()
            ->send();
    }
}
