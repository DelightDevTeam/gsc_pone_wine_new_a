<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerRequest;
use App\Http\Requests\TransferLogRequest;
use App\Models\Admin\TransferLog;
use App\Models\User;
use App\Services\WalletService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class OwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private const OWNER_ROLE = 2;

    public function index()
    {
        abort_if(
            Gate::denies('owner_index'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        // //kzt
        // $users = User::with([
        //     'roles',
        //     'children.children.children.children.children.poneWinePlayer',
        //     'children.children.children.children.children.results',
        //     'children.children.children.children.children.betNResults',
        // ]
        // )->whereHas('roles', function ($query) {
        //     $query->where('role_id', self::OWNER_ROLE);
        // })
        //     ->where('agent_id', auth()->id())
        //     ->orderBy('id', 'desc')
        //     ->get();
        //kzt

        //KS
        $owners = User::with(['roles', 'children.children.children.children.poneWinePlayer'])->whereHas('roles', fn ($query) => $query->where('role_id', self::OWNER_ROLE))
            ->select('id', 'name', 'user_name', 'phone', 'status')
            ->where('agent_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $reportData = DB::table('users as o')
            ->join('users as s', 's.agent_id', '=', 'o.id')          // senior
            ->join('users as m', 'm.agent_id', '=', 's.id')          // master
            ->join('users as a', 'a.agent_id', '=', 'm.id')          // agent
            ->join('users as p', 'p.agent_id', '=', 'a.id')          // player
            ->join('reports', 'reports.member_name', '=', 'p.user_name')
            ->groupBy('o.id')
            ->selectRaw('
        o.id as owner_id,
        SUM(reports.bet_amount) as total_bet_amount,
        SUM(reports.payout_amount) as total_payout_amount
    ')
            ->get()
            ->keyBy('owner_id');

        $users = $owners->map(function ($owner) use ($reportData) {
            $report = $reportData->get($owner->id);
            $poneWineTotalAmt = $owner->children->flatMap->children->flatMap->children->flatMap->children->flatMap->poneWinePlayer->sum('win_lose_amt');
            $winLose = ($report->total_bet_amount ?? 0) - ($report->total_payout_amount ?? 0);

            return (object) [
                'id' => $owner->id,
                'name' => $owner->name,
                'user_name' => $owner->user_name,
                'phone' => $owner->phone,
                'balanceFloat' => $owner->balanceFloat,
                'status' => $owner->status,
                'win_lose' => $winLose + $poneWineTotalAmt,
            ];
        });

        //KS
        return view('admin.owner.index', compact('users'));
    }

    public function OwnerPlayerList()
    {
        abort_if(
            Gate::denies('owner_access'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden | You cannot access this page because you do not have permission'
        );

        $adminId = auth()->id(); // Get the authenticated admin's ID

        $agents = User::with(['createdAgents.createdAgents.players'])
            ->where('id', $adminId) // Only fetch data for the current admin
            ->get();

        $players = $agents->pluck('createdAgents')
            ->flatten()
            ->pluck('createdAgents')
            ->flatten()
            ->pluck('players')
            ->flatten();

        return view('admin.player.list', compact('players'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OwnerRequest $request)
    {
        abort_if(
            Gate::denies('owner_create'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        $admin = Auth::user();

        $user_name = session()->get('user_name');

        $inputs = $request->validated();

        $userPrepare = array_merge(
            $inputs,
            [
                'user_name' => $user_name,
                'password' => Hash::make($inputs['password']),
                'agent_id' => Auth()->user()->id,
                'type' => UserType::Owner,
                'site_name' => $inputs['site_name'],
                'site_link' => $inputs['site_link'],
            ]
        );

        if (isset($inputs['amount']) && $inputs['amount'] > $admin->balanceFloat) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient balance for transfer.',
            ]);
        }
        // image
        if ($request->agent_logo) {
            $image = $request->file('agent_logo');
            $ext = $image->getClientOriginalExtension();
            $filename = uniqid('logo').'.'.$ext; // Generate a unique filename
            $image->move(public_path('assets/img/logo/'), $filename); // Save the file
            $userPrepare['agent_logo'] = $filename;
        }

        $user = User::create($userPrepare);
        $user->roles()->sync(self::OWNER_ROLE);

        if (isset($inputs['amount'])) {
            app(WalletService::class)->transfer($admin, $user, $inputs['amount'], TransactionName::CreditTransfer);
        }

        return redirect()->route('admin.owner.index')
            ->with('successMessage', 'Owner created successfully')
            ->with('password', $request->password)
            ->with('username', $user->user_name)
            ->with('amount', $request->amount);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(
            Gate::denies('owner_create'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        $user_name = $this->generateRandomString();

        session()->put('user_name', $user_name);

        return view('admin.owner.create', compact('user_name', 'user_name'));
    }

    private function generateRandomString()
    {
        $randomNumber = mt_rand(10000000, 99999999);

        return 'O'.$randomNumber;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort_if(
            Gate::denies('owner_show'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $master = User::find($id);

        return view('admin.owner.show', compact('master'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_if(
            Gate::denies('owner_edit'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $owner = User::find($id);

        return view('admin.owner.edit', compact('owner'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function getCashIn(string $id)
    {
        abort_if(
            Gate::denies('make_transfer'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $owner = User::find($id);

        return view('admin.owner.cash_in', compact('owner'));
    }

    public function getCashOut(string $id)
    {
        abort_if(
            Gate::denies('make_transfer'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        // Assuming $id is the user ID
        $owner = User::findOrFail($id);

        return view('admin.owner.cash_out', compact('owner'));
    }

    public function makeCashIn(TransferLogRequest $request, $id)
    {

        abort_if(
            Gate::denies('make_transfer') || ! $this->ifChildOfParent($request->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        try {

            $inputs = $request->validated();
            $master = User::findOrFail($id);
            $admin = Auth::user();
            $cashIn = $inputs['amount'];
            if ($cashIn > $admin->balanceFloat) {
                throw new \Exception('You do not have enough balance to transfer!');
            }

            // Transfer money
            app(WalletService::class)->transfer($admin, $master, $request->validated('amount'), TransactionName::CreditTransfer, ['note' => $request->note]);

            return redirect()->back()->with('success', 'Money fill request submitted successfully!');
        } catch (Exception $e) {

            session()->flash('error', $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function makeCashOut(TransferLogRequest $request, string $id)
    {

        abort_if(
            Gate::denies('make_transfer') || ! $this->ifChildOfParent($request->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        try {
            $inputs = $request->validated();

            $master = User::findOrFail($id);
            $admin = Auth::user();
            $cashOut = $inputs['amount'];

            if ($cashOut > $master->balanceFloat) {

                return redirect()->back()->with('error', 'You do not have enough balance to transfer!');
            }

            // Transfer money
            app(WalletService::class)->transfer($master, $admin, $request->validated('amount'), TransactionName::DebitTransfer, ['note' => $request->note]);

            return redirect()->back()->with('success', 'Money fill request submitted successfully!');
        } catch (Exception $e) {

            session()->flash('error', $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Money fill request submitted successfully!');
    }

    public function getTransferDetail($id)
    {
        abort_if(
            Gate::denies('make_transfer') || ! $this->ifChildOfParent(request()->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        $transfer_detail = TransferLog::where('from_user_id', $id)
            ->orWhere('to_user_id', $id)
            ->get();

        return view('admin.owner.transfer_detail', compact('transfer_detail'));
    }

    public function banOwner($id)
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

    public function update(Request $request, string $id)
    {
        abort_if(
            Gate::denies('owner_edit') || ! $this->ifChildOfParent($request->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden | You cannot access this page because you do not have permission'
        );

        $user = User::findOrFail($id);

        $request->validate([
            'user_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric',
            'agent_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'site_link' => 'nullable|string',

        ]);

        if ($request->file('agent_logo')) {
            if ($user->agent_logo && File::exists(public_path('assets/img/logo/'.$user->agent_logo))) {
                File::delete(public_path('assets/img/logo/'.$user->agent_logo));
            }

            $image = $request->file('agent_logo');
            $filename = uniqid('logo').'.'.$image->getClientOriginalExtension();
            $image->move(public_path('assets/img/logo/'), $filename);
            $user->agent_logo = $filename;
        } else {
            Log::info('No file uploaded for agent_logo.');
        }

        $user->update([
            'user_name' => $request->user_name ?? $user->user_name,
            'name' => $request->name,
            'phone' => $request->phone,
            'agent_logo' => $user->agent_logo,
            'site_name' => $user->site_name,
            'site_link' => $request->site_link,
        ]);

        return redirect()->back()
            ->with('success', 'Owner updated successfully!');
    }

    public function getChangePassword($id)
    {
        $owner = User::find($id);

        return view('admin.owner.change_password', compact('owner'));
    }

    public function makeChangePassword($id, Request $request)
    {
        abort_if(
            Gate::denies('make_transfer') || ! $this->ifChildOfParent(request()->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $master = User::find($id);
        $master->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()
            ->with('success', 'Owner Change Password successfully')
            ->with('password', $request->password)
            ->with('username', $master->user_name);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $owner)
    {
        if (! $owner) {
            return redirect()->back()->with('error', 'Banner Not Found');
        }

        $agentIds = User::where('agent_id', $owner->id)->pluck('id');
        User::whereIn('agent_id', $agentIds)->delete();

        User::where('agent_id', $owner->id)->delete();
        $owner->delete();

        return redirect()->back()->with('success', 'Banner Deleted.');
    }

    // KS Upgrade RPIndex
    public function ownerReportIndex($id)
    {
        $owner = User::with(['children.children.children.children.poneWinePlayer'])->where('id', $id)
            ->select('id', 'name', 'user_name', 'phone', 'status')
            ->first();

        //         $reportData = DB::table('users as o')
        //             ->join('users as s', 's.agent_id', '=', 'o.id')
        //             ->join('users as m', 'm.agent_id', '=', 's.id')
        //             ->join('users as a', 'a.agent_id', '=', 'm.id')
        //             ->join('users as p', 'p.agent_id', '=', 'a.id')
        //             ->join('reports', 'reports.member_name', '=', 'p.user_name')
        //             ->groupBy('o.id')
        //             ->selectRaw([
        //   'o.id as owner_id',
        //             'p.user_name as player_name',
        //             'reports.bet_amount',
        //             'reports.payout_amount',
        //             'reports.created_at',
        // ])
        //             ->get()
        //             ->keyBy('owner_id');

        $reportData = DB::table('users as o')
            ->join('users as s', 's.agent_id', '=', 'o.id')          // senior
            ->join('users as m', 'm.agent_id', '=', 's.id')          // master
            ->join('users as a', 'a.agent_id', '=', 'm.id')          // agent
            ->join('users as p', 'p.agent_id', '=', 'a.id')          // player
            ->join('reports', 'reports.member_name', '=', 'p.user_name')
            ->where('o.id', $owner->id)
            ->select([
                'o.id as owner_id',
                'p.user_name as player_name',
                'reports.bet_amount',
                'reports.payout_amount',
                'reports.created_at',
            ])
            ->get();

        $winLose = [];
        foreach ($reportData as $item) {
            $winLose = $item->payout_amount == 0 ? ($item->bet_amount - $item->bet_amount) : ($item->bet_amount - $item->payout_amount);
        }

        $poneWineTotalAmt = $owner->children->flatMap->children->flatMap->children->flatMap->children->flatMap->poneWinePlayer->sum('win_lose_amt');

        $report = (object) [
            'id' => $owner->id,
            'name' => $owner->name,
            'user_name' => $owner->user_name,
            'phone' => $owner->phone,
            'balanceFloat' => $owner->balanceFloat,
            'status' => $owner->status,
            'win_lose' => $winLose,
            'total_win_lose_pone_wine' => $poneWineTotalAmt,

        ];

        return view('admin.owner.report_index', compact('report'));
    }
}
