@extends('layouts.master')

@section('style')
<style>
    .pagination {
        margin: 20px 0;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    .table th, .table td {
        padding: 8px;
        text-align: left;
    }
</style>
@endsection

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Player Daily Total W/L Report</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 20px;">
                    <div class="card-header">
                        <h3>Total Daily Slot Win/Lose Report</h3>
                    </div>
                    <div class="card-body">
                        <table id="ponewineTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Member Name</th>
                                    <th>Agent ID</th>
                                    <th>Valid Bet Amount</th>
                                    <th>Payout Amount</th>
                                    <th>Total Bet Amount</th>
                                    <th>Win Amount</th>
                                    <th>Lose Amount</th>
                                    <th>Stake Count</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($summaries as $summary)
                                    <tr>
                                        <td>{{ $summary->report_date->format('Y-m-d') }}</td>
                                        <td>{{ $summary->member_name ?? 'N/A' }}</td>
                                        <td>{{ $summary->agent_id ?? 'N/A' }}</td>
                                        <td>{{ number_format($summary->total_valid_bet_amount) }}</td>
                                        <td>{{ number_format($summary->total_payout_amount) }}</td>
                                        <td>{{ number_format($summary->total_bet_amount) }}</td>
                                        <td>{{ number_format($summary->total_win_amount) }}</td>
                                        <td>{{ number_format($summary->total_lose_amount) }}</td>
                                        <td>{{ $summary->total_stake_count }}</td>
                                        <td>{{ $summary->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10">No summaries found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="pagination">
                            {{ $summaries->links() }}
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</section>
@endsection