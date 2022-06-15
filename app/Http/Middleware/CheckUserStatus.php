<?php

namespace App\Http\Middleware;

use App\Http\Controllers\API\ResponseController;
use App\Models\EWUserDetail;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus extends ResponseController
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $authInfo = Auth::user();

        $userDetails = EWUserDetail::where('user_id', $authInfo->id)->first(['status']);
        if (!$userDetails) {
            $responseObj = $this->makeResponse('error', Response::HTTP_NOT_FOUND, 'No user details found.');
            return $this->sendResponse($responseObj, Response::HTTP_NOT_FOUND);
        }

        $messageArr = [];
        $status = $userDetails->status;

        if ($status == 'approved') {
            return $next($request);
        }

        if ($status == 'pending') {
            $message = config('pending_message_en');
            $messageArr = [
                'en' => $message,
                'la' => config('pending_message_la'),
            ];
        } else if ($status == 'hold') {
            $message = config('hold_message_en');
            $messageArr = [
                'en' => $message,
                'la' => config('hold_message_la'),
            ];
        } else if ($status == 'rejected') {
            $message = config('rejected_message_en');
            $messageArr = [
                'en' => $message,
                'la' => config('rejected_message_la'),
            ];
        }

        $responseObj = $this->makeResponse('error', Response::HTTP_NOT_FOUND, 'Something went wrong.', [
            'status' => $status,
            'message' => $message
        ], [
            'message_with_lang' => $messageArr
        ]);

        return $this->sendResponse($responseObj, Response::HTTP_NOT_FOUND);
    }
}
