@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Transaction</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                        <li class="breadcrumb-item active">Transaction</li>
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
                    <div class="card">
                        <div class="card-body">
                            <table id="mytable" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>UserName</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>CreatedAt</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($transferLogs as $log)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$log->targetUser->name }}</td>
                                        <td>{{ $log->amount }}</td>
                                        <td>
                                            @if($log->type == 'withdraw')
                                                <p class="text-success font-weight-bold">Deposit</p>
                                            @else
                                                <p class="text-danger font-weight-bold">Withdraw</p>
                                            @endif
                                        </td>
                                        <td>{{$log->created_at}}</td>
                                    </tr>
                                @endforeach
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
