<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ImpersonationController extends Controller
{
    /**
     * Get the current impersonation status.
     */
    public function status(Request $request): JsonResponse
    {
        $impersonation = $request->session()->get('impersonation');
        
        return response()->json([
            'is_impersonating' => !is_null($impersonation),
            'impersonator' => $impersonation,
        ]);
    }

    /**
     * Stop impersonation and redirect to logout.
     */
    public function stop(Request $request): RedirectResponse
    {
        // Clear impersonation data
        $request->session()->forget('impersonation');
        
        // Redirect to logout
        return redirect()->route('logout');
    }

    /**
     * Show impersonation banner component data.
     */
    public function banner(Request $request): Response
    {
        $impersonation = $request->session()->get('impersonation');
        
        return Inertia::render('components/ImpersonationBanner', [
            'impersonation' => $impersonation,
        ]);
    }
} 