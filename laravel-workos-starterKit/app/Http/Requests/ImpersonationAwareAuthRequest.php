<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Laravel\WorkOS\Http\Requests\AuthKitAuthenticationRequest;
use Illuminate\Support\Facades\Log;

class ImpersonationAwareAuthRequest extends AuthKitAuthenticationRequest
{
    public function authenticate()
    {
        // Call the parent authentication method
        $response = parent::authenticate();
        
        // Get the authentication response data
        $authData = $this->getAuthData();
        
        Log::info('ImpersonationAwareAuthRequest: Authentication response', [
            'auth_data' => $authData,
        ]);
        
        // Check if this is an impersonation session
        if (isset($authData['impersonator'])) {
            $impersonationData = [
                'email' => $authData['impersonator']['email'] ?? null,
                'reason' => $authData['impersonator']['reason'] ?? null,
            ];
            
            // Store impersonation data in session
            $this->session()->put('impersonation', $impersonationData);
            
            Log::info('Impersonation detected during authentication', [
                'impersonator_email' => $impersonationData['email'],
                'reason' => $impersonationData['reason'],
            ]);
        } else {
            // Clear any existing impersonation data
            $this->session()->forget('impersonation');
            Log::info('No impersonation detected during authentication');
        }
        
        return $response;
    }
    
    protected function getAuthData()
    {
        // This is a placeholder - we need to figure out how to get the auth data
        // from the Laravel WorkOS package
        return [];
    }
} 