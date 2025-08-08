<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ShareImpersonationData
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $impersonation = $request->session()->get('impersonation');
        
        Log::info('ShareImpersonationData: Sharing data', [
            'has_impersonation' => !is_null($impersonation),
            'impersonation_data' => $impersonation,
        ]);
        
        if ($impersonation) {
            Inertia::share('impersonation', $impersonation);
        } else {
            Inertia::share('impersonation', null);
        }

        return $next($request);
    }
} 