<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PoneWineBetRequest;
use App\Models\PoneWineBet;
use App\Models\PoneWineBetInfo;
use App\Models\PoneWinePlayerBet;
use App\Models\User;
use App\Services\WalletService;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PoneWineBetController extends Controller
{
    use HttpResponses;

    public function index(PoneWineBetRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        try {
            DB::beginTransaction();
            $results = [];
            
            foreach ($validatedData as $data) {
                $bet = $this->createBet($data);
                $results = array_merge($results, $this->processPlayers($data, $bet));
            }
            
            DB::commit();
            return $this->success($results, 'Transaction Successful');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Transaction failed', $e->getMessage(), 500);
        }
    }

    private function processPlayers(array $data, $bet): array
    {
        $results = [];
        
        foreach ($data['players'] as $playerData) {
            $player = $this->getUserByUsername($playerData['playerId']);
            if (!$player) continue;

            $this->handlePlayerTransaction($data, $playerData, $player, $bet);
            $results[] = [
                'playerId' => $player->user_name,
                'balance' => $player->balanceFloat,
            ];
        }
        
        return $results;
    }

    private function getUserByUsername(string $username): ?User
    {
        return User::where('user_name', $username)->first();
    }

    private function handlePlayerTransaction(array $data, array $playerData, User $player, $bet): void
    {
        $betPlayer = $this->createBetPlayer($bet, $player, $playerData['winLoseAmount']);
        $this->createBetInfos($betPlayer, $playerData['betInfos']);
        $this->updatePlayerBalance($player, $playerData['winLoseAmount']);
    }

    private function createBet(array $data): PoneWineBet
    {
        return PoneWineBet::create([
            'room_id' => $data['roomId'],
            'match_id' => $data['matchId'],
            'win_number' => $data['winNumber'],
        ]);
    }

    private function createBetPlayer(PoneWineBet $bet, User $player, float $winLoseAmount): PoneWinePlayerBet
    {
        return PoneWinePlayerBet::create([
            'pone_wine_bet_id' => $bet->id,
            'user_id' => $player->id,
            'user_name' => $player->user_name,
            'win_lose_amt' => $winLoseAmount,
        ]);
    }

    private function createBetInfos(PoneWinePlayerBet $betPlayer, array $betInfos): void
    {
        foreach ($betInfos as $info) {
            PoneWineBetInfo::create([
                'bet_no' => $info['betNumber'],
                'bet_amount' => $info['betAmount'],
                'pone_wine_player_bet_id' => $betPlayer->id,
            ]);
        }
    }

    private function updatePlayerBalance(User $player, float $amountChanged): void
    {
        $walletService = new WalletService();
        
        if ($amountChanged > 0) {
            $walletService->deposit($player, $amountChanged, TransactionName::CapitalDeposit);
        } else {
            $walletService->withdraw($player, abs($amountChanged), TransactionName::CapitalWithdraw);
        }
    }
}
