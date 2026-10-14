<?php

namespace App\Filament\Resources;

use App\Filament\Blocks\BlockRegistry;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Pages';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('pages.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('pages.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('pages.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('pages.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('slug')
                ->helperText('Use "home" for the front page. Other pages appear at /en/slug, /my/slug, and /th/slug.')
                ->required()->unique(ignoreRecord: true)->maxLength(100),
            static::pageTypeField(),
            Select::make('status')
                ->options(['draft' => 'Draft', 'published' => 'Published'])
                ->default('draft')->required(),
            Tabs::make('Page content')->tabs([
                static::localeTab('English', 'en'),
                static::localeTab('မြန်မာ', 'my'),
                static::localeTab('ไทย', 'th'),
            ])->columnSpanFull(),
            Repeater::make('blocks')
                ->label('Page blocks')
                ->helperText('Drag blocks into the order that they should appear on the page.')
                ->extraAttributes(['class' => 'page-block-editor'])
                ->schema([
                    Select::make('type')->label('Block name')->options(BlockRegistry::options())->required()->live(),
                    Group::make()->schema(fn ($get): array => BlockRegistry::schemaFor($get('type')))->columnSpanFull(),
                ])
                ->itemLabel(fn (array $state): string => BlockRegistry::labelFor($state['type'] ?? null))
                ->itemNumbers()->collapsible()->cloneable()->reorderable()->defaultItems(0)->columnSpanFull(),
        ]);
    }

    /** @return array<int, Step> */
    public static function creationSteps(): array
    {
        return [
            Step::make('Page details')->schema([
                TextInput::make('translations.en.title')->label('Page title')
                    ->required()->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                        if (blank($get('data.slug', true))) {
                            $set('data.slug', Str::slug($state ?? ''), true);
                        }

                        foreach ($get('data.blocks', true) ?? [] as $key => $block) {
                            $headingField = match ($block['type'] ?? null) {
                                'vue_richtext' => 'headline',
                                'content' => 'heading',
                                'vue_header', 'vue_service_details' => 'title',
                                default => null,
                            };
                            if ($headingField && blank(data_get($block, "translations.en.{$headingField}"))) {
                                $set("data.blocks.{$key}.translations.en.{$headingField}", $state, true);
                            }
                        }
                    }),
                TextInput::make('slug')->label('Page URL')->prefix('/')->required()
                    ->alphaDash()->unique(ignoreRecord: true)->maxLength(100),
                static::pageTypeField(),
                Select::make('status')->label('Visibility')
                    ->options(['draft' => 'Draft', 'published' => 'Published'])
                    ->default('draft')->required(),
                Tabs::make('Other languages')->tabs([
                    Tabs\Tab::make('မြန်မာ')->schema([TextInput::make('translations.my.title')->label('Page title')]),
                    Tabs\Tab::make('ไทย')->schema([TextInput::make('translations.th.title')->label('Page title')]),
                ])->columnSpanFull(),
            ])->columns(2),
            Step::make('Page sections')->schema([
                Repeater::make('blocks')->label('Sections')
                    ->extraAttributes(['class' => 'page-block-editor'])
                    ->schema([
                        Select::make('type')->label('Block name')
                            ->options(static::creationBlockOptions())->required()->live(),
                        Group::make()->schema(fn ($get): array => BlockRegistry::schemaFor($get('type')))->columnSpanFull(),
                    ])
                    ->default(static::defaultCreationBlocks())
                    ->minItems(1)->collapsible()->reorderable()->itemNumbers()
                    ->itemLabel(fn (array $state): string => BlockRegistry::labelFor($state['type'] ?? null))
                    ->addActionLabel('Add section')->columnSpanFull(),
            ]),
        ];
    }

    protected static function defaultCreationBlock(): string
    {
        return 'vue_richtext';
    }

    protected static function defaultCreationBlocks(): array
    {
        return [['type' => static::defaultCreationBlock()]];
    }

    protected static function creationBlockOptions(): array
    {
        return BlockRegistry::createOptions();
    }

    protected static function localeTab(string $label, string $locale): Tabs\Tab
    {
        return Tabs\Tab::make($label)->schema([
            TextInput::make("translations.{$locale}.title")->label('Page title')->required($locale === 'en'),
            TextInput::make("translations.{$locale}.meta_title")->label('SEO title'),
            Textarea::make("translations.{$locale}.meta_description")->label('SEO description')->rows(2),
        ]);
    }

    protected static function pageTypeField(): Select
    {
        return Select::make('page_type')
            ->default('page')
            ->hidden()
            ->required();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('page_type', 'page');
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('slug')->searchable()->sortable(),
            TextColumn::make('translations.en.title')->label('English title')->searchable(),
            BadgeColumn::make('page_type')->label('Type')->colors(['primary' => 'page', 'success' => 'service']),
            BadgeColumn::make('status')->colors(['warning' => 'draft', 'success' => 'published']),
            TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->actions([
            EditAction::make(),
            \Filament\Tables\Actions\Action::make('preview')->label('Preview')->icon('heroicon-o-eye')
                ->url(fn (Page $record) => route('pages.preview', $record))->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('pages.view')),
        ])->bulkActions([
            DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
