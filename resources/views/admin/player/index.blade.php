@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Player Lists</li>
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
                        <a href="{{ route('admin.player.create') }}" class="btn btn-success " style="width: 100px;"><i
                                class="fas fa-plus text-white  mr-2"></i>Create</a>
                    </div>
                    <div class="card " style="border-radius: 20px;">
                        <div class="card-header">
                            <h3>Player Lists</h3>
                        </div>
                        <div class="card-body">
                            <table id="mytable" class="table table-bordered table-hover">
                                <thead class="text-center">
                                    <th>#</th>
                                    <th>PlayerID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Balance</th>
                                    <th>Total Win/Lose</th>
                                    {{-- <th>CreatedAt</th> --}}
                                    <th>Action</th>
                                    <th>Transaction</th>
                                </thead>
                                <tbody>
                                    @if (isset($users))
                                        @if (count($users) > 0)
                                            @foreach ($users as $user)
                                                <tr class="text-center">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <span class="d-block">{{ $user->user_name }}</span>

                                                    </td>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->phone }}</td>
                                                    <td>
                                                        <small
                                                            class="badge bg-gradient-{{ $user->status == 1 ? 'success' : 'danger' }}">{{ $user->status == 1 ? 'active' : 'inactive' }}</small>
                                                    </td>
                                                    <td class="text-bold">{{ number_format($user->balanceFloat) }}</td>
                                                    <td class="{{$user->win_lose >= 0 ? 'text-success text-bold' : 'text-danger text-bold'}}">{{ number_format($user->win_lose)}}</td>
                                                    {{-- <td>{{ $user->created_at->setTimezone('Asia/Yangon')->format('d-m-Y H:i:s') }} --}}
                                                    </td>
                                                    <td>
                                                        @if ($user->status == 1)
                                                            <a onclick="event.preventDefault(); document.getElementById('banUser-{{ $user->id }}').submit();"
                                                                class="me-2" href="#" data-bs-toggle="tooltip"
                                                                data-bs-original-title="Active Player">
                                                                <i class="fas fa-user-check text-success"
                                                                    style="font-size: 20px;"></i>
                                                            </a>
                                                        @else
                                                            <a onclick="event.preventDefault(); document.getElementById('banUser-{{ $user->id }}').submit();"
                                                                class="me-2" href="#" data-bs-toggle="tooltip"
                                                                data-bs-original-title="InActive Player">
                                                                <i class="fas fa-user-slash text-danger"
                                                                    style="font-size: 20px;"></i>
                                                            </a>
                                                        @endif
                                                        <form class="d-none" id="banUser-{{ $user->id }}"
                                                            action="{{ route('admin.player.ban', $user->id) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('PUT')
                                                        </form>
                                                        <a class="me-1"
                                                            href="{{ route('admin.player.getChangePassword', $user->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="Change Password">
                                                            <i class="fas fa-lock text-info" style="font-size: 20px;"></i>
                                                        </a>
                                                        <a class="me-1" href="{{ route('admin.player.edit', $user->id) }}"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Edit Agent">
                                                            <i class="fas fa-edit text-info" style="font-size: 20px;"></i>
                                                        </a>

                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.player.getCashIn', $user->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="Deposit To Player"
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-plus text-white me-1"></i>
                                                            Deposit
                                                        </a>
                                                        <a href="{{ route('admin.player.getCashOut', $user->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="WithDraw To Player"
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-minus text-white me-1"></i>
                                                            Withdrawl
                                                        </a>

                                                        <a href="{{ route('admin.logs', $user->id) }}"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Reports"
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-right-left text-white me-1"></i>
                                                            Logs
                                                        </a>
                                                        <a href="{{ route('admin.transferLogDetail', $user->id) }}"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Reports"
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-right-left text-white me-1"></i>
                                                            transferLogs
                                                        </a>
                                                        <a href="{{ route('admin.player.report', $user->user_name) }}"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Reports"
                                                            class="btn btn-info btn-sm ">
                                                            <i class="fas fa-right-left text-white me-1"></i>
                                                            Reports
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td col-span=8>
                                                    There was no Players.
                                                </td>
                                            </tr>
                                        @endif
                                    @endif

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
