<?php

namespace App\Http\Controllers;

use Carbon;
use App\Notification;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(100);
        $unreadCount = auth()->user()->notifications()->unread()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function read(Notification $notification)
    {
        if (auth()->user()->id === $notification->user->id) {
            $notification->markAsRead();
        }

        session()->flash('toastr.success', 'Notification marked as read.');

        return redirect()->route('notifications.index');
    }

    public function readAll()
    {
        $nowInMilliseconds = (int) (Carbon::now()->timestamp . str_pad(Carbon::now()->milli, 3, '0', STR_PAD_LEFT));

        auth()->user()->notifications()->unread()->update([
            // We need instance of \MongoDB\BSON\UTCDateTime to mass update the MongoDB date column
            'read_at' => new \MongoDB\BSON\UTCDateTime($nowInMilliseconds)
        ]);

        session()->flash('toastr.success', 'All notifications marked as read.');

        return redirect()->route('notifications.index');
    }

    public function destroy(Notification $notification)
    {
        if (auth()->user()->id === $notification->user->id) {
            $notification->delete();
        }

        session()->flash('toastr.success', 'Notification has been removed.');

        return redirect()->route('notifications.index');
    }

    public function destroyAll()
    {
        auth()->user()->notifications()->delete();

        session()->flash('toastr.success', 'Notifications have been removed.');

        return redirect()->route('notifications.index');
    }
}
