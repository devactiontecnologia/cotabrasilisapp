<?php

namespace App\Http\Controllers;

use App\Services\Asaas\AsaasSubaccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected AsaasSubaccountService $asaasSubaccountService
    ) {}

    /**
     * Show the user dashboard or the pending-approval screen.
     */
    public function index()
    {
        $user = request()->user();
        $profile = $user->profile;

        if (!$user->isProfileApproved()) {
            if ($user->isProfileRejected()) {
                return view('dashboard-rejected');
            }
            return view('dashboard-pending-approval');
        }

        $user->load('asaasSubaccount');
        $asaasSubaccount = $user->asaasSubaccount;
        $walletBalance = 0.0;
        $walletAvailable = false;

        if ($asaasSubaccount?->isActive()) {
            $walletBalance = $this->asaasSubaccountService->getBalance($asaasSubaccount);
            $walletAvailable = true;
        }

        return view('dashboard', compact('profile', 'asaasSubaccount', 'walletBalance', 'walletAvailable'));
    }

    /**
     * Clear the "show approval success modal" flag after the user has seen it.
     */
    public function clearApprovalModal(Request $request)
    {
        $user = $request->user();
        $user->show_approval_success_modal = false;
        $user->save();

        return response()->json(['ok' => true]);
    }

    /**
     * Estado da aprovação do cadastro (polling na tela de aguardando aprovação).
     */
    public function approvalStatus(Request $request): JsonResponse
    {
        $user = $request->user()->fresh();

        return response()->json([
            'approved' => $user->isProfileApproved(),
            'rejected' => $user->isProfileRejected(),
            'pending' => $user->isProfilePending(),
        ]);
    }
}
