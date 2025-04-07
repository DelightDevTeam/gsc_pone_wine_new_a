@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Player W/L Report</li>
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
                <div class="card " style="border-radius: 20px;">
                    <div class="card-header">
                        <h3>PoneWine Win/Lose Report</h3>
                    </div>
                    <div class="card-body">
                        <table id="ponewineTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>UserName</th>
                                    <th>TotalBetAmount</th>
                                    <th>TotalWinAmount</th>
                                    <th>Win Number</th>
                                    <th>MatchId</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($poneWineReport as $report)
                                <tr>
                                    <td>{{ $report->user_name }}</td>
                                    <td>{{ number_format($report->total_bet_amount, 2) }}</td>
                                    <td>{{ number_format($report->win_lose_amt, 2) }}</td>
                                    <td>{{ number_format($report->win_number, 2) }}</td>
                                    <td>{{ $report->match_id}}</td>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>

                <div class="card " style="border-radius: 20px;">
                    <div class="card-header">
                        <h3>Slot Win/Lose Report</h3>
                    </div>
                    <div class="card-body">
                        <table id="slotTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>UserName</th>
                                    <th>ProductName</th>
                                    <th>TotalStake</th>
                                    <th>TotalBetAmount</th>
                                    <th>TotalWin/Lose</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($slotReports as $report)
                                <tr>
                                    <td>{{ $report->user_name }}</td>
                                    <td>{{ $report->provider_name}}</td>
                                    <td>{{ number_format($report->total_count, 2) }}</td>
                                    <td>{{ number_format($report->total_bet_amount, 2) }}</td>
                                    <td>{{ number_format($report->net_win, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</section>
@endsection