<?php

namespace App\Http\Controllers;

use App\Models\Admin\BetresultBackup;
use App\Models\BackupReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultArchiveController extends Controller
{
    public function getAllResults()
    {
        // Fetch results with pagination (10 results per page)
        $results = BackupReport::orderBy('created_at', 'asc')->paginate(10);

        // Pass the results to the view
        return view('report.backup.result_index', compact('results'));
    }

    public function archiveResults(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $startOfDay = Carbon::parse($request->start_date)->startOfDay();
            $endOfDay = Carbon::parse($request->end_date)->endOfDay();
        } catch (\Exception $e) {
            return back()->with('error', 'Invalid date format. Please provide a valid start and end date.');
        }

        try {
            DB::table('results')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->orderBy('id')
                ->chunk(1000, function ($oldResults) {
                    if ($oldResults->isEmpty()) {
                        session()->flash('info', 'No results found to archive.');

                        return;
                    }

                    DB::transaction(function () use ($oldResults) {
                        $oldResults->chunk(100)->each(function ($batch) {
                            try {
                                DB::table('result_backups')->insert(
                                    $batch->map(function ($result) {
                                        return [
                                            'user_id' => $result->user_id,
                                            'player_name' => $result->player_name,
                                            'game_provide_name' => $result->game_provide_name,
                                            'game_name' => $result->game_name,
                                            'operator_id' => $result->operator_id,
                                            'request_date_time' => $result->request_date_time,
                                            'signature' => $result->signature,
                                            'player_id' => $result->player_id,
                                            'currency' => $result->currency,
                                            'round_id' => $result->round_id,
                                            'bet_ids' => $result->bet_ids,
                                            'result_id' => $result->result_id,
                                            'game_code' => $result->game_code,
                                            'total_bet_amount' => $result->total_bet_amount,
                                            'win_amount' => $result->win_amount,
                                            'net_win' => $result->net_win,
                                            'tran_date_time' => $result->tran_date_time,
                                            'created_at' => $result->created_at,
                                            'updated_at' => $result->updated_at,
                                        ];
                                    })->toArray()
                                );
                            } catch (\Exception $e) {
                                Log::error('Error inserting results into result_backups: '.$e->getMessage());
                            }
                        });

                        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                        DB::table('results')->whereIn('id', $oldResults->pluck('id')->toArray())->delete();
                        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    });
                });

            return back()->with('success', 'Results have been archived and deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error archiving results: '.$e->getMessage());

            return back()->with('error', 'An error occurred while archiving results. Check logs for details.');
        }
    }

    public function getAllBetNResults()
    {
        // Fetch results with pagination (10 results per page)
        $results = BetresultBackup::orderBy('created_at', 'asc')->paginate(10);

        // Pass the results to the view
        return view('report.backup.bet_n_result_index', compact('results'));
    }

    public function archiveBetNResults(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $startOfDay = Carbon::parse($request->start_date)->startOfDay();
            $endOfDay = Carbon::parse($request->end_date)->endOfDay();
        } catch (\Exception $e) {
            return back()->with('error', 'Invalid date format. Please provide a valid start and end date.');
        }

        try {
            DB::table('bet_n_results')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->orderBy('id')
                ->chunk(1000, function ($oldResults) {
                    if ($oldResults->isEmpty()) {
                        session()->flash('info', 'No results found to archive.');

                        return;
                    }

                    DB::transaction(function () use ($oldResults) {
                        $oldResults->chunk(100)->each(function ($batch) {
                            try {
                                DB::table('betresult_backups')->insert(
                                    $batch->map(function ($result) {
                                        return [
                                            'user_id' => $result->user_id,
                                            'operator_id' => $result->operator_id,
                                            'request_date_time' => $result->request_date_time,
                                            'signature' => $result->signature,
                                            'player_id' => $result->player_id,
                                            'currency' => $result->currency,
                                            'tran_id' => $result->tran_id,
                                            'game_code' => $result->game_code,
                                            'bet_amount' => $result->bet_amount,
                                            'win_amount' => $result->win_amount,
                                            'net_win' => $result->net_win,
                                            'tran_date_time' => $result->tran_date_time,
                                            'provider_code' => $result->provider_code,
                                            'auth_token' => $result->auth_token,
                                            'status' => $result->status,
                                            'cancelled_at' => $result->cancelled_at,
                                            'old_balance' => $result->old_balance,
                                            'new_balance' => $result->new_balance,
                                            'created_at' => $result->created_at,
                                            'updated_at' => $result->updated_at,
                                        ];
                                    })->toArray()
                                );
                            } catch (\Exception $e) {
                                Log::error('Error inserting results into result_backups: '.$e->getMessage());
                            }
                        });

                        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                        DB::table('bet_n_results')->whereIn('id', $oldResults->pluck('id')->toArray())->delete();
                        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    });
                });

            return back()->with('success', 'Results have been archived and deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error archiving results: '.$e->getMessage());

            return back()->with('error', 'An error occurred while archiving results. Check logs for details.');
        }
    }

    // public function archiveResults(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //     ]);

    //     try {
    //         $startOfDay = Carbon::parse($request->start_date)->startOfDay();
    //         $endOfDay = Carbon::parse($request->end_date)->endOfDay();
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Invalid date format. Please provide a valid start and end date.');
    //     }

    //     try {
    //         DB::table('results')
    //             ->whereBetween('created_at', [$startOfDay, $endOfDay])
    //             ->orderBy('id')
    //             ->chunk(1000, function ($oldResults) {
    //                 if ($oldResults->isEmpty()) {
    //                     session()->flash('info', 'No results found to archive.');
    //                     return;
    //                 }

    //                 DB::transaction(function () use ($oldResults) {
    //                     $oldResults->chunk(100)->each(function ($batch) {
    //                         try {
    //                             DB::table('result_backups')->insert(
    //                                 $batch->map(function ($result) {
    //                                     return [
    //                                         'user_id' => $result->user_id,
    //                                         'player_name' => $result->player_name,
    //                                         'game_provide_name' => $result->game_provide_name,
    //                                         'game_name' => $result->game_name,
    //                                         'operator_id' => $result->operator_id,
    //                                         'request_date_time' => $result->request_date_time,
    //                                         'signature' => $result->signature,
    //                                         'player_id' => $result->player_id,
    //                                         'currency' => $result->currency,
    //                                         'round_id' => $result->round_id,
    //                                         'bet_ids' => $result->bet_ids,
    //                                         'result_id' => $result->result_id,
    //                                         'game_code' => $result->game_code,
    //                                         'total_bet_amount' => $result->total_bet_amount,
    //                                         'win_amount' => $result->win_amount,
    //                                         'net_win' => $result->net_win,
    //                                         'tran_date_time' => $result->tran_date_time,
    //                                         'created_at' => $result->created_at,
    //                                         'updated_at' => $result->updated_at,
    //                                     ];
    //                                 })->toArray()
    //                             );
    //                         } catch (\Exception $e) {
    //                             Log::error('Error inserting results into result_backups: ' . $e->getMessage());
    //                         }
    //                     });

    //                     DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    //                     DB::table('results')->whereIn('id', $oldResults->pluck('id')->toArray())->delete();
    //                     DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    //                 });
    //             });

    //         return back()->with('success', 'Results have been archived and deleted successfully.');
    //     } catch (\Exception $e) {
    //         Log::error('Error archiving results: ' . $e->getMessage());
    //         return back()->with('error', 'An error occurred while archiving results. Check logs for details.');
    //     }
    // }
}
