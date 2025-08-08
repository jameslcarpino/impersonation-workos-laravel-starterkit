<?php

use Illuminate\Support\Facades\Route;
use Laravel\WorkOS\Http\Requests\AuthKitAuthenticationRequest;
use Laravel\WorkOS\Http\Requests\AuthKitLoginRequest;
use Laravel\WorkOS\Http\Requests\AuthKitLogoutRequest;
use Illuminate\Support\Facades\Log;
use Laravel\WorkOS\WorkOS;
use WorkOS\UserManagement;
use App\Models\User as AppUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Laravel\WorkOS\User;

Route::get('login', function (AuthKitLoginRequest $request) {
    return $request->redirect();
})->middleware(['guest'])->name('login');

Route::get('authenticate', function (AuthKitAuthenticationRequest $request) {
    try {
        // Debug: Log session data before authentication
        Log::info('Auth: Before authentication', [
            'session_keys' => array_keys($request->session()->all()),
            'workos_session_keys' => array_filter(array_keys($request->session()->all()), fn($key) => str_starts_with($key, 'workos')),
            'query_params' => $request->query(),
            'request_method' => $request->method(),
            'request_url' => $request->url(),
            'has_code' => $request->has('code'),
            'code_value' => $request->get('code'),
            'has_state' => $request->has('state'),
            'state_value' => $request->get('state'),
            'session_state' => $request->session()->get('state'),
        ]);
        
        // Check if we have the required parameters
        if (!$request->has('code')) {
            Log::error('Auth: Missing code parameter');
            return response()->json([
                'error' => 'Authentication failed',
                'message' => 'Missing authorization code',
            ], 403);
        }
        
        // Custom authentication for impersonation
        Log::info('Auth: About to call custom authenticate()');
        $user = authenticateWithWorkOS($request);
        Log::info('Auth: authenticate() completed successfully');
        
        // Debug: Log session data after authentication
        Log::info('Auth: After authentication', [
            'session_keys' => array_keys($request->session()->all()),
            'workos_session_keys' => array_filter(array_keys($request->session()->all()), fn($key) => str_starts_with($key, 'workos')),
            'session_data' => $request->session()->all(),
        ]);
        
        // Check for impersonation data in the session after authentication
        $accessToken = $request->session()->get('workos_access_token');
        if ($accessToken) {
            $jwtPayload = decodeJwt($accessToken);
            Log::info('Auth: JWT payload after authentication', [
                'jwt_payload' => $jwtPayload,
            ]);
            
            Log::info('Auth: Checking for impersonation', [
                'has_jwt_payload' => !is_null($jwtPayload),
                'has_act' => isset($jwtPayload['act']),
                'act_data' => $jwtPayload['act'] ?? 'NOT_FOUND',
            ]);
            
            if ($jwtPayload && isset($jwtPayload['act'])) {
                // The impersonator email is in act.sub
                $impersonatorEmail = $jwtPayload['act']['sub'] ?? null;
                
                Log::info('Auth: Processing impersonation data', [
                    'act_data' => $jwtPayload['act'],
                    'act_sub' => $jwtPayload['act']['sub'] ?? 'NOT_FOUND',
                    'extracted_email' => $impersonatorEmail,
                ]);
                
                $impersonationData = [
                    'email' => $impersonatorEmail,
                    'reason' => $jwtPayload['act']['reason'] ?? null,
                ];
                
                Log::info('Auth: Setting impersonation data in session', [
                    'impersonation_data' => $impersonationData,
                ]);
                
                $request->session()->put('impersonation', $impersonationData);
                
                Log::info('Auth: Impersonation detected during authentication', [
                    'impersonator_email' => $impersonationData['email'],
                    'reason' => $impersonationData['reason'],
                    'act_data' => $jwtPayload['act'],
                ]);
            } else {
                // Check if this is a cross-user impersonation
                $currentUser = Auth::user();
                $authenticatedEmail = $currentUser ? $currentUser->email : null;
                
                Log::info('Auth: Checking for cross-user impersonation', [
                    'authenticated_user_email' => $authenticatedEmail,
                    'jwt_payload_keys' => $jwtPayload ? array_keys($jwtPayload) : 'NULL',
                    'has_act' => isset($jwtPayload['act']),
                ]);
                
                // For now, we'll skip cross-user impersonation until we can implement it properly
                $request->session()->forget('impersonation');
                Log::info('Auth: No impersonation detected during authentication', [
                    'jwt_payload_keys' => $jwtPayload ? array_keys($jwtPayload) : 'NULL',
                    'has_act' => isset($jwtPayload['act']),
                ]);
            }
        }
        
        return to_route('dashboard');
    } catch (\Exception $e) {
        Log::error('Auth: Authentication failed', [
            'error' => $e->getMessage(),
            'exception_class' => get_class($e),
            'previous_exception' => $e->getPrevious() ? [
                'class' => get_class($e->getPrevious()),
                'message' => $e->getPrevious()->getMessage(),
            ] : null,
            'trace' => $e->getTraceAsString(),
            'query_params' => $request->query(),
        ]);
        
        // Return a proper error response instead of letting it bubble up
        return response()->json([
            'error' => 'Authentication failed',
            'message' => $e->getMessage() ?: 'Unknown authentication error',
            'exception_class' => get_class($e),
        ], 403);
    }
})->middleware(['guest']);

