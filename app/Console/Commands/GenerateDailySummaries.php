<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateDailySummaries extends Command
{
    protected $signature = 'generate:daily-summaries {date?}';
    protected $description = 'Generate daily summaries from reports data';

    public function handle()
    {
        $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : Carbon::yesterday();
        
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // 1. Generate overall summary (member_name = null, agent_id = null)
        $this->generateOverallSummary($date, $startOfDay, $endOfDay);

        // 2. Generate per-agent summaries (member_name = null)
        $this->generateAgentSummaries($date, $startOfDay, $endOfDay);

        // 3. Generate per-member summaries (with agent_id)
        $this->generateMemberSummaries($date, $startOfDay, $endOfDay);
        
        $this->info("Daily summaries for {$date->format('Y-m-d')} generated successfully!");
    }

    protected function generateOverallSummary($date, $startOfDay, $endOfDay)
    {
        $summary = DB::table('reports')
            ->select(
                DB::raw('SUM(valid_bet_amount) as total_valid_bet_amount'),
                DB::raw('SUM(payout_amount) as total_payout_amount'),
                DB::raw('SUM(bet_amount) as total_bet_amount'),
                DB::raw('SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END) as total_win_amount'),
                DB::raw('SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END) as total_lose_amount'),
                DB::raw('COUNT(*) as total_stake_count')
            )
            ->whereBetween('created_on', [$startOfDay, $endOfDay])
            ->first();
            
        DB::table('daily_summaries')->updateOrInsert(
            [
                'report_date' => $date->format('Y-m-d'),
                'member_name' => null,
                'agent_id' => null
            ],
            [
                'total_valid_bet_amount' => $summary->total_valid_bet_amount ?? 0,
                'total_payout_amount' => $summary->total_payout_amount ?? 0,
                'total_bet_amount' => $summary->total_bet_amount ?? 0,
                'total_win_amount' => $summary->total_win_amount ?? 0,
                'total_lose_amount' => $summary->total_lose_amount ?? 0,
                'total_stake_count' => $summary->total_stake_count ?? 0,
                'updated_at' => now(),
            ]
        );
    }

    protected function generateAgentSummaries($date, $startOfDay, $endOfDay)
    {
        $agentSummaries = DB::table('reports')
            ->select(
                'agent_id',
                DB::raw('SUM(valid_bet_amount) as total_valid_bet_amount'),
                DB::raw('SUM(payout_amount) as total_payout_amount'),
                DB::raw('SUM(bet_amount) as total_bet_amount'),
                DB::raw('SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END) as total_win_amount'),
                DB::raw('SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END) as total_lose_amount'),
                DB::raw('COUNT(*) as total_stake_count')
            )
            ->whereBetween('created_on', [$startOfDay, $endOfDay])
            ->groupBy('agent_id')
            ->get();

        foreach ($agentSummaries as $summary) {
            DB::table('daily_summaries')->updateOrInsert(
                [
                    'report_date' => $date->format('Y-m-d'),
                    'member_name' => null,
                    'agent_id' => $summary->agent_id
                ],
                [
                    'total_valid_bet_amount' => $summary->total_valid_bet_amount ?? 0,
                    'total_payout_amount' => $summary->total_payout_amount ?? 0,
                    'total_bet_amount' => $summary->total_bet_amount ?? 0,
                    'total_win_amount' => $summary->total_win_amount ?? 0,
                    'total_lose_amount' => $summary->total_lose_amount ?? 0,
                    'total_stake_count' => $summary->total_stake_count ?? 0,
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function generateMemberSummaries($date, $startOfDay, $endOfDay)
    {
        $memberSummaries = DB::table('reports')
            ->select(
                'member_name',
                'agent_id',
                DB::raw('SUM(valid_bet_amount) as total_valid_bet_amount'),
                DB::raw('SUM(payout_amount) as total_payout_amount'),
                DB::raw('SUM(bet_amount) as total_bet_amount'),
                DB::raw('SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END) as total_win_amount'),
                DB::raw('SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END) as total_lose_amount'),
                DB::raw('COUNT(*) as total_stake_count')
            )
            ->whereBetween('created_on', [$startOfDay, $endOfDay])
            ->groupBy('member_name', 'agent_id')
            ->get();

        foreach ($memberSummaries as $summary) {
            DB::table('daily_summaries')->updateOrInsert(
                [
                    'report_date' => $date->format('Y-m-d'),
                    'member_name' => $summary->member_name,
                    'agent_id' => $summary->agent_id
                ],
                [
                    'total_valid_bet_amount' => $summary->total_valid_bet_amount ?? 0,
                    'total_payout_amount' => $summary->total_payout_amount ?? 0,
                    'total_bet_amount' => $summary->total_bet_amount ?? 0,
                    'total_win_amount' => $summary->total_win_amount ?? 0,
                    'total_lose_amount' => $summary->total_lose_amount ?? 0,
                    'total_stake_count' => $summary->total_stake_count ?? 0,
                    'updated_at' => now(),
                ]
            );
        }
    }
}