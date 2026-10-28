<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filter' => ['sometimes', 'in:all,unread'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $filter = $validated['filter'] ?? 'all';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = $request->user()
            ->notifications()
            ->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $paginator = $query->paginate($perPage);

        $notifications = collect($paginator->items())
            ->map(function ($notification) {
                $data = $notification->data;

                $severity = $data['severity'] ?? 'info';

                if (! in_array($severity, ['success', 'info', 'warning', 'error'], true)) {
                    $severity = 'info';
                }

                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Notifikasi',
                    'message' => $data['message'] ?? '',
                    'href' => $data['href'] ?? null,
                    'category' => $data['category'] ?? 'system',
                    'severity' => $severity,
                    'action' => $data['action'] ?? null,
                    'meta' => $data['meta'] ?? [],
                    'time' => $notification->created_at?->diffForHumans(),
                    'created_at' => $notification->created_at?->toISOString(),
                    'read' => $notification->read_at !== null,
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'has_more' => $paginator->hasMorePages(),
            'next_page' => $paginator->hasMorePages()
                ? $paginator->currentPage() + 1
                : null,
            'filter' => $filter,
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
}
