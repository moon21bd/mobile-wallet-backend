<?php

namespace App\Repositories;

use App\Models\Notification;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Log;

class NotificationHandler
{
    /**
     * @param $title
     * @param $description
     * @param $link
     * @param $user_id
     */
    public function sendNotification($title, $description, $link, $userID)
    {
        $data = [
            'title' => $title,
            'description' => $description,
            'link' => $link,
            'user_id' => $userID,
            'is_seen' => 0,
            'is_read' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        Log::info('Notification::Send Data: '.json_encode($data));
        $status = Notification::create($data);
        $data['id'] = $status->id;
        if($status) {
            $pusher = new PlatformPusher();
            $pusher->sendPrivateData('channel-user-'.$userID, 'user_event', $data);
        }
    }
}