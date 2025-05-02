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
                    <div class="card " style="border-radius: 20px;">
                        <div class="card-header">
                            <h3>Shan Report</h3>
                        </div>
                        <form role="form" class="text-start" action="{{ route('admin.shan_report') }}" method="GET">
                            <div class="row ml-5">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold" for="inputEmail1">From Date</label>
                                        <input type="date" class="form-control border border-1 border-secondary px-2"
                                            name="start_date" value="{{ request()->start_date }}">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold" for="inputEmail1">To Date</label>
                                        <input type="date" class="form-control border border-1 border-secondary px-2"
                                            id="" name="end_date" value="{{ request()->end_date }}">
                                    </div>
                                </div>
                                <div class="col-log-3">
                                    <button type="submit" class="btn btn-primary" style="margin-top: 32px;">Search</button>
                                    <a href="{{ route('admin.shan_report') }}" class="btn btn-warning"
                                        style="margin-top: 32px;">Refresh</a>
                                </div>
                            </div>
                        </form>
                        <div class="card-body">
                            <table id="mytable" class="table table-bordered  table-hover">
                                <thead class=" table-primary text-center">
                                    <tr>
                                        <th>#</th>
                                        @can('senior_owner_access')
                                            <th>Owner Id</th>
                                            <th>Senior Id</th>
                                            <th>Master ID</th>
                                            <th>Agent Id</th>
                                        @endcan
                                        @can('owner_access')
                                            <th>Senior Id</th>
                                            <th>Master ID</th>
                                            <th>Agent Id</th>
                                        @endcan
                                        @can('senior_access')
                                            <th>Master ID</th>
                                            <th>Agent Id</th>
                                        @endcan
                                        @can('master_access')
                                            <th>Agent Id</th>
                                        @endcan

                                        <th>Player ID</th>
                                        <th>Player Name</th>
                                        <th>Transaction ID</th>
                                        <th>Bet Amounts</th>
                                        <th>Payouts</th>
                                        <th>Net Win</th>
                                        <th>Bet Time</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center" style="font-size: 14px !important;">

                                    @foreach ($filteredReports as $row)
                                        <tr>
                                            <td>{{ ($filteredReports->currentPage() - 1) * $filteredReports->perPage() + $loop->iteration }}
                                            </td>
                                            @can('senior_owner_access')
                                                <td>{{ $row->owner_id }}</td>
                                                <td>{{ $row->senior_id }}</td>
                                                <td>{{ $row->master_id }}</td>
                                                <td>{{ $row->agent_id }}</td>
                                            @endcan
                                            @can('owner_access')
                                                <td>{{ $row->senior_id }}</td>
                                                <td>{{ $row->master_id }}</td>
                                                <td>{{ $row->agent_id }}</td>
                                            @endcan
                                            @can('senior_access')
                                                <td>{{ $row->master_id }}</td>
                                                <td>{{ $row->agent_id }}</td>
                                            @endcan
                                            @can('master_access')
                                                <td>{{ $row->agent_id }}</td>
                                            @endcan
                                            <td>{{ $row->player_id }}</td>
                                            <td>{{ $row->player_name }}</td>
                                            <td>{{ $row->transaction_id }}</td>
                                            <td>{{ $row->bet_amount / 100 }}</td>
                                            <?php
                                            if($row->status != 1) {
                                                if($row->transaction_amount < 0) {
                                                    $payout = $row->transaction_amount;
                                                } else {
                                                    $payout = 0;
                                                }

                                                if($payout == 0) {
                                                    $netWin = - $row->bet_amount ;
                                                } else {
                                                    $netWin = $row->transaction_amount - $row->bet_amount;
                                                }
                                            } else {
                                                $payout = $row->transaction_amount + $row->bet_amount;
                                                $netWin = $payout - $row->bet_amount;
                                            }
                                            ?>
                                           <td class="{{$payout/100 >= 0 ? 'text-success' : 'text-danger'}}">{{ $payout/100 }}</td>
                                           <td class="{{$netWin/100 >= 0 ? 'text-success' : 'text-danger'}}">{{ $netWin/100 }}</td>
                                           <td>{{ \Carbon\Carbon::parse($row->transaction_date)->format('H:i:s  d-m-Y') }}</a>

                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="text-center">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th>Total</th>
                                    <th>{{$data['total_bet']/100}}</th>
                                    <th>{{$data['total_payout']/100}}</th>
                                    <th>{{$data['total_netWin']/100}}</th>
                                    <th></th>

                                </tfoot>

                            </table>

                            <div class="text-center " style="font-weight: bold;">
                                {{ $filteredReports->links() }}
                            </div>

                            @if ($filteredReports->isEmpty())
                                <div class="text-center text-danger mt-3" style="font-weight: bold;">
                                    🔍 Data not found!
                                </div>
                            @endif

                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
@endsection
