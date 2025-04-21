<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SeniorRequest;
use App\Http\Requests\TransferLogRequest;
use App\Models\Admin\TransferLog;
use App\Models\User;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class SeniorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private const SENIOR_ROLE = 3;

    public function index()
    {
        abort_if(
            Gate::denies('senior_index'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        // $users = User::with(['roles', 'children.children.children.poneWinePlayer', 'children.children.children.results', 'children.children.children.betNResults'])
        //     ->whereHas('roles', function ($query) {
        //         $query->where('role_id', self::SENIOR_ROLE);
        //     })
        //     ->where('agent_id', auth()->id())
        //     ->orderBy('id', 'desc')
        //     ->get();

        $seniors = User::with(['roles', 'children.children.children.poneWinePlayer'])->whereHas('roles', fn ($query) => $query->where('role_id', self::SENIOR_ROLE))
            ->select('id', 'name', 'user_name', 'phone', 'status')
            ->where('agent_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $reportData = DB::table('users as s')
            ->join('users as m', 'm.agent_id', '=', 's.id')          // master
            ->join('users as a', 'a.agent_id', '=', 'm.id')          // agent
            ->join('users as p', 'p.agent_id', '=', 'a.id')          // player
            ->join('reports', 'reports.member_name', '=', 'p.user_name')
            ->groupBy('s.id')
            ->selectRaw('s.id as senior_id,SUM(reports.bet_amount) as total_bet_amount,SUM(reports.payout_amount) as total_payout_amount')
            ->get()
            ->keyBy('senior_id');

        // dd($reportData);
        $users = $seniors->map(function ($senior) use ($reportData) {
            $report = $reportData->get($senior->id);
            $poneWineTotalAmt = $senior->children->flatMap->children->flatMap->children->flatMap->poneWinePlayer->sum('win_lose_amt');

            return (object) [
                'id' => $senior->id,
                'name' => $senior->name,
                'user_name' => $senior->user_name,
                'phone' => $senior->phone,
                'balanceFloat' => $senior->balanceFloat,
                'status' => $senior->status,
                'win_lose' => (($report->total_bet_amount ?? 0) - ($report->total_payout_amount ?? 0)) + $poneWineTotalAmt,
            ];
        });

        //KS

        return view('admin.senior.index', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SeniorRequest $request)
    {
        abort_if(
            Gate::denies('senior_create'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        $admin = Auth::user();

        $inputs = $request->validated();

        $userPrepare = array_merge(
            $inputs,
            [
                'password' => Hash::make($inputs['password']),
                'agent_id' => Auth()->user()->id,
                'type' => UserType::Senior,
            ]
        );

        if (isset($inputs['amount']) && $inputs['amount'] > $admin->balanceFloat) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient balance for transfer.',
            ]);
        }

        $user = User::create($userPrepare);
        $user->roles()->sync(self::SENIOR_ROLE);

        if (isset($inputs['amount'])) {
            app(WalletService::class)->transfer(
                $admin,
                $user,
                $inputs['amount'],
                TransactionName::CreditTransfer,
                [
                    'old_balance' => $user->balanceFloat,
                    'new_balance' => $user->balanceFloat + $request->amount,
                ]
            );
        }

        return redirect()->route('admin.senior.index')
            ->with('successMessage', 'senior created successfully')
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
            Gate::denies('senior_create'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        $user_name = $this->generateRandomString();

        return view('admin.senior.create', compact('user_name', 'user_name'));
    }

    private function generateRandomString()
    {
        $randomNumber = mt_rand(10000000, 99999999);

        return 'S'.$randomNumber;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort_if(
            Gate::denies('senior_show'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $senior = User::find($id);

        return view('admin.senior.show', compact('senior'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_if(
            Gate::denies('senior_edit'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $senior = User::find($id);

        return view('admin.senior.edit', compact('senior'));
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

        $senior = User::find($id);

        return view('admin.senior.cash_in', compact('senior'));
    }

    public function getCashOut(string $id)
    {
        abort_if(
            Gate::denies('make_transfer'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $senior = User::findOrFail($id);

        return view('admin.senior.cash_out', compact('senior'));
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
            $senior = User::findOrFail($id);
            $admin = Auth::user();
            $cashIn = $inputs['amount'];
            if ($cashIn > $admin->balanceFloat) {
                throw new \Exception('You do not have enough balance to transfer!');
            }

            // Transfer money
            app(WalletService::class)->transfer(
                $admin,
                $senior,
                $request->validated('amount'),
                TransactionName::CreditTransfer,
                [
                    'note' => $request->note,
                    'old_balance' => $senior->balanceFloat,
                    'new_balance' => $senior->balanceFloat + $request->amount,
                ]
            );

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

            $senior = User::findOrFail($id);
            $admin = Auth::user();
            $cashOut = $inputs['amount'];

            if ($cashOut > $senior->balanceFloat) {

                return redirect()->back()->with('error', 'You do not have enough balance to transfer!');
            }

            // Transfer money
            app(WalletService::class)->transfer(
                $senior,
                $admin,
                $request->validated('amount'),
                TransactionName::DebitTransfer,
                [
                    'note' => $request->note,
                    'old_balance' => $senior->balanceFloat,
                    'new_balance' => $senior->balanceFloat - $request->amount,
                ]
            );

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

        return view('admin.senior.transfer_detail', compact('transfer_detail'));
    }

    public function bansenior($id)
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
            Gate::denies('senior_edit') || ! $this->ifChildOfParent($request->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden | You cannot access this page because you do not have permission'
        );

        $user = User::findOrFail($id);

        $user->update($request->all());

        return redirect()->back()->with('success', 'senior updated successfully!');
    }

    public function getChangePassword($id)
    {
        $senior = User::find($id);

        return view('admin.senior.change_password', compact('senior'));
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

        $senior = User::find($id);
        $senior->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()
            ->with('success', 'senior Change Password successfully')
            ->with('password', $request->password)
            ->with('username', $senior->user_name);
    }

    // KS Upgrade RPIndex
    public function seniorReportIndex($id)
    {
        $senior = User::with([
            'roles',
            'children.children.children.poneWinePlayer',
        ])->find($id);

        $poneWineTotalAmt = $senior->children->flatMap->children->flatMap->children->flatMap->poneWinePlayer->sum('win_lose_amt');

        $reportData = DB::table('users as s')
            ->join('users as m', 'm.agent_id', '=', 's.id')          // master
            ->join('users as a', 'a.agent_id', '=', 'm.id')          // agent
            ->join('users as p', 'p.agent_id', '=', 'a.id')          // player
            ->join('reports', 'reports.member_name', '=', 'p.user_name')
            ->groupBy('s.id')
            ->selectRaw('
    s.id as senior_id,
    SUM(reports.bet_amount) as total_bet_amount,
    SUM(reports.payout_amount) as total_payout_amount
')
            ->get()
            ->keyBy('senior_id');

        $report = $reportData->get($senior->id);
        $report = (object) [
            'win_lose' => ($report->total_bet_amount ?? 0) - ($report->total_payout_amount ?? 0),
            'total_win_lose_pone_wine' => $poneWineTotalAmt ?? 0,
        ];

        return view('admin.senior.report_index', compact('report'));
    }
}
