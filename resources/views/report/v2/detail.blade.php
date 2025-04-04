@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Backup W/L Report Detail</li>
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
                        <h3>BackUp Winlose Report</h3>
                    </div>
                    <form role="form" class="text-start" action="{{ route('admin.reportv2.detail', $playerId) }}" method="GET">
                        <div class="row ml-5">
                            <div class="col-md-3">
                                <div class="input-group input-group-static mb-4">
                                    <label for="">Product Type</label>
                                    <select name="product_id" id="" class="form-control">
                                        <option value="">Select Product type</option>
                                        @foreach ($productTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->provider_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-bold" for="inputEmail1">From Date</label>
                                    <input type="date" class="form-control border border-1 border-secondary px-2"
                                        name="start_date" value="{{request()->start_date }}">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-bold" for="inputEmail1">To Date</label>
                                    <input type="date" class="form-control border border-1 border-secondary px-2"
                                        id="" name="end_date" value="{{request()->end_date }}">
                                </div>
                            </div>
                            <div class="col-log-3">
                                <button type="submit" class="btn btn-primary" style="margin-top: 32px;">Search</button>
                                <a href="{{ route('admin.reportv2.detail', $playerId) }}" class="btn btn-warning" style="margin-top: 32px;">Refresh</a>
                            </div>
                        </div>
                    </form>
                    <div class="card-body">
                        <table id="mytable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>PlayerID</th>
                                    <th>Name</th>
                                    <th>Product Name</th>
                                    <th>Game Name</th>
                                    <th>Valid Bet</th>
                                    <th>Win/Lose Amt</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $detail->user_name }}</td>
                                    <td>{{ $detail->name }}</td>
                                    <td>{{ $detail->provider_name }}</td>
                                    <td>{{ $detail->game_name }}</td>
                                    <td>{{ number_format($detail->total_bet_amount, 2) }}</td>
                                    <td><span
                                            class="{{ $detail->net_win > 0 ? 'text-success' : 'text-danger' }}">{{ number_format($detail->net_win, 2) }}</span>
                                    </td>
                                    <td>{{ $detail->date }}</td>
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