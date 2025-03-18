<?php

namespace App\Http\Controllers\Admin\TransferLog;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TransferLogController extends Controller
{
    protected const SUB_AGENT_ROlE = 'Sub Agent';

    public function index(Request $request)
    {
        $agent = $this->getAgentOrCurrentUser();

        [$startDate, $endDate] = $this->parseDateRange($request);

        $transferLogs = $this->fetchTransferLogs($agent, $startDate, $endDate);
        $depositTotal = $this->fetchTotalAmount($agent, 'deposit', $startDate, $endDate);

        $withdrawTotal = $this->fetchTotalAmount($agent, 'withdraw', $startDate, $endDate);

        return view('admin.trans_log.index', compact('transferLogs', 'depositTotal', 'withdrawTotal'));
    }

    public function transferLog($id)
    {
        abort_if(
            Gate::denies('make_transfer') || ! $this->ifChildOfParent(request()->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden | You cannot access this page because you do not have permission'
        );
        $agent = $this->getAgent() ?? Auth::user();

        $transferLogs = $agent->transactions()->with('targetUser')
            ->whereIn('transactions.type', ['withdraw', 'deposit'])
            ->whereIn('transactions.name', ['credit_transfer', 'debit_transfer'])
            ->where('target_user_id', $id)->orderBy('transactions.id', 'desc')->paginate();

        return view('admin.trans_log.detail', compact('transferLogs'));
    }

    private function isExistingAgent($userId)
    {
        $user = User::find($userId);

        return $user && $user->hasRole(self::SUB_AGENT_ROlE) ? $user->parent : null;
    }

    private function getAgent()
    {
        return $this->isExistingAgent(Auth::id());
    }

    private function getAgentOrCurrentUser(): User
    {
        $user = Auth::user();

        return $this->findAgent($user->id) ?? $user;
    }

    private function findAgent(int $userId): ?User
    {
        $user = User::find($userId);

        return $user && $user->hasRole(self::SUB_AGENT_ROlE) ? $user->parent : null;
    }

    private function parseDateRange(Request $request): array
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::today()->endOfDay();

        return [$startDate->format('Y-m-d H:i'), $endDate->format('Y-m-d H:i')];
    }

    private function fetchTransferLogs(User $agent, string $startDate, string $endDate)
    {
        return $agent->transactions()
            ->with('targetUser')
            ->whereIn('type', ['withdraw', 'deposit'])
            ->whereIn('name', ['credit_transfer', 'debit_transfer'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('id')
            ->get();
    }

    private function fetchTotalAmount(User $agent, string $type, string $startDate, string $endDate): float
    {
        return $agent->transactions()
            ->with('targetUser')
            ->where('type', $type)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('name', ['credit_transfer', 'debit_transfer'])
            ->sum('amount');
    }
}
