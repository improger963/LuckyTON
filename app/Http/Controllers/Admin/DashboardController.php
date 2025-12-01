<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\GameRoom;
use App\Models\Tournament;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * DashboardController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display the dashboard
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_transactions' => Transaction::count(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDay())->count(),
            'pending_withdrawals' => Transaction::pendingWithdrawals()->count(),
            'total_rooms' => GameRoom::count(),
            'total_tournaments' => Tournament::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
        ];

        $chartData = $this->getRegistrationChartData();
        $latestUsers = $this->getLatestUsers();
        $recentActivities = $this->getRecentActivities();

        return view('admin.dashboard.modern-index', compact('stats', 'chartData', 'latestUsers', 'recentActivities'));
    }

    /**
     * Get user registration chart data
     */
    private function getRegistrationChartData(int $days = 7): array
    {
        $registrations = $this->userRepository->getRegistrationChartData($days);
        
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $data[] = $registrations->where('date', $date)->first()?->count ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get latest registered users
     */
    private function getLatestUsers(int $limit = 5)
    {
        return User::with('wallet')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities(int $limit = 10)
    {
        return Transaction::with(['wallet.user'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}