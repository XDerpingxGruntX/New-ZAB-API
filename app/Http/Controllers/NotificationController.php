<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get user notifications with pagination.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        // Get total unread count
        $unreadCount = $user->unreadNotifications->count();

        // Get total notification count
        $totalCount = $user->notifications->count();

        // Get paginated notifications
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = [
            'data' => $notifications,
            'unread_count' => $unreadCount,
            'has_more' => $totalCount > ($page * $limit),
            'current_page' => $page,
            'total' => $totalCount,
        ];

        // Always return JSON for notifications endpoint
        return response()->json($data);
    }

    /**
     * Delete a specific notification.
     */
    public function destroy(Request $request, string $id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            if ($request->header('X-Inertia')) {
                return back()->withErrors(['error' => 'Notification not found']);
            }

            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->delete();

        if ($request->header('X-Inertia')) {
            return back();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            if ($request->header('X-Inertia')) {
                return back()->withErrors(['error' => 'Notification not found']);
            }

            return response()->json(['error' => 'Notification not found'], 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        if ($request->header('X-Inertia')) {
            return back();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        if ($request->header('X-Inertia')) {
            return back();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete all notifications for the user.
     */
    public function deleteAll(Request $request)
    {
        $user = auth()->user();
        $user->notifications()->delete();

        if ($request->header('X-Inertia')) {
            return back();
        }

        return response()->json(['success' => true]);
    }
}
