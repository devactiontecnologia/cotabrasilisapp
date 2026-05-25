<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{

    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::with('profile')
            ->withCount(['quotas', 'rentalTransactions', 'ownedTransactions'])
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'whatsapp' => 'nullable|string|max:20',
            'role' => 'required|in:admin,moderator,user',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $userData = $request->only([
            'name', 'email', 'whatsapp', 'role', 'is_admin', 'is_active'
        ]);
        $userData['password'] = Hash::make($request->password);

        $user = User::create($userData);

        // Create user profile
        UserProfile::create([
            'user_id' => $user->id,
            'profile_type' => 'basic',
            'can_fraction' => false,
            'max_fractions' => 0,
            'is_verified' => false,
        ]);

        AdminController::logAction('created', 'User', $user->id, null, $user->toArray());

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['profile', 'quotas', 'rentalTransactions', 'ownedTransactions', 'notifications']);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'whatsapp' => 'nullable|string|max:20',
            'role' => 'required|in:admin,moderator,user',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
        ]);

        $oldData = $user->toArray();

        $userData = $request->only([
            'name', 'email', 'whatsapp', 'role', 'is_admin', 'is_active', 'is_blocked'
        ]);

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        AdminController::logAction('updated', 'User', $user->id, $oldData, $user->toArray());

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Block or unblock a user.
     */
    public function toggleBlock(User $user)
    {
        $oldData = $user->toArray();
        
        $user->update([
            'is_blocked' => !$user->is_blocked,
            'blocked_until' => $user->is_blocked ? null : now()->addDays(30)
        ]);

        $action = $user->is_blocked ? 'blocked' : 'unblocked';
        AdminController::logAction($action, 'User', $user->id, $oldData, $user->toArray());

        return redirect()->back()
            ->with('success', $user->is_blocked ? 'Usuário bloqueado!' : 'Usuário desbloqueado!');
    }

    /**
     * Activate or deactivate a user.
     */
    public function toggleActive(User $user)
    {
        $oldData = $user->toArray();
        
        $user->update(['is_active' => !$user->is_active]);

        $action = $user->is_active ? 'activated' : 'deactivated';
        AdminController::logAction($action, 'User', $user->id, $oldData, $user->toArray());

        return redirect()->back()
            ->with('success', $user->is_active ? 'Usuário ativado!' : 'Usuário desativado!');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $oldData = $user->toArray();
        
        $user->delete();

        AdminController::logAction('deleted', 'User', $user->id, $oldData, null);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário removido com sucesso!');
    }
}
