@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">GCSGameProvider</li>
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
                            <h3>GSC Game Provider List</h3>
                        </div>
                        <div class="card-body">
                            <table id="mytable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="bg-success text-white">Game Type</th>
                                        <th class="bg-danger text-white">Product</th>
                                        <th class="bg-danger text-white">Code</th>
                                        <th class="bg-warning text-white">Image</th>
                                        <th class="bg-info text-white">Status</th>
                                        <th class="bg-info text-white">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gameTypes as $gameType)
                                        @foreach ($gameType->products as $product)
                                            <tr>
                                                <td class="text-center">{{ $gameType->name }}</td>
                                                <td class="text-center">{{ $product->short_name }}</td>
                                                <td class="text-center">{{ $product->code }}</td>
                                                <td class="text-center"><img src="{{ $product->getImgUrlAttribute() }}"
                                                        alt="" width="100px">
                                                </td>
                                                <td class="text-center">
                                                    <span class="status-label" data-product-id="{{ $product->id }}">
                                                        {{ $product->status == 1 ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-warning toggle-status-btn"
                                                        data-product-id="{{ $product->id }}"
                                                        data-status="{{ $product->status }}" style="width: 120px;">
                                                        {{ $product->status == 1 ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                    <a href="{{ route('admin.gametypes.edit', [$gameType->id, $product->id]) }}"
                                                        class="btn btn-info" style="width: 120px;">Edit</a>

                                                </td>
                                            </tr>
                                        @endforeach
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
        document.querySelectorAll('.toggle-status-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const currentStatus = this.getAttribute('data-status');
                const newStatus = currentStatus == 1 ? 0 : 1;
                const statusLabel = document.querySelector(`.status-label[data-product-id="${productId}"]`);

                fetch('{{ route('admin.gametypes.toggle-status', ':productId') }}'.replace(':productId',
                        productId), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({}),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.setAttribute('data-status', data.newStatus);
                            this.textContent = data.newStatus == 1 ? 'Deactivate' : 'Activate';
                            statusLabel.textContent = data.newStatus == 1 ? 'Active' : 'Inactive';
                            this.classList.toggle('btn-warning');
                            this.classList.toggle('btn-success');
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the status.');
                    });
            });
        });
    </script>
@endsection
