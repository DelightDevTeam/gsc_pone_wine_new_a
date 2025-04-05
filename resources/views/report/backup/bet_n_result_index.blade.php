@extends('layouts.master')
@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">

                    <div class="card-body">
                        <h5 class="mb-0">Report BackUp(bet_n_result)</h5>
                    </div>
                    <div class="mt-2">
                        <form action="{{ route('admin.archive.bet_n_result') }}" method="POST">
                            @csrf
                            <label for="start_date">Start Date:</label>
                            <input type="date" id="start_date" name="start_date" required>

                            <label for="end_date">End Date:</label>
                            <input type="date" id="end_date" name="end_date" required>

                            <button type="submit">ReporeBackUp</button>
                        </form>

                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-flush" id="users-search">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>PlayerID</th>
                                <th>Game Code</th>
                                <th>Bet Amount</th>
                                <th>WinAmount</th>
                                <th>NetWin</th>
                                <th>Transaction Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($results as $index => $result)
                                <tr>
                                    <td>{{ $results->firstItem() + $index }}</td> <!-- Adjust for pagination -->
                                    <td>{{ $result->player_id }}</td>
                                    <td>{{ $result->game_code }}</td>
                                    <td>{{ number_format($result->bet_amount, 2) }}</td>
                                    <td>{{ number_format($result->win_amount, 2) }}</td>
                                    <td>{{ number_format($result->net_win, 2) }}</td>
                                    <td>{{ $result->tran_date_time }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Links -->
                    <div class="d-flex justify-content-center">
                        {{ $results->links() }}
                    </div>

                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        if (document.getElementById('users-search')) {
            const dataTableSearch = new simpleDatatables.DataTable("#users-search", {
                searchable: true,
                fixedHeight: false,
                perPage: 7
            });

        };
    </script>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
@endsection
