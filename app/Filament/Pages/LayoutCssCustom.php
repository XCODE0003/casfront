<?php

namespace App\Filament\Pages;

use App\Models\CustomLayout;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Forms\Components\Actions\Action;
use Creagia\FilamentCodeField\CodeField;

class LayoutCssCustom extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.layout-css-custom';

    protected static ?string $navigationLabel = 'Настройка макета';

    protected static ?string $modelLabel = 'Настройка макета';

    protected static ?string $title = 'Настройка макета';

    protected static ?string $slug = 'custom-layout';

    public ?array $data = [];
    public CustomLayout $custom;

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
        $this->custom = CustomLayout::first();
        $this->form->fill($this->custom->toArray());
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
                Section::make()
                    ->label('Фон')
                    ->schema([
                        ColorPicker::make('background_base')
                            ->label('Основной фон')
                            ->required(),
                        ColorPicker::make('background_base_dark')
                            ->label('Основной фон (Темный)')
                            ->required(),
                        ColorPicker::make('carousel_banners')
                            ->label('Карусель баннеров')
                            ->required(),
                        ColorPicker::make('carousel_banners_dark')
                            ->label('Карусель баннеров (Темная)')
                            ->required(),
                    ])->columns(4)
                ,
                Section::make()
                    ->label('Боковая панель & Навигация & Подвал')
                    ->schema([
                        ColorPicker::make('sidebar_color')
                            ->label('Боковая панель')
                            ->required(),

                        ColorPicker::make('sidebar_color_dark')
                            ->label('Боковая панель (Темная)')
                            ->required(),

                        ColorPicker::make('navtop_color')
                            ->label('Верхняя навигация')
                            ->required(),

                        ColorPicker::make('navtop_color_dark')
                            ->label('Верхняя навигация (Темная)')
                            ->required(),

                        ColorPicker::make('side_menu')
                            ->label('Боковое меню')
                            ->required(),

                        ColorPicker::make('side_menu_dark')
                            ->label('Боковое меню (Темное)')
                            ->required(),

                        ColorPicker::make('footer_color')
                            ->label('Цвет подвала')
                            ->required(),

                        ColorPicker::make('footer_color_dark')
                            ->label('Цвет подвала (Темный)')
                            ->required(),
                    ])->columns(4)
                ,

                Section::make('Настройка')
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->label('Основной цвет')
                            ->required(),
                        ColorPicker::make('primary_opacity_color')
                            ->label('Основной цвет с прозрачностью')
                            ->required(),

                        ColorPicker::make('input_primary')
                            ->label('Основной цвет полей ввода')
                            ->required(),
                        ColorPicker::make('input_primary_dark')
                            ->label('Основной цвет полей ввода (Темный)')
                            ->required(),

                        ColorPicker::make('card_color')
                            ->label('Основной цвет карточки')
                            ->required(),
                        ColorPicker::make('card_color_dark')
                            ->label('Основной цвет карточки (Темный)')
                            ->required(),

                        ColorPicker::make('secundary_color')
                            ->label('Вторичный цвет')
                            ->required(),
                        ColorPicker::make('gray_dark_color')
                            ->label('Темно-серый цвет')
                            ->required(),
                        ColorPicker::make('gray_light_color')
                            ->label('Светло-серый цвет')
                            ->required(),
                        ColorPicker::make('gray_medium_color')
                            ->label('Средне-серый цвет')
                            ->required(),
                        ColorPicker::make('gray_over_color')
                            ->label('Серый цвет наведения')
                            ->required(),
                        ColorPicker::make('title_color')
                            ->label('Цвет заголовка')
                            ->required(),
                        ColorPicker::make('text_color')
                            ->label('Цвет текста')
                            ->required(),
                        ColorPicker::make('sub_text_color')
                            ->label('Цвет подтекста')
                            ->required(),
                        ColorPicker::make('placeholder_color')
                            ->label('Цвет подсказки')
                            ->required(),
                        ColorPicker::make('background_color')
                            ->label('Цвет фона')
                            ->required(),
                        TextInput::make('border_radius')
                            ->label('Радиус границ')
                            ->required(),
                    ])->columns(4),
               Section::make()
                ->schema([
                    CodeField::make('custom_css')
                        ->setLanguage(CodeField::CSS)
                        ->withLineNumbers()
                        ->minHeight(400),
                    CodeField::make('custom_js')
                        ->setLanguage(CodeField::JS)
                        ->withLineNumbers()
                        ->minHeight(400),
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
            if(env('APP_DEMO')) {
                Notification::make()
                    ->title('Внимание')
                    ->body('Вы не можете внести это изменение в демо-версии')
                    ->danger()
                    ->send();
                return;
            }

            $custom = CustomLayout::first();

            if(!empty($custom)) {
                if($custom->update($this->data)) {

                    Cache::put('custom', $custom);

                    Notification::make()
                        ->title('Данные изменены')
                        ->body('Данные успешно изменены!')
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
