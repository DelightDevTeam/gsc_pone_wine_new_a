<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Game Report</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="container mt-4">
        <!-- Report Header with Filters -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Game Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.game.report') }}">
                    <div class="row">
                        <!-- Date Range Filter -->
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date"
                                value="{{ request()->start_date }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date"
                                value="{{ request()->end_date }}">
                        </div>

                        <!-- User ID Filter -->
                        <div class="col-md-3">
                            <label for="user_id" class="form-label">User ID</label>
                            <input type="text" class="form-control" name="user_id" placeholder="Enter User ID"
                                value="{{ request()->user_id }}">
                        </div>

                        <!-- Search Button -->
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card mt-3 shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Valid Turnover</th>
                            <th>Win/Loss</th>
                            <th>Commission</th>
                            <th>Total P/L</th>
                            <th>PT Win/Loss</th>
                            <th>Comm.</th>
                            <th>Total P/L</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report as $data)
                            <tr data-bs-toggle="collapse" data-bs-target="#row{{ $loop->index }}" class="clickable">
                                <td>{{ $data->player_id }}</td>
                                <td>{{ $data->player_name }}</td>
                                <td>{{ number_format($data->total_bet_amount, 2) }}</td>
                                <td class="{{ $data->total_net_win < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($data->total_net_win, 2) }}
                                </td>
                                <td>{{ number_format($data->commission, 2) }}</td>
                                <td>{{ number_format($data->total_net_win + $data->commission, 2) }}</td>
                                <td>{{ number_format($data->pt_win_loss, 2) }}</td>
                                <td>{{ number_format($data->pt_commission, 2) }}</td>
                                <td>{{ number_format($data->pt_win_loss + $data->pt_commission, 2) }}</td>
                                <td>
                                    <a href="{{ route('admin.game.report.detail', ['player_id' => $data->player_id, 'game_code' => $data->game_code]) }}"
                                        class="btn btn-primary btn-sm">
                                        Details
                                    </a>
                                </td>
                            </tr>

                            <!-- Collapsible Row (Detailed Breakdown) -->
                            <tr class="collapse" id="row{{ $loop->index }}">
                                <td colspan="10">
                                    <strong>Game Provider:</strong> {{ $data->game_provide_name }}<br>
                                    <strong>Game Name:</strong> {{ $data->game_name }}<br>
                                    <strong>Old Balance:</strong> {{ number_format($data->old_balance, 2) }}<br>
                                    <strong>New Balance:</strong> {{ number_format($data->new_balance, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $report->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .clickable {
            cursor: pointer;
        }
    </style>


</body>

</html>
