# WorkOS Impersonation Feature

This project now includes WorkOS impersonation functionality that allows administrators to impersonate users for debugging and support purposes.

## Features

### 1. Automatic Impersonation Detection
- The `DetectImpersonation` middleware automatically detects when a user is being impersonated
- Impersonation data is stored in the session and shared with all pages
- Logs are created for audit purposes when impersonation is detected

### 2. Visual Impersonation Banner
- A yellow banner appears at the top of the page when impersonation is active
- Shows the impersonator's email and reason (if provided)
- Includes a "Stop Impersonation" button to end the session
- Can be hidden temporarily with the X button

### 3. Admin Interface
- Admin dashboard at `/admin` shows all users
- User detail pages show WorkOS IDs for easy identification
- Instructions for using WorkOS Dashboard impersonation

## How It Works

### Backend Components

1. **DetectImpersonation Middleware** (`app/Http/Middleware/DetectImpersonation.php`)
   - Checks for impersonation data in the WorkOS access token
   - Stores impersonation information in the session
   - Logs impersonation events for auditing

2. **ShareImpersonationData Middleware** (`app/Http/Middleware/ShareImpersonationData.php`)
   - Shares impersonation data with all Inertia pages
   - Makes impersonation status available to frontend components

3. **ImpersonationController** (`app/Http/Controllers/ImpersonationController.php`)
   - Provides API endpoints for impersonation status
   - Handles stopping impersonation sessions

### Frontend Components

1. **ImpersonationBanner Component** (`resources/js/components/impersonation-banner.tsx`)
   - Displays impersonation status with visual indicators
   - Provides controls to stop impersonation or hide banner
   - Uses yellow styling to make impersonation obvious

2. **Admin Pages**
   - Admin dashboard lists all users with their WorkOS IDs
   - User detail pages provide impersonation instructions
   - Easy navigation to find users for impersonation

## Usage

### For Administrators

1. **Enable Impersonation in WorkOS Dashboard**
   - Go to your WorkOS Dashboard
   - Navigate to Authentication → User Impersonation
   - Click "Configure" to enable impersonation for your environment

2. **Impersonate a User**
   - Go to your WorkOS Dashboard → Users
   - Find the user you want to impersonate
   - Click "Impersonate User" in the Danger Zone
   - Provide a reason (required in production)
   - You'll be redirected to your application as that user

3. **Monitor Impersonation**
   - The yellow banner will appear at the top of all pages
   - You can see who you're impersonating and why
   - Use "Stop Impersonation" to end the session

### For Developers

1. **Testing Impersonation**
   - Visit `/admin` to see all users
   - Note the WorkOS IDs for easy identification
   - Use the WorkOS Dashboard to impersonate specific users

2. **Customizing the Banner**
   - Modify `resources/js/components/impersonation-banner.tsx`
   - Adjust styling, content, or behavior as needed

3. **Adding Impersonation Logic**
   - Check `page.props.impersonation` in your components
   - Add conditional logic based on impersonation status
   - Restrict sensitive features during impersonation

## Security Considerations

1. **Audit Logging**
   - All impersonation events are logged with user details
   - Check Laravel logs for impersonation activity

2. **Session Management**
   - Impersonation sessions expire after 60 minutes
   - Users can manually stop impersonation at any time

3. **Access Control**
   - Only WorkOS team members with Admin role can impersonate
   - Impersonation is disabled by default and must be enabled per environment

## Configuration

### Environment Variables
Ensure your `.env` file has the required WorkOS configuration:
```
WORKOS_CLIENT_ID=your_client_id
WORKOS_API_KEY=your_api_key
WORKOS_REDIRECT_URL=your_redirect_url
```

### Middleware Registration
The impersonation middleware is automatically registered in `bootstrap/app.php`:
```php
$middleware->web(append: [
    HandleAppearance::class,
    HandleInertiaRequests::class,
    ShareImpersonationData::class,
    AddLinkHeadersForPreloadedAssets::class,
]);
```

## Routes

- `/admin` - Admin dashboard with user list
- `/admin/users/{user}` - User detail page
- `/impersonation/status` - API endpoint for impersonation status
- `/impersonation/stop` - API endpoint to stop impersonation

## Files Added/Modified

### New Files
- `app/Http/Middleware/DetectImpersonation.php`
- `app/Http/Middleware/ShareImpersonationData.php`
- `app/Http/Controllers/ImpersonationController.php`
- `routes/impersonation.php`
- `routes/admin.php`
- `resources/js/components/impersonation-banner.tsx`
- `resources/js/pages/admin/index.tsx`
- `resources/js/pages/admin/users/show.tsx`

### Modified Files
- `routes/web.php` - Added impersonation routes and middleware
- `bootstrap/app.php` - Registered ShareImpersonationData middleware
- `resources/js/layouts/app/app-sidebar-layout.tsx` - Added impersonation banner
- `resources/js/components/app-sidebar.tsx` - Added admin navigation link

## Troubleshooting

1. **Banner not showing during impersonation**
   - Check that impersonation is enabled in WorkOS Dashboard
   - Verify the access token contains impersonation data
   - Check browser console for JavaScript errors

2. **Impersonation not working**
   - Ensure you have Admin role in WorkOS
   - Check that impersonation is enabled for your environment
   - Verify the user exists in your WorkOS organization

3. **Session issues**
   - Clear browser cookies and try again
   - Check Laravel logs for session-related errors
   - Verify WorkOS configuration in `.env` 