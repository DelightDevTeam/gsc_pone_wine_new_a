@extends('layouts.master')
@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard v1</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ number_format($user->wallet->balanceFloat, 2) }}</h3>
                            <p>Balance</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="#" class="small-box-footer"> <i class="fas "></i></a>
                    </div>
                </div>
                @if ($role['0'] == 'Senior')
                    <!-- ./col -->
                    <div class="col-lg-4 col-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ number_format($totalBalance->balance / 100, 2) }}</h3>

                                <p>Master Total Balance</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-stats-bars"></i>
                            </div>
                            <a href="{{ route('admin.agent.index') }}" class="small-box-footer">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>

                        </div>
                    </div>
                @endif
                @if ($role['0'] == 'Master')
                    <div class="col-lg-4 col-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ number_format($totalBalance->balance / 100, 2) }}</h3>
                                <p>Agent Total Balance</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-stats-bars"></i>
                            </div>
                            <a href="{{ route('admin.agent.index') }}" class="small-box-footer">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif
                <!-- ./col -->
                <!-- ./col -->
                @can('senior_access')
                    <div class="col-lg-4 col-6">
                        <!-- small box -->
                        <div class="small-box bg-danger">
                            <div class="inner">
                                @if ($playerBalance)
                                    <h3>{{ number_format($playerBalance->balance / 100, 2) }}</h3>
                                @else
                                    <h3>0.00</h3>
                                @endif
                                <p>Player Balance</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            <a href="{{ route('admin.playerList') }}" class="small-box-footer">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endcan

                <!-- ./col -->

                @can('senior_owner_access')
                    <div class="col-lg-4 col-6">
                        <!-- small box -->
                        <div class="small-box bg-warning">
                            <form action="{{ route('admin.balanceUp') }}" method="post">
                                @csrf
                                <div class="card-header p-3 pb-0">
                                    <h6 class="mb-1">Update Balance</h6>
                                    <p class="text-sm mb-0">
                                        Seninor can update balance.
                                    </p>
                                </div>
                                <div class="card-body p-3">
                                    <div class="input-group input-group-static my-4">
                                        <label>Amount</label>
                                        <input type="integer" class="form-control" name="balance">
                                    </div>

                                    <button class="btn bg-gradient-dark mb-0 float-end">Update </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endcan

                @can('owner_access')
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $totalSuper }}</h3>
                                <p>Total Super</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            @if (Auth::user()->hasRole('Owner'))
                                <a href="{{ route('admin.super.index') }}" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            @endif
                        </div>
                    </div>
                @endcan

                @canany(['owner_access', 'super_access'])
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-gradient-maroon">
                            <div class="inner">
                                <h3>{{ $totalSenior }}</h3>
                                <p>Total Senior</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            @if (Auth::user()->hasRole('Super'))
                                <a href="{{ route('admin.senior.index') }}" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            @else
                                <a href="#" class="small-box-footer"> <i class="fas "></i></a>
                            @endif
                        </div>
                    </div>
                @endcanany

                @canany(['owner_access', 'super_access', 'senior_access'])
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box  bg-gradient-purple">
                            <div class="inner">
                                <h3>{{ $totalMaster }}</h3>
                                <p>Total Master</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            @if (Auth::user()->hasRole('Senior'))
                                <a href="{{ route('admin.master.index') }}" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            @else
                                <a href="#" class="small-box-footer"> <i class="fas "></i></a>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-primary ">
                            <div class="inner">
                                <h3>{{ $totalAgent }}</h3>
                                <p>Total Agent</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            @if (Auth::user()->hasRole('Master'))
                                <a href="{{ route('admin.agent.index') }}" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            @else
                                <a href="#" class="small-box-footer"> <i class="fas "></i></a>
                            @endif
                        </div>
                    </div>
                @endcanany

                @can('master_access')
                    <div class="col-lg-4 col-6">
                        <!-- small box -->
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $totalAgent }}</h3>
                                <p>Total Agent</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            @if (Auth::user()->hasRole('Master'))
                                <a href="{{ route('admin.agent.index') }}" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            @else
                                <a href="#" class="small-box-footer"> <i class="fas "></i></a>
                            @endif
                        </div>
                    </div>
                @endcan



                @canany(['owner_access', 'super_access', 'senior_access', 'master_access', 'agent_access'])
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-gradient-success ">
                            <div class="inner">
                                <h3>{{ $totalPlayer }}</h3>
                                <p>Total Player</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>

                            @if (Auth::user()->hasRole('Agent'))
                                <a href="{{ route('admin.player.index') }}" class="small-box-footer">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            @else
                                <a href="#" class="small-box-footer"> <i class="fas "></i></a>
                            @endif
                        </div>
                    </div>
                @endcanany
                @can('agent_access')
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ number_format($totalWinlose, 2) }}</h3>
                                <p>Total WinLose</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            <a href="{{ route('admin.player.index') }}" class="small-box-footer">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-gray">
                            <div class="inner">
                                <h3>{{ number_format($todayWinlose, 2) }}</h3>
                                <p>Today WinLose</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            <div class="d-flex justify-content-around small-box-footer ">
                                <a href="{{ route('admin.report.index') }}" class=" text-decoration-none text-white">Slots
                                    more info <i class="fas fa-arrow-circle-right text-white "></i></a>
                                <span>|</span>
                                <a href="{{ route('admin.report.ponewine') }}" class=" text-decoration-none text-white">Pone
                                    Wine more info <i class="fas fa-arrow-circle-right text-white "></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ number_format($todayDeposit, 2) }}</h3>
                                <p>Today Deposit</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            <a href="{{ route('admin.agent.deposit') }}" class="small-box-footer">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ number_format($todayWithdraw, 2) }}</h3>
                                <p>Today Withdraw</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            <a href="{{ route('admin.agent.withdraw') }}" class="small-box-footer">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endcan
                <!-- ./col -->
            </div>
        </div>
    </section>
@endsection
