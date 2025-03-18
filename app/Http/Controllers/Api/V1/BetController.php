<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\User;
use App\Services\WalletService;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BetController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            '*.room_id' => 'required',
            '*.player_id' => 'required',
            '*.bet_no' => 'required',
            '*.bet_amount' => 'required',
            '*.win_lose' => 'required',
            '*.net_win' => 'required',
            '*.is_winner' => 'required',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validatedData as $data) {
                $player = $this->getUserByUsername($data['player_id']);
                if ($player) {
                    $this->handlePlayerTransaction($data, $player);
                    $results[] = [
                        'player_id' => $player->user_name,
                        'balance' => $player->balanceFloat,
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error('Transaction failed', $e->getMessage(), 500);
        }

        return $this->success($results, 'Transaction Successful');
    }

    private function getUserByUsername(string $username): ?User
    {
        return User::where('user_name', $username)->first();
    }

    private function handlePlayerTransaction($data, $player): void
    {
        Bet::create([
            'user_id' => $player->id,
            'user_name' => $player->user_name,
            'room_id' => $data['room_id'],
            'bet_no' => $data['bet_no'],
            'bet_amount' => $data['bet_amount'],
            'win_lose' => $data['win_lose'],
            'net_win' => $data['net_win'],
            'is_winner' => $data['is_winner'],
        ]);

        $this->updatePlayerBalance($player, $data['net_win'], $data['is_winner']);
    }

    private function updatePlayerBalance(User $player, $amountChanged, int $winLoseStatus): void
    {
        if ($winLoseStatus == 1) {
            (new WalletService)->deposit($player, $amountChanged, TransactionName::CapitalDeposit);
        } else {
            (new WalletService)->withdraw($player, abs($amountChanged), TransactionName::CapitalWithdraw);
        }

        $player->wallet->save();
    }
}
