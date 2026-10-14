<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')->label('Preview saved page')->icon('heroicon-o-eye')
                ->url(fn () => route('pages.preview', $this->record))->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('pages.view'))
                ->tooltip('Save your changes first. Preview does not publish the page.'),
            Actions\DeleteAction::make(),
        ];
    }
}
