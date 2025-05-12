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

class PullReportUpdateVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pull-report-update-version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $operatorCode;

    protected $secretKey;

    protected $apiUrl;

    public const VERSION_KEY = 1;

    public function __construct()
    {
        parent::__construct();
        $this->operatorCode = config('game.api.operator_code');
        $this->secretKey = config('game.api.secret_key');
        $this->apiUrl = config('game.api.url');
    }
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('PullReportUpdateVersion command started');
        $lockKey = 'pullreport-api-lock'; // You can change this key if needed
        $lockTimeout = 60; // Lock timeout in seconds, adjust as needed

        $lock = Cache::lock($lockKey, $lockTimeout);
        Log::info('PullReportUpdateVersion command started', ['lock' => $lock]);

        if ($lock->get()) {
            try {
                $apiUrl = $this->apiUrl.'/Seamless/PullReport';
                $operatorCode = Config::get('game.api.operator_code');
                Log::info('PullReportUpdateVersion command started', ['operatorCode' => $operatorCode]);
                $secretKey = Config::get('game.api.secret_key');
                Log::info('PullReportUpdateVersion command started', ['secretKey' => $secretKey]);

                $endDate = now();
                $startDate = $endDate->copy()->subMinutes(5);
                $requestTime = now()->format('YmdHis');
                $signature = md5($operatorCode.$requestTime.'pullreport'.$secretKey);

                $data = [
                    'OperatorCode' => $operatorCode,
                    'StartDate' => $startDate->format('Y-m-d H:i:s'),
                    'EndDate' => $endDate->format('Y-m-d H:i:s'),
                    'Sign' => $signature,
                    'RequestTime' => $requestTime,
                ];
                Log::info('PullReportUpdateVersion command started', ['data' => $data]);
                Log::debug('PullReport Request Payload', $data);
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($apiUrl, $data);
                Log::debug('PullReport API Response', ['body' => $response->body()]);
                Log::info('PullReportUpdateVersion command started', ['response' => $response->body()]);

                if ($response->successful() && $response->json('ErrorCode') == 0) {
                    $data = $response->json();
                    if (!empty($data['Wagers'])) {
                        $wagers = $data['Wagers'];
                        foreach ($wagers as $report) {
                            $wagerId = Report::where('wager_id', $report['WagerID'])->first();
                            $user = User::where('user_name', $report['MemberName'])->first();
                            $game_name = GameList::where('code', $report['GameID'])->first();
                            $report_game_name = $game_name ? $game_name->name : $report['GameID'];
                            $agent_id = $user ? $user->agent_id : null;
                            if ($wagerId) {
                                $wagerId->update([
                                    'member_name' => $report['MemberName'],
                                    'wager_id' => $report['WagerID'],
                                    'product_code' => $report['ProductID'],
                                    'game_type_id' => $report['GameType'],
                                    //'game_name' => $report['GameID'],
                                    'game_name' => $report_game_name,
                                    'game_round_id' => $report['GameRoundID'],
                                    'valid_bet_amount' => $report['ValidBetAmount'],
                                    'bet_amount' => $report['BetAmount'],
                                    'payout_amount' => $report['PayoutAmount'],
                                    'commission_amount' => $report['CommissionAmount'],
                                    'jack_pot_amount' => $report['JackpotAmount'],
                                    'jp_bet' => $report['JPBet'],
                                    'status' => $report['Status'],
                                    'created_on' => $report['CreatedOn'],
                                    'modified_on' => $report['ModifiedOn'],
                                    // 'settlement_date' => $report['SettlementDate'],
                                    'settlement_date' => $report['SettlementDate'] ?? now(),
                                    'agent_id' => $agent_id, // Store the agent_id
                                    'agent_commission' => 0.00,
                                ]);
                            } else {
                                Report::create([
                                    'member_name' => $report['MemberName'],
                                    'wager_id' => $report['WagerID'],
                                    'product_code' => $report['ProductID'],
                                    'game_type_id' => $report['GameType'],
                                    'game_name' => $report_game_name,
                                    'game_round_id' => $report['GameRoundID'],
                                    'valid_bet_amount' => $report['ValidBetAmount'],
                                    'bet_amount' => $report['BetAmount'],
                                    'payout_amount' => $report['PayoutAmount'],
                                    'commission_amount' => $report['CommissionAmount'],
                                    'jack_pot_amount' => $report['JackpotAmount'],
                                    'jp_bet' => $report['JPBet'],
                                    'status' => $report['Status'],
                                    'created_on' => $report['CreatedOn'],
                                    'modified_on' => $report['ModifiedOn'],
                                    //'settlement_date' => $report['SettlementDate'],
                                    'settlement_date' => $report['SettlementDate'] ?? now(),
                                    'agent_id' => $agent_id, // Store the agent_id
                                    'agent_commission' => 0.00,
                                ]);
                            }
                        }
                    }
                    $this->line('<fg=green>Pull Report success for interval: ' . $startDate . ' to ' . $endDate . '</>');
                } else {
                    Log::error('PullReport API Error or Call Failed', ['response' => $response->body()]);
                    $this->line('<fg=red>API Error or Call Failed for interval: ' . $startDate . ' to ' . $endDate . '</>');
                }
            } finally {
                $lock->release();
            }
        } else {
            Log::warning('PullReport: Another process is already running. Skipping this run.');
            $this->line('<fg=yellow>Another PullReport process is running. Skipping.</>');
        }
    }
}