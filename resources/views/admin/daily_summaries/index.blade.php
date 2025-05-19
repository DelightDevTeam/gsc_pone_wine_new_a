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

        .table th,
        .table td {
            padding: 8px;
            text-align: left;
        }

        .date-filter-form {
            margin-bottom: 20px;
        }

        .date-filter-form .form-group {
            margin-right: 15px;
            display: inline-block;
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

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-radius: 20px;">
                        <div class="card-header">
                            <h3>Total Daily Slot Win/Lose Report</h3>
                        </div>
                        <div class="card-body">
                            <!-- Date Filter Form -->
                            {{-- <form method="GET" action="{{ route('admin.daily_summaries.index') }}" class="date-filter-form">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('admin.daily_summaries.index') }}" class="btn btn-secondary">Clear</a>
                        </form> --}}
                        @can('senior_owner_access')
                            <form method="POST" action="{{ route('admin.generate_daily_sammary') }}"
                                class="date-filter-form">
                                @csrf
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Generate Summaries</button>
                            </form>
                        @endcan
                            

                            <!-- Add this to display results -->
                            <div id="generationResult" class="mt-3"></div>
                            @if(session('success'))
       <div class="alert alert-success">{{ session('success') }}</div>
   @endif
   @if(session('error'))
       <div class="alert alert-danger">{{ session('error') }}</div>
   @endif

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
                                            <td>{{ $summary->report_date_formatted }}</td>
                                            <td>{{ $summary->member_name ?? 'N/A' }}</td>
                                            <td>{{ $summary->agent_id ?? 'N/A' }}</td>
                                            <td>{{ number_format($summary->total_valid_bet_amount ?? 0) }}</td>
                                            <td>{{ number_format($summary->total_payout_amount) }}</td>
                                            <td>{{ number_format($summary->total_bet_amount) }}</td>
                                            <td>{{ number_format($summary->total_win_amount) }}</td>
                                            <td>{{ number_format($summary->total_lose_amount) }}</td>
                                            <td>{{ $summary->total_stake_count }}</td>
                                            <td>{{ $summary->created_at_formatted }}</td>
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
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
<!-- <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script> -->

    <script>
        // Handle form submission with AJAX
        $('.date-filter-form').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    let html = `<div class="alert alert-success">
                <strong>Success!</strong> ${response.message}<br>
                Processed dates: ${response.processed_dates.join(', ')}<br>
                Total summaries created: ${response.total_summaries_created}
            </div>`;
                    $('#generationResult').html(html);
                },
                error: function(xhr) {
                    let error = xhr.responseJSON.error || 'Unknown error occurred';
                    $('#generationResult').html(`<div class="alert alert-danger">${error}</div>`);
                }
            });
        });
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('ponewineTable');
        if (table) {
            $('#ponewineTable').DataTable(); // Only initialize if table exists
        }

        const form = document.querySelector('.date-filter-form');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: $(form).serialize(),
                    success: function (response) {
                        const html = `<div class="alert alert-success">
                            <strong>Success!</strong> ${response.message}<br>
                            Processed dates: ${response.processed_dates.join(', ')}<br>
                            Total summaries created: ${response.total_summaries_created}
                        </div>`;
                        $('#generationResult').html(html);
                    },
                    error: function (xhr) {
                        const error = xhr.responseJSON?.error || 'Unknown error occurred';
                        $('#generationResult').html(`<div class="alert alert-danger">${error}</div>`);
                    }
                });
            });
        }
    });
</script>

@endsection
