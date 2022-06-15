<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HttpLog
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $logName;
    public $requesterRefID = '';
    public $receiverRefID  = '';
    public $from_url       = '';
    public $to_url         = '';
    public $method         = null;
    public $request        = null;
    public $response       = null;
    public $statusCode     = '';
    public $direction      = null;
    public $responseTime   = 0;

    public function __construct($params = [])
    {
        //get the request object
        $request= $params['request'] ?? '';

        //calculate response time
        $responseTime = microtime(true) - LARAVEL_START;

        $this->logName        = $params['log_name'] ?? '';
        $this->requesterRefID = $params['requester_ref_id'] ?? '';
        $this->receiverRefID  = $params['receiver_ref_id'] ?? '';
        if ($request instanceof Request) {
            $this->from_url = parse_url($request->headers->get('origin'), PHP_URL_HOST);
            $this->to_url   = $request->fullUrl();
            $this->method   = $request->method();
            $this->request  = json_encode($request->all(), JSON_UNESCAPED_UNICODE);
        } else {
            $this->request = json_encode($request, JSON_UNESCAPED_UNICODE);
        }
        $this->response     = json_encode($params['response'] ?? '', JSON_UNESCAPED_UNICODE);
        $this->statusCode   =  $params['status_code'] ?? '';
        $this->direction    =  $params['direction'] ?? '';
        $this->responseTime =  $params['response_time'] ?? $responseTime;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
