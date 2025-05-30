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
                            <h3 class="text-center">Win/Lose Reports</h3>
                        </div>
                        <div class="card-body">
                            <table id="ponewineTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th>Slots</th>
                                        <th>PoneWine</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <td class="{{$report['slotTotalAmt'] >= 0 ? 'text-success' : 'text-danger'}}">
                                            {{ number_format( $report['slotTotalAmt']) }}</td>
                                        <td class="{{ $report['poneWineTotalAmt'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format( $report['poneWineTotalAmt'] ) }}</td>
                                    </tr>


                                </tbody>

                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>

                    {{-- <div class="card " style="border-radius: 20px;">
                    <div class="card-header">
                        <h3>Slot Win/Lose Report</h3>
                    </div>
                    <div class="card-body">
                        <table id="slotTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>UserName</th>
                                    <th>ProductName</th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td></td>
                                </tr>

                            </tbody>

                        </table>
                    </div>
                    <!-- /.card-body -->
                </div> --}}
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
@endsection
