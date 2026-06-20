<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreContactMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $message = ContactMessage::create([
            'cmsg_nmname' => $request->input('nombre'),
            'cmsg_dsemai' => $request->input('email'),
            'cmsg_cdsubj' => $request->input('asunto'),
            'cmsg_dsmess' => $request->input('mensaje'),
            'cmsg_dsipad' => $request->ip(),
            'cmsg_dsuage' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $this->notifyByEmail($message);

        return back()->with('contact_status', 'sent');
    }

    protected function notifyByEmail(ContactMessage $message): void
    {
        $recipient = Setting::getValue('site.contact.notify_email')
            ?: Setting::getValue('mail_from_address')
            ?: config('mail.from.address');

        if (! $recipient) {
            return;
        }

        try {
            Mail::to($recipient)->send(new ContactMessageReceived($message));
        } catch (\Throwable $e) {
            // Persisting the message succeeded; never block the visitor over a mail failure.
            Log::warning('ContactMessage email send failed', [
                'message_id' => $message->cmsg_idcmsg,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
