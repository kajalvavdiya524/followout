<?php

namespace App\Http\Controllers\API;

use Carbon;
use App\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationsController extends Controller
{
    public function index()
    {
        $authUser = auth()->guard('api')->user();

        $notifications = $authUser->notifications;

        return response()->json([
            'status' => 'OK',
            'notifications' => $notifications,
        ]);
    }

    public function read($notification)
    {
        $authUser = auth()->guard('api')->user();

        $notification = Notification::find($notification);

        if (is_null($notification)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found.',
            ], 404);
        }

        if ($authUser->id === $notification->user->id) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'unread_count' => $authUser->unreadNotificationsCount(),
            ],
        ]);
    }

    public function readAll()
    {
        $authUser = auth()->guard('api')->user();

        $nowInMilliseconds = (int) (Carbon::now()->timestamp . str_pad(Carbon::now()->milli, 3, '0', STR_PAD_LEFT));

        $authUser->notifications()->unread()->update([
            // We need instance of \MongoDB\BSON\UTCDateTime to mass update the MongoDB date column
            'read_at' => new \MongoDB\BSON\UTCDateTime($nowInMilliseconds)
        ]);

        return response()->json([
            'status' => 'OK',
            'data' => [
                'unread_count' => $authUser->unreadNotificationsCount(),
            ],
        ]);
    }

    public function destroy($notification)
    {
        $authUser = auth()->guard('api')->user();

        $notification = Notification::find($notification);

        if (is_null($notification)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found.',
            ], 404);
        }

        if ($authUser->id === $notification->user->id) {
            $notification->delete();
        }

        return response()->json([ 'status' => 'OK' ]);
    }

    public function destroyAll()
    {
        $authUser = auth()->guard('api')->user();

        $authUser->notifications()->delete();

        return response()->json([ 'status' => 'OK' ]);
    }
}
