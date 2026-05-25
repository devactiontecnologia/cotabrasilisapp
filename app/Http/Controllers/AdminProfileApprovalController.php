<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class AdminProfileApprovalController extends Controller
{
    /**
     * List users by approval status with tabs: Pendentes, Aprovados, Reprovados.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');

        $query = User::with('profile')
            ->where(function ($q) {
                $q->where('is_admin', false)->where(function ($r) {
                    $r->whereNull('role')->orWhere('role', '!=', 'admin');
                });
            });

        if ($tab === 'pending') {
            $query->where('profile_approval_status', User::PROFILE_APPROVAL_PENDING);
        } elseif ($tab === 'approved') {
            $query->where('profile_approval_status', User::PROFILE_APPROVAL_APPROVED);
        } elseif ($tab === 'rejected') {
            $query->where('profile_approval_status', User::PROFILE_APPROVAL_REJECTED);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.profile-approvals.index', compact('users', 'tab'));
    }

    /**
     * Approve a user's profile.
     */
    public function approve(Request $request, User $user)
    {
        if ($user->hasAdminPrivileges()) {
            return redirect()->route('admin.profile-approvals.index')->with('error', 'Não é possível alterar aprovação de administrador.');
        }

        $user->profile_approval_status = User::PROFILE_APPROVAL_APPROVED;
        $user->profile_approved_at = now();
        $user->profile_rejected_at = null;
        $user->show_approval_success_modal = true;
        $user->save();

        try {
            $emailService = new EmailService();
            $emailService->sendAccountApprovedEmail($user);
        } catch (\Throwable $e) {
            Log::warning('Erro ao enviar email de conta aprovada: ' . $e->getMessage());
        }

        return redirect()->route('admin.profile-approvals.index', ['tab' => 'pending'])
            ->with('success', 'Perfil de ' . $user->name . ' aprovado com sucesso.');
    }

    /**
     * Reject a user's profile.
     */
    public function reject(Request $request, User $user)
    {
        if ($user->hasAdminPrivileges()) {
            return redirect()->route('admin.profile-approvals.index')->with('error', 'Não é possível alterar aprovação de administrador.');
        }

        $user->profile_approval_status = User::PROFILE_APPROVAL_REJECTED;
        $user->profile_rejected_at = now();
        $user->profile_approved_at = null;
        $user->show_approval_success_modal = false;
        $user->save();

        return redirect()->route('admin.profile-approvals.index', ['tab' => 'pending'])
            ->with('success', 'Perfil de ' . $user->name . ' reprovado.');
    }
}
