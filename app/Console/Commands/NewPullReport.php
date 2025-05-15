<?php

namespace App\Console\Commands;

use App\Models\Admin\GameList;
use App\Models\Report;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
class NewPullReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:new-pull-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call Seamless PullReport API and log/display the response.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $operatorCode = config('game.api.operator_code');
        $secretKey = config('game.api.secret_key');
        $apiUrl = config('game.api.url') . '/Seamless/PullReport';

        $endDate = now();
        $startDate = $endDate->copy()->subMinutes(5);
        $requestTime = now()->format('YmdHis');
        $sign = md5($operatorCode . $requestTime . 'pullreport' . $secretKey);

        $payload = [
            'OperatorCode' => $operatorCode,
            'StartDate' => $startDate->format('Y-m-d H:i:s'),
            'EndDate' => $endDate->format('Y-m-d H:i:s'),
            'Sign' => $sign,
            'RequestTime' => $requestTime,
        ];

        Log::info('NewPullReport request payload', $payload);
        $this->info('Request Payload: ' . json_encode($payload));

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($apiUrl, $payload);

            Log::info('NewPullReport API response', ['body' => $response->body()]);
            $this->info('API Response: ' . $response->body());

            if ($response->successful() && $response->json('ErrorCode') == 0) {
                $data = $response->json();
                if (!empty($data['Wagers'])) {
                    foreach ($data['Wagers'] as $wager) {
                        $existing = Report::where('wager_id', $wager['WagerID'])->first();
                        $user = User::where('user_name', $report['MemberName'])->first();
                                $game_name = GameList::where('code', $report['GameID'])->first();
                                $report_game_name = $game_name ? $game_name->name : $report['GameID'];
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
                            Log::info('Wager updated', ['wager_id' => $wager['WagerID']]);
                        } else {
                            Report::create($fields);
                            Log::info('Wager created', ['wager_id' => $wager['WagerID']]);
                        }
                    }
                    $this->info('All wagers processed and stored in reports table.');
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
