<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DetectImpersonation
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user) {
            $impersonation = $request->session()->get('impersonation');
            
            Log::info('DetectImpersonation: Current impersonation status', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'has_impersonation' => !is_null($impersonation),
                'impersonation_data' => $impersonation,
            ]);
        }

        return $next($request);
    }
} 