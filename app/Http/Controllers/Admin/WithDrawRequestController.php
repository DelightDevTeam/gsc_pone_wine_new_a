<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithDrawRequest;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithDrawRequestController extends Controller
{
    protected const SUB_AGENT_ROLE = 'Sub Agent';

    public function index(Request $request)
    {
        $agent = $this->getAgent() ?? Auth::user();

        $withdraws = WithDrawRequest::where('agent_id', $agent->id)
            ->when($request->filled('status') && $request->input('status') !== 'all', function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.withdraw_request.index', compact('withdraws'));
    }

    public function statusChangeIndex(Request $request, WithDrawRequest $withdraw)
    {
        $agent = $this->getAgent() ?? Auth::user();

        $player = User::find($request->player);

        if ($request->status == 1 && $player->balanceFloat < $request->amount) {
            return redirect()->back()->with('error', 'Insufficient Balance!');
        }

        $withdraw->update([
            'status' => $request->status,
        ]);

        if ($request->status == 1) {
            app(WalletService::class)->transfer($player, $agent, $request->amount,
                TransactionName::DebitTransfer, [
                    'old_balance' => $player->balanceFloat,
                    'new_balance' => $player->balanceFloat - $request->amount,
                ]);
        }

        return redirect()->route('admin.agent.withdraw')->with('success', 'Withdraw status updated successfully!');
    }

    public function statusChangeReject(Request $request, WithDrawRequest $withdraw)
    {
        $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        try {
            $withdraw->update([
                'status' => $request->status,
            ]);

            return redirect()->route('admin.agent.withdraw')->with('success', 'Withdraw status updated successfully!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function isExistingAgent($userId)
    {
        $user = User::find($userId);

        return $user && $user->hasRole(self::SUB_AGENT_ROLE) ? $user->parent : null;
    }

    private function getAgent()
    {
        return $this->isExistingAgent(Auth::id());
    }
}