Route::post('logout', function (AuthKitLogoutRequest $request) {
    return $request->logout();
})->middleware(['auth'])->name('logout');

/**
 * Custom authentication function that bypasses state validation for impersonation
 */
function authenticateWithWorkOS($request)
{
    WorkOS::configure();

    // Skip state validation for impersonation
    // The state is only set during normal login, not during impersonation
    Log::info('Auth: Skipping state validation for impersonation');

    $user = (new UserManagement)->authenticateWithCode(
        config('services.workos.client_id'),
        $request->query('code'),
    );

    [$user, $accessToken, $refreshToken] = [
        $user->user,
        $user->access_token,
        $user->refresh_token,
    ];

    $user = new User(
        id: $user->id,
        firstName: $user->firstName,
        lastName: $user->lastName,
        email: $user->email,
        avatar: $user->profilePictureUrl,
    );

    $existingUser = findUsing($user->id);

    if (! $existingUser) {
        $existingUser = createUsing($user);
        event(new Registered($existingUser));
    } else {
        $existingUser = updateUsing($existingUser, $user);
    }

    Auth::guard('web')->login($existingUser);

    $request->session()->put('workos_access_token', $accessToken);
    $request->session()->put('workos_refresh_token', $refreshToken);

    $request->session()->regenerate();

    return $existingUser;
}

/**
 * Find the user with the given WorkOS ID.
 */
function findUsing(string $id): ?AppUser
{
    return AppUser::where('workos_id', $id)->first();
}

/**
 * Create a user from the given WorkOS user.
 */
function createUsing(User $user): AppUser
{
    return AppUser::create([
        'name' => $user->firstName.' '.$user->lastName,
        'email' => $user->email,
        'email_verified_at' => now(),
        'workos_id' => $user->id,
        'avatar' => $user->avatar ?? '',
    ]);
}

/**
 * Update a user from the given WorkOS user.
 */
function updateUsing(AppUser $user, User $userFromWorkOS): AppUser
{
    return tap($user)->update([
        'avatar' => $userFromWorkOS->avatar ?? '',
    ]);
}

/**
 * Decode JWT token (basic implementation)
 */
function decodeJwt($token)
{
    try {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            Log::error('Auth: JWT decode error - invalid token format', [
                'parts_count' => count($parts),
            ]);
            return null;
        }
        
        $payload = $parts[1];
        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $payload = base64_decode($payload);
        
        $decoded = json_decode($payload, true);
        
        Log::info('Auth: JWT decode result', [
            'has_decoded' => !is_null($decoded),
            'decoded_keys' => $decoded ? array_keys($decoded) : 'NULL',
            'has_act' => $decoded && isset($decoded['act']),
        ]);
        
        return $decoded;
    } catch (\Exception $e) {
        Log::error('Auth: JWT decode error', [
            'error' => $e->getMessage(),
        ]);
        return null;
    }
}
