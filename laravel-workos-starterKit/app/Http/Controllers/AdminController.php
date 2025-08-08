<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index(): Response
    {
        $users = User::all(['id', 'name', 'email', 'workos_id', 'created_at']);
        
        return Inertia::render('admin/index', [
            'users' => $users,
        ]);
    }

    /**
     * Show user details.
     */
    public function show(User $user): Response
    {
        return Inertia::render('admin/users/show', [
            'user' => $user,
        ]);
    }
} 