<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PushNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendPushNotification()
    {
        $user = User::find(2);

        Notification::send($user, new PushNotification());

        return response()->json(['message' => 'Push notification sent to all users.']);
    }
}
