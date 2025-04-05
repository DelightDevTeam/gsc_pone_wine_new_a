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
                    <a href="{{ url('admin/slot/report') }}" class="btn btn-primary " style="width: 100px;"><i
                            class="fas fa-plus text-white  mr-2"></i>Back</a>
                </div>
                <div class="card " style="border-radius: 20px;">
                    <div class="card-header">
                        <h3>W/L Report Detail</h3>
                    </div>
                    <form role="form" class="text-start mt-4" action="{{ route('admin.reports.details', $playerId ) }}" method="GET">
                        <div class="row ml-5">
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-bold" for="inputEmail1">Product Type</label>
                                    <select name="product_id" id="" class="form-control">
                                        <option value="">Select Product type</option>
                                        @foreach($productTypes as $type)
                                        <option value="{{$type->id}}" {{$type->id == request()->product_id ? 'selected' : ''}}>{{$type->provider_name}}</option>
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
                                <a href="{{ route('admin.reports.details', $playerId ) }}" class="btn btn-warning" style="margin-top: 32px;">Refresh</a>
                            </div>
                        </div>
                    </form>
                    <div class="card-body">
                        <table id="mytable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Player Name</th>
                                    <th>ProviderName</th>
                                    <th>Game Name</th>
                                    <th>History</th>
                                    <th>Bet</th>
                                    <th>Win</th>
                                    <th>NetWin</th>
                                    <th>TransactionDateTime</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $detail)
                                <tr>
                                    <td>{{ $detail->user_name }}</td>
                                    <td>{{ $detail->provider_name }}</td>
                                    <td>{{ $detail->game_name }}</td>

                                    <td>
                                        <a href="javascript:void(0);"
                                            onclick="getTransactionDetails('{{ $detail->round_id }}')"
                                            style="color: blueviolet; text-decoration: underline;">
                                            {{ $detail->round_id }}
                                        </a>
                                    </td>
                                    <td>{{ number_format($detail->total_bet_amount, 2) }}</td>
                                    <td>{{ number_format($detail->win_amount, 2) }}</td>
                                    <td>{{ number_format($detail->net_win, 2) }}</td>
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

@section('script')
<script>
    function getTransactionDetails(tranId) {
        fetch(`/api/transaction-details/${tranId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Only if CSRF protection is enabled
                },
                body: JSON.stringify({
                    tranId: tranId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.Url) {
                    window.open(data.Url, '_blank');
                } else {
                    const newPageUrl =
                        `/transaction-details-page?tranId=${tranId}&details=${encodeURIComponent(JSON.stringify(data))}`;
                    window.open(newPageUrl, '_blank');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to get transaction details');
            });
    }
</script>
@endsection