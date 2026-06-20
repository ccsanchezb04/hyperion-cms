<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->query('filter', 'all');

        $query = ContactMessage::query()->latest('cmsg_dtcrea');

        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->read();
        }

        $messages = $query
            ->paginate(20)
            ->withQueryString()
            ->through(fn (ContactMessage $m) => [
                'id' => $m->cmsg_idcmsg,
                'name' => $m->cmsg_nmname,
                'email' => $m->cmsg_dsemai,
                'subject' => $m->cmsg_cdsubj,
                'is_read' => $m->isRead(),
                'received_at' => $m->cmsg_dtcrea?->format('Y-m-d H:i'),
            ]);

        return Inertia::render('ContactMessages/Index', [
            'messages' => $messages,
            'filter' => $filter,
            'unreadCount' => ContactMessage::unread()->count(),
        ]);
    }

    public function show(ContactMessage $contactMessage): Response
    {
        $contactMessage->markAsRead();

        return Inertia::render('ContactMessages/Show', [
            'message' => [
                'id' => $contactMessage->cmsg_idcmsg,
                'name' => $contactMessage->cmsg_nmname,
                'email' => $contactMessage->cmsg_dsemai,
                'subject' => $contactMessage->cmsg_cdsubj,
                'body' => $contactMessage->cmsg_dsmess,
                'ip' => $contactMessage->cmsg_dsipad,
                'received_at' => $contactMessage->cmsg_dtcrea?->format('Y-m-d H:i:s'),
                'read_at' => $contactMessage->cmsg_dtread?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('status', 'Mensaje eliminado.');
    }
}
