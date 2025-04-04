@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">W/L Report</li>
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
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('home') }}" class="btn btn-primary " style="width: 100px;"><i
                                class="fas fa-arrow-left text-white  mr-2"></i>Back</a>
                    </div>
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
                                        <input type="text" class="form-control" name="user_id"
                                            placeholder="Enter User ID" value="{{ request()->user_id }}">
                                    </div>

                                    <!-- Search Button -->
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card " style="border-radius: 20px;">


                        <div class="card-body">
                            <table id="" class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Player ID</th>
                                        {{-- <th>Player Name</th> --}}
                                        <th>Game Code</th>
                                        <th>Game Name</th>
                                        <th>Game Provider</th>
                                        <th>Total Bets</th>
                                        <th>Total Bet Amount</th>
                                        <th>Total Win Amount</th>
                                        <th>Total Net Win</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report as $data)
                                        <tr>
                                            <td>{{ $data->player_id }}</td>
                                            {{-- <td>{{ $data->player_name }}</td> --}}
                                            <td>{{ $data->game_code }}</td>
                                            <td>{{ $data->game_name }}</td>
                                            <td>{{ $data->game_provide_name }}</td>
                                            <td>
                                                @if ($data->total_results == 0)
                                                    {{ $data->total_bets }}
                                                @else
                                                    {{ $data->total_results }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($data->total_result_bet_amount == 0)
                                                    {{ number_format($data->total_bet_amount, 2) }}
                                                @else
                                                    {{ number_format($data->total_result_bet_amount, 2) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($data->total_result_bet_amount == 0)
                                                    {{ number_format($data->total_win_amount, 2) }}
                                                @else
                                                    {{ number_format($data->total_result_win_amount, 2) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($data->total_result_net_win == 0)
                                                    {{ number_format($data->total_net_win, 2) }}
                                                @else
                                                    {{ number_format($data->total_result_net_win, 2) }}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.game.report.detail', ['player_id' => $data->player_id, 'game_code' => $data->game_code]) }}"
                                                    class="btn btn-primary">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Laravel Pagination Links -->
                            <div class="d-flex justify-content-center">
                                {{ $report->links() }}
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
