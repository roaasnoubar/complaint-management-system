<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Notifications", description="User notification management")
 */
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Get all notifications for the authenticated user",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="List of notifications")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);

        $notifications = Notification::forUser(auth()->id())
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $notifications,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/unread-count",
     *     summary="Get unread notification count",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Unread count")
     * )
     */
    public function unreadCount(): JsonResponse
    {
        $count = Notification::forUser(auth()->id())
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'data'    => ['unread_count' => $count],
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/notifications/{id}/read",
     *     summary="Mark a notification as read",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Notification marked as read"),
     *     @OA\Response(response=404, description="Notification not found")
     * )
     */
    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::forUser(auth()->id())
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data'    => $notification,
        ]);
    }

    /**
     * Mark ALL notifications as read for the authenticated user.
     *
     * @OA\Put(
     *     path="/api/notifications/read-all",
     *     summary="Mark all notifications as read",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="All notifications marked as read")
     * )
     */
    public function markAllAsRead(): JsonResponse
    {
        Notification::forUser(auth()->id())
            ->unread()
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/notifications/{id}",
     *     summary="Delete a notification",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Notification deleted"),
     *     @OA\Response(response=404, description="Notification not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $notification = Notification::forUser(auth()->id())
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }
}
