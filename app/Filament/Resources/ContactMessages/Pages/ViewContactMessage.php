<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->getRecord()->markAsRead();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label(__('Reply by email'))
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->url(fn (ContactMessage $record) => 'mailto:'.$record->email.'?subject='.rawurlencode('Re: '.($record->subject ?? ''))),
            Action::make('markUnread')
                ->label(__('Mark as unread'))
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->action(function (ContactMessage $record) {
                    $record->update(['read_at' => null]);

                    return redirect(ContactMessageResource::getUrl());
                }),
            DeleteAction::make(),
        ];
    }
}
