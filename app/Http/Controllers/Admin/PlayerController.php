<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerRequest;
use App\Http\Requests\TransferLogRequest;
use App\Models\PaymentType;
use App\Models\Report;
use App\Models\User;
use App\Services\UserService;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PlayerController extends Controller
{
    protected $userService;

    private const PLAYER_ROLE = 7;

    protected const SUB_AGENT_ROlE = 'Sub Agent';

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(
            Gate::denies('player_index'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        //kzt
        // $agent = $this->getAgent() ?? Auth::user();

        // $users = User::with('roles', 'poneWinePlayer', 'results', 'betNResults')
        //     ->whereHas('roles', function ($query) {
        //         $query->where('role_id', self::PLAYER_ROLE);
        //     })
        //     ->where('agent_id', $agent->id)
        //     ->orderBy('id', 'desc')
        //     ->get();

        $players = User::with(['roles', 'poneWinePlayer'])->whereHas('roles', fn ($query) => $query->where('role_id', self::PLAYER_ROLE))
            ->select('id', 'name', 'user_name', 'phone', 'status', 'referral_code')
            ->where('agent_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $reportData = DB::table('users as p')
            ->join('reports', 'reports.member_name', '=', 'p.user_name')
            ->groupBy('p.id')
            ->selectRaw('p.id as player_id,SUM(reports.bet_amount) as total_bet_amount,SUM(reports.payout_amount) as total_payout_amount')
            ->get()
            ->keyBy('player_id');

        $users = $players->map(function ($player) use ($reportData) {
            $report = $reportData->get($player->id);
            $poneWineTotalAmt = $player->children->flatMap->poneWinePlayer->sum('win_lose_amt');

            return (object) [
                'id' => $player->id,
                'name' => $player->name,
                'user_name' => $player->user_name,
                'phone' => $player->phone,
                'balanceFloat' => $player->balanceFloat,
                'status' => $player->status,
                'win_lose' => (($report->total_payout_amount ?? 0) - ($report->total_bet_amount ?? 0)) + $poneWineTotalAmt,
            ];
        });

        return view('admin.player.index', compact('users'));
    }

    /**
     * Display a listing of the users with their agents.
     *
     * @return \Illuminate\View\View
     */
    public function player_with_agent()
    {
        $users = User::player()->with('roles')->get();

        return view('admin.player.list', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(
            Gate::denies('player_create'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        $player_name = $this->generateRandomString();
        $agent = $this->getAgent() ?? Auth::user();
        //$owner_id = User::where('agent_id', $agent->agent_id)->first();
        // Get the related owner of the agent
        $owner = User::where('id', $agent->agent_id)->first(); // Assuming `agent_id` refers to the owner's ID

        //return $owner;

        return view('admin.player.create', compact('player_name', 'owner'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlayerRequest $request)
    {
        Gate::allows('player_store');

        $agent = $this->getAgent() ?? Auth::user();
        $siteLink = $agent->parent->parent->parent->parent->site_link ?? 'null';

        $inputs = $request->validated();

        try {
            if (isset($inputs['amount']) && $inputs['amount'] > $agent->balanceFloat) {
                return redirect()->back()->with('error', 'Balance Insufficient');
            }

            $user = User::create([
                'name' => $inputs['name'],
                'user_name' => $inputs['user_name'],
                'password' => Hash::make($inputs['password']),
                'phone' => $inputs['phone'],
                'agent_id' => $agent->id,
                'type' => UserType::Player,
            ]);

            $user->roles()->sync(self::PLAYER_ROLE);

            if (isset($inputs['amount'])) {
                app(WalletService::class)->transfer($agent, $user, $inputs['amount'],
                    TransactionName::CreditTransfer, [
                        'old_balance' => $user->balanceFloat,
                        'new_balance' => $user->balanceFloat + $request->amount,
                    ]);
            }

            return redirect()->back()
                ->with('successMessage', 'Player created successfully')
                ->with('amount', $request->amount)
                ->with('password', $request->password)
                ->with('site_link', $siteLink)
                ->with('user_name', $user->user_name);
        } catch (\Exception $e) {
            Log::error('Error creating user: '.$e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while creating the player.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort_if(
            Gate::denies('player_show'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $user_detail = User::findOrFail($id);

        return view('admin.player.show', compact('user_detail'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $player)
    {
        abort_if(
            Gate::denies('player_edit'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        return response()->view('admin.player.edit', compact('player'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $player)
    {

        $player->update($request->all());

        return redirect()->route('admin.player.index')->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $player)
    {
        abort_if(
            Gate::denies('player_delete') || ! $this->ifChildOfParent(request()->user()->id, $player->id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        $player->delete();

        return redirect()->route('admin.player.index')->with('success', 'User deleted successfully');
    }

    public function massDestroy(Request $request)
    {
        User::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }

    public function banUser($id)
    {
        abort_if(
            ! $this->ifChildOfParent(request()->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $user = User::find($id);
        $user->update(['status' => $user->status == 1 ? 0 : 1]);

        return redirect()->back()->with(
            'success',
            'User '.($user->status == 1 ? 'activate' : 'inactive').' successfully'
        );
    }

    public function getCashIn(User $player)
    {
        abort_if(
            Gate::denies('make_transfer'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        return view('admin.player.cash_in', compact('player'));
    }

    public function makeCashIn(TransferLogRequest $request, User $player)
    {
        abort_if(
            Gate::denies('make_transfer'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        try {
            $inputs = $request->validated();
            $inputs['refrence_id'] = $this->getRefrenceId();

            $agent = $this->getAgent() ?? Auth::user();

            $cashIn = $inputs['amount'];

            if ($cashIn > $agent->balanceFloat) {

                return redirect()->back()->with('error', 'You do not have enough balance to transfer!');
            }

            app(WalletService::class)->transfer($agent, $player, $request->validated('amount'),
                TransactionName::CreditTransfer, [
                    'note' => $request->note,
                    'old_balance' => $player->balanceFloat,
                    'new_balance' => $player->balanceFloat + $request->amount,
                ]);

            return redirect()->back()
                ->with('success', 'CashIn submitted successfully!');
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getCashOut(User $player)
    {
        abort_if(
            Gate::denies('make_transfer'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        return view('admin.player.cash_out', compact('player'));
    }

    public function makeCashOut(TransferLogRequest $request, User $player)
    {
        abort_if(
            Gate::denies('make_transfer'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        try {
            $inputs = $request->validated();
            $inputs['refrence_id'] = $this->getRefrenceId();

            $agent = $this->getAgent() ?? Auth::user();

            $cashOut = $inputs['amount'];

            if ($cashOut > $player->balanceFloat) {

                return redirect()->back()->with('error', 'You do not have enough balance to transfer!');
            }

            app(WalletService::class)->transfer($player, $agent, $request->validated('amount'),
                TransactionName::DebitTransfer, [
                    'note' => $request->note,
                    'old_balance' => $player->balanceFloat,
                    'new_balance' => $player->balanceFloat - $request->amount,
                ]);

            return redirect()->back()
                ->with('success', 'CashOut submitted successfully!');
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getChangePassword($id)
    {
        $player = User::find($id);

        return view('admin.player.change_password', compact('player'));
    }

    public function makeChangePassword($id, Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $player = User::find($id);
        $player->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()
            ->with('success', 'Player Change Password successfully')
            ->with('password', $request->password)
            ->with('username', $player->user_name);
    }

    public function playerReportIndex($id)
    {

        $startDate = request('start_date') ?? Carbon::today()->startOfDay()->toDateString();
        $endDate = request('end_date') ?? Carbon::today()->endOfDay()->toDateString();


        $reportDetail = DB::table('reports')
            ->join('products', 'products.code', '=', 'reports.product_code')
            ->select(
                'reports.*', 'products.name as provider_name',
            )
            ->where('reports.member_name', $id)
            ->whereBetween('reports.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->paginate(20)
            ->appends([
               'start_date' => $startDate,
               'end_date' => $endDate,
           ]);

        $total = [
            'total_bet_amt' => $reportDetail->sum('bet_amount'),
            'total_payout_amt' => $reportDetail->sum('payout_amount'),
            'total_net_win' =>   $reportDetail->sum('payout_amount') - $reportDetail->sum('bet_amount'),
        ];

        return view('admin.player.report_index', compact('reportDetail', 'total'));
    }

    private function generateRandomString()
    {
        $randomNumber = mt_rand(10000000, 99999999);

        return 'P'.$randomNumber;
    }

    private function getRefrenceId($prefix = 'REF')
    {
        return uniqid($prefix);
    }

    public function playersByAgent(Request $request, int $agentId)
    {
        $players = User::getPlayersByAgentId($agentId);

        return view('players.index', compact('players'));
    }

    private function isExistingUserForAgent($phone, $agent_id): bool
    {
        //return User::where('phone', $phone)->where('agent_id', $agent_id)->first();
        return User::where('phone', $phone)->where('agent_id', $agent_id)->exists();
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
}
