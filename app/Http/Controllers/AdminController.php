<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Quota;
use App\Models\QuotaTransaction;
use App\Models\Hotel;
use App\Models\AdminLog;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'blocked_users' => User::where('is_blocked', true)->count(),
            'total_quotas' => Quota::count(),
            'available_quotas' => Quota::where('status', 'available')->count(),
            'rented_quotas' => Quota::where('status', 'rented')->count(),
            'total_transactions' => QuotaTransaction::count(),
            'total_hotels' => Hotel::count(),
            'active_hotels' => Hotel::where('is_active', true)->count(),
        ];

        $recent_quotas = Quota::with('user')
            ->latest()
            ->limit(5)
            ->get();

        $recent_transactions = QuotaTransaction::with(['quota', 'renter', 'owner'])
            ->latest()
            ->limit(5)
            ->get();

        $recent_users = User::latest()
            ->limit(5)
            ->get();

        $recent_logs = AdminLog::with('admin')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recent_quotas',
            'recent_transactions',
            'recent_users',
            'recent_logs'
        ));
    }

    /**
     * Display all quotas with admin controls.
     */
    public function quotas()
    {
        $quotas = Quota::with('user')
            ->withCount('transactions')
            ->latest()
            ->paginate(20);

        // Statistics
        $totalQuotas = Quota::count();
        $availableQuotas = Quota::where('status', 'available')->count();
        $rentedQuotas = Quota::where('status', 'rented')->count();
        $totalValue = Quota::sum('rental_price');

        return view('admin.quotas.index', compact('quotas', 'totalQuotas', 'availableQuotas', 'rentedQuotas', 'totalValue'));
    }

    /**
     * Display quota details for admin.
     */
    public function showQuota(Quota $quota)
    {
        $quota->load(['user', 'transactions.renter', 'transactions.owner', 'rentalOffers']);
        
        return view('admin.quotas.show', compact('quota'));
    }

    /**
     * Display all users with admin controls.
     */
    public function users()
    {
        $users = User::with('profile')
            ->withCount(['quotas', 'rentalTransactions', 'ownedTransactions'])
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display all transactions.
     */
    public function transactions()
    {
        $transactions = QuotaTransaction::with(['quota', 'renter', 'owner'])
            ->latest()
            ->paginate(20);

        // Statistics
        $totalTransactions = QuotaTransaction::count();
        $completedTransactions = QuotaTransaction::where('status', 'completed')->count();
        $pendingTransactions = QuotaTransaction::where('status', 'pending')->count();
        $totalValue = QuotaTransaction::sum('total_amount');

        return view('admin.transactions.index', compact('transactions', 'totalTransactions', 'completedTransactions', 'pendingTransactions', 'totalValue'));
    }

    /**
     * Display transaction details for admin.
     */
    public function showTransaction(QuotaTransaction $transaction)
    {
        $transaction->load(['quota.user', 'renter', 'owner', 'digitalContract']);
        
        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Display all hotels.
     */
    public function hotels()
    {
        $hotels = Hotel::withCount('quotas')
            ->latest()
            ->paginate(20);

        return view('admin.hotels.index', compact('hotels'));
    }

    /**
     * Display admin logs.
     */
    public function logs()
    {
        $logs = AdminLog::with('admin')
            ->latest()
            ->paginate(50);

        return view('admin.logs.index', compact('logs'));
    }

    /**
     * Display notifications.
     */
    public function notifications()
    {
        $notifications = Notification::with('user')
            ->latest()
            ->paginate(50);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Log admin action.
     */
    public static function logAction($action, $modelType, $modelId = null, $oldData = null, $newData = null)
    {
        if (Auth::check()) {
            AdminLog::create([
                'admin_id' => Auth::id(),
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'old_data' => $oldData,
                'new_data' => $newData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
