<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $unreadOnly = $request->query('unread', false);

        $query = $user->notifications();

        if ($unreadOnly) {
            $query->unread();
        }

        $notifications = $query->latest()->paginate(20);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $user->unreadNotificationsCount(),
            ],
        ]);
    }

    /**
     * Get a specific notification.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);

        return response()->json([
            'data' => $notification,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'data' => $notification,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->markAllNotificationsAsRead();

        return response()->json([
            'message' => 'All notifications marked as read',
            'unread_count' => $user->unreadNotificationsCount(),
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Get unread count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'unread_count' => $user->unreadNotificationsCount(),
            ],
        ]);
    }
}
