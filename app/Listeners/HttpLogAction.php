<?php

namespace App\Listeners;

use App\Models\HttpLog;
use App\Events\HttpLog as HttpLogEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HttpLogAction
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  HttpLogEvent  $event
     * @return void
     */
    public function handle(HttpLogEvent $event)
    {
        // Log::info('This is some useful information.');
        HttpLog::create([
            'log_name'         => $event->logName,
            'requester_ref_id' => $event->requesterRefID,
            'receiver_ref_id'  => $event->receiverRefID,
            'from_url'         => $event->from_url,
            'to_url'           => $event->to_url,
            'method'           => $event->method,
            'request'          => $event->request,
            'response'         => $event->response,
            'status_code'      => $event->statusCode,
            'direction'        => $event->direction,
            'response_time'    => $event->responseTime,
        ]);
    }
}
