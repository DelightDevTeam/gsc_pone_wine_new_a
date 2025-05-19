<?php

namespace App\Console\Commands;

use App\Models\Admin\GameList;
use App\Models\Report;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NewPullReport extends Command
{
    protected $signature = 'app:new-pull-report';
    protected $description = 'Call Seamless PullReport API and log/display the response.';

    public function handle()
    {
        $operatorCode = config('game.api.operator_code');
        $secretKey = config('game.api.secret_key');
        $apiUrl = config('game.api.url') . '/Seamless/PullReport';

        // ✅ Use timestamp tracking to avoid overlapping
        $endDate = now();
        $startDate = Cache::get('last_pull_time', $endDate->copy()->subMinutes(5));

        // Enforce maximum 5-minute range
        if ($endDate->diffInMinutes($startDate) > 5) {
            $startDate = $endDate->copy()->subMinutes(5);
        }

        $requestTime = now()->format('YmdHis');
        $sign = md5($operatorCode . $requestTime . 'pullreport' . $secretKey);

        $payload = [
            'OperatorCode' => $operatorCode,
            'StartDate' => $startDate->format('Y-m-d H:i:s'),
            'EndDate' => $endDate->format('Y-m-d H:i:s'),
            'Sign' => $sign,
            'RequestTime' => $requestTime,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($apiUrl, $payload);

            if ($response->successful() && $response->json('ErrorCode') == 0) {
                $data = $response->json();

                if (!empty($data['Wagers'])) {
                    foreach ($data['Wagers'] as $wager) {
                        $existing = Report::where('wager_id', $wager['WagerID'])->first();
                        $user = User::where('user_name', $wager['MemberName'])->first();
                        $game_name = GameList::where('code', $wager['GameID'])->first();
                        $report_game_name = $game_name ? $game_name->name : $wager['GameID'];
                        $agent_id = $user ? $user->agent_id : null;

                        $fields = [
                            'member_name' => $wager['MemberName'],
                            'wager_id' => $wager['WagerID'],
                            'product_code' => $wager['ProductID'],
                            'game_type_id' => $wager['GameType'],
                            'game_name' => $report_game_name,
                            'game_round_id' => $wager['GameRoundID'],
                            'valid_bet_amount' => $wager['ValidBetAmount'],
                            'bet_amount' => $wager['BetAmount'],
                            'payout_amount' => $wager['PayoutAmount'],
                            'commission_amount' => $wager['CommissionAmount'],
                            'jack_pot_amount' => $wager['JackpotAmount'],
                            'jp_bet' => $wager['JPBet'],
                            'status' => $wager['Status'],
                            'created_on' => $wager['CreatedOn'],
                            'settlement_date' => $wager['SettlementDate'] ?? now(),
                            'modified_on' => $wager['ModifiedOn'],
                            'agent_id' => $agent_id,
                            'agent_commission' => 0.00,
                        ];

                        if ($existing) {
                            $existing->update($fields);
                        } else {
                            Report::create($fields);
                        }
                    }

                    // ✅ Only update the last pull time if data is successfully processed
                    Cache::put('last_pull_time', $endDate);
                    $this->info('Wagers processed successfully. Last pull time updated.');
                } else {
                    $this->info('No wagers found in response.');
                }
            } else {
                $this->error('API call failed with status: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('NewPullReport Exception', ['message' => $e->getMessage()]);
            $this->error('Exception: ' . $e->getMessage());
        }
    }
}
