<?php

namespace App\Http\Middleware;

use App\Services\EncryptionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DecryptPayload
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('payload')) {
            Log::info('Request Path (Not encrypting): ' . $request->path());
            return $next($request);
        }
        Log::info('Request Path (get encrypted data): ' . $request->path() .' payload: '. $request->get('payload'));

        //decryption
        $encryptionService = new EncryptionService();
        $decrypted_data = $encryptionService->getDecryptedData($request->payload);
        //merging with
        $request->merge($decrypted_data);
        $request->request->remove('payload');
        return $next($request);
    }
}
