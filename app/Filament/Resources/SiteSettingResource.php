<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
use App\Support\MailDelivery;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Site Settings';

    protected static ?string $navigationLabel = 'General';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('settings.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny() && ! SiteSetting::query()->exists();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getNavigationUrl(): string
    {
        $recordId = SiteSetting::query()->value('id');

        return $recordId
            ? static::getUrl('edit', ['record' => $recordId])
            : static::getUrl('index');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::generalSchema());
    }

    public static function generalSchema(): array
    {
        return [
            Section::make('General')->schema([
                FileUpload::make('header_logo')
                    ->label('Header logo')->image()->disk('public')->directory('site/logo')
                    ->visibility('public')->imageEditor()->maxSize(4096)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('Upload a PNG, JPG, or WEBP logo up to 4 MB.'),
                TextInput::make('brand_name')->required()->maxLength(255),
                TextInput::make('brand_mark')->required()->maxLength(10),
            ]),
            Section::make('Contact and footer details')->schema([
                Textarea::make('footer_description_en')->label('Description (English)')->rows(2),
                Textarea::make('footer_description_my')->label('Description (Myanmar)')->rows(2),
                Textarea::make('footer_description_th')->label('Description (Thai)')->rows(2),
                TextInput::make('email')->email(),
                TextInput::make('phone_en')->label('Phone (English)')->tel(),
                TextInput::make('phone_my')->label('Phone (Myanmar)')->tel(),
                TextInput::make('phone_th')->label('Phone (Thai)')->tel(),
                Textarea::make('address_en')->label('Address (English)')->rows(2),
                Textarea::make('address_my')->label('Address (Myanmar)')->rows(2),
                Textarea::make('address_th')->label('Address (Thai)')->rows(2),
                Textarea::make('open_hours_en')->label('Opening hours (English)')->rows(2),
                Textarea::make('open_hours_my')->label('Opening hours (Myanmar)')->rows(2),
                Textarea::make('open_hours_th')->label('Opening hours (Thai)')->rows(2),
            ]),
            Section::make('Email delivery')->schema([
                Placeholder::make('mail_delivery_status')->label('Current mail transport')
                    ->content(fn (): string => MailDelivery::statusMessage()),
            ]),
            Section::make('Social links')->schema([
                Repeater::make('social_icons')
                    ->label('Social icons')
                    ->schema([
                        TextInput::make('name')->label('Platform name')->required()->maxLength(100),
                        TextInput::make('link')->label('Profile URL')->url()->required()->maxLength(2048),
                        FileUpload::make('icon')->label('Icon image')->image()->required()
                            ->disk('public')->directory('site/social-icons')->visibility('public')->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                        Toggle::make('is_active')->label('Visible')->default(true),
                    ])
                    ->columns(2)->columnSpanFull()->collapsible()->cloneable()->reorderable()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->addActionLabel('Add social link')->defaultItems(0),
            ]),
        ];
    }

    public static function headerNavigationSchema(): array
    {
        return [
            Section::make('Header navigation')->schema([
                static::menuRepeater('header_menu')->label('Header links'),
            ]),
        ];
    }

    protected static function customerNavigationSection(): Section
    {
        return Section::make('Customer header navigation')
            ->description('Menus shown after customer login. Drag groups to change their order.')
            ->schema([
                Repeater::make('customer_navigation_groups')
                    ->label('Navigation groups')
                    ->schema([
                        TextInput::make('label_en')->label('Group label (English)')->required(),
                        TextInput::make('label_my')->label('Group label (Myanmar)'),
                        TextInput::make('label_th')->label('Group label (Thai)'),
                        Textarea::make('description_en')->label('Dropdown description (English)')->rows(2)->maxLength(500),
                        Textarea::make('description_my')->label('Dropdown description (Myanmar)')->rows(2)->maxLength(500),
                        Textarea::make('description_th')->label('Dropdown description (Thai)')->rows(2)->maxLength(500),
                        FileUpload::make('feature_image')->label('Dropdown image')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(4096)->disk('public')->directory('site/navigation')->visibility('public')
                            ->imageEditor()->helperText('PNG, JPG or WEBP, up to 4 MB. Leave empty to use the default map image.'),
                        Toggle::make('is_active')->label('Visible')->default(true),
                        Repeater::make('items')
                            ->label('Menu items')
                            ->schema(static::menuItemSchema())
                            ->columns(2)->columnSpanFull()->collapsible()->cloneable()->reorderable()
                            ->itemLabel(fn (array $state): ?string => $state['label_en'] ?? null)
                            ->addActionLabel('Add menu item')
                            ->defaultItems(0),
                    ])
                    ->columns(2)->columnSpanFull()->collapsible()->cloneable()->reorderable()
                    ->itemLabel(fn (array $state): ?string => $state['label_en'] ?? null)
                    ->addActionLabel('Add navigation group')
                    ->default(SiteSetting::defaultCustomerNavigationGroups()),
                static::menuRepeater('customer_book_track_menu')->label('Legacy Book & Track')
                    ->default(SiteSetting::defaultCustomerMenu('customer_book_track'))
                    ->visible(fn (): bool => false),
                static::menuRepeater('customer_tools_menu')->label('Legacy Tools')
                    ->default(SiteSetting::defaultCustomerMenu('customer_tools'))
                    ->visible(fn (): bool => false),
            ]);
    }

    public static function footerNavigationSchema(): array
    {
        return [
            Section::make('Footer navigation')->schema([
                TextInput::make('footer_quick_links_title_en')->label('Quick Links heading (English)')->required(),
                TextInput::make('footer_quick_links_title_my')->label('Quick Links heading (Myanmar)'),
                TextInput::make('footer_quick_links_title_th')->label('Quick Links heading (Thai)'),
                static::menuRepeater('footer_menu')->label('Quick Links'),
                TextInput::make('footer_services_title_en')->label('Solutions heading (English)')->required(),
                TextInput::make('footer_services_title_my')->label('Solutions heading (Myanmar)'),
                TextInput::make('footer_services_title_th')->label('Solutions heading (Thai)'),
                static::menuRepeater('footer_services_menu')->label('Solutions links'),
                static::menuRepeater('footer_bottom_menu')->label('Bottom links'),
            ])->columns(3),
        ];
    }

    protected static function menuRepeater(string $name): Repeater
    {
        return Repeater::make($name)
            ->schema(static::menuItemSchema($name === 'header_menu'))
            ->columns(2)
            ->columnSpanFull()
            ->collapsible()
            ->cloneable()
            ->reorderable()
            ->itemLabel(fn (array $state): ?string => $state['label_en'] ?? null)
            ->addActionLabel('Add menu item')
            ->defaultItems(0);
    }

    /** @return array<int, Component> */
    protected static function menuItemSchema(bool $withSubmenu = false): array
    {
        $schema = [
            TextInput::make('label_en')->label('Label (English)')->required(),
            TextInput::make('label_my')->label('Label (Myanmar)'),
            TextInput::make('label_th')->label('Label (Thai)'),
            Select::make('type')
                ->options($withSubmenu ? ['URL' => 'Link', 'Pages' => 'Page', 'Group' => 'Dropdown group'] : ['URL' => 'Link', 'Pages' => 'Page'])
                ->default('URL')->required()->live(),
            TextInput::make('url')->label('Frontend URL')->placeholder('/about')->required(),
            Toggle::make('is_active')->label('Visible')->default(true),
            Toggle::make('open_in_new_tab')->label('Open in new tab')->default(false),
        ];

        if ($withSubmenu) {
            $schema[] = Repeater::make('sub_menu')
                ->label('Dropdown items')
                ->schema(static::menuItemSchema())
                ->columns(2)->collapsible()->reorderable()->cloneable()
                ->itemLabel(fn (array $state): ?string => $state['label_en'] ?? null)
                ->visible(fn ($get): bool => $get('type') === 'Group')
                ->columnSpanFull();
        }

        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('brand_name')->label('Site name'),
            TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->actions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteSettings::route('/'),
            'create' => Pages\CreateSiteSetting::route('/create'),
            'edit' => Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
