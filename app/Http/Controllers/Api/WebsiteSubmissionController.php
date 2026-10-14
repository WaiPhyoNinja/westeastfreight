<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Filament\Resources\WebsiteSubmissionResource;
use App\Models\User;
use App\Models\WebsiteSubmission;
use App\Support\ContactReceiptSender;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class WebsiteSubmissionController extends Controller
{
    public function store(Request $request, string $collection): JsonResponse
    {
        $rules = ['email' => 'required|email|max:255'];
        if ($collection === 'contact_us') {
            $rules += [
                'name' => 'required|string|max:255', 'phone' => 'nullable|string|max:50',
                'subject' => 'nullable|string|max:255', 'address' => 'nullable|string|max:2000',
                'transport_id' => 'nullable|string|max:100', 'message' => 'nullable|string|max:5000',
            ];
        }
        $details = $request->validate($rules);
        $submission = WebsiteSubmission::create(['type' => $collection, 'email' => $details['email'], 'details' => $details]);

        if ($collection === 'contact_us') {
            try {
                $recipients = User::query()
                    ->whereHas('permissions', fn ($query) => $query->where('name', 'submissions.manage'))
                    ->orWhereHas('roles.permissions', fn ($query) => $query->where('name', 'submissions.manage'))
                    ->get();

                if ($recipients->isNotEmpty()) {
                    Notification::make()
                        ->title('New Contact Us message from '.($details['name'] ?? $submission->email))
                        ->body(Str::limit($details['message'] ?? $details['address'] ?? $details['subject'] ?? '', 160))
                        ->info()
                        ->actions([
                            Action::make('view')->label('View and reply')
                                ->url(WebsiteSubmissionResource::getUrl('edit', ['record' => $submission->id], panel: 'admin'))
                                ->markAsRead(),
                        ])
                        ->sendToDatabase($recipients);
                }
            } catch (Throwable $exception) {
                Log::error('Contact admin notification could not be created.', ['submission_id' => $submission->id, 'exception' => $exception]);
            }

            app(ContactReceiptSender::class)->send($submission);
        }

        return response()->json([
            'message' => 'Your submission has been received.',
            'email_status' => $collection === 'contact_us' ? $submission->receipt_mail_status : null,
        ], 201);
    }
}
