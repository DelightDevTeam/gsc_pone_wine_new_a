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
                                        <th class="bg-warning text-white">Image</th>
                                        <th class="bg-info text-white">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gameTypes as $gameType)
                                        @foreach ($gameType->products as $product)  

                                            <tr>
                                                <td class="text-center">{{ $gameType->name }}</td>
                                                <td class="text-center">{{ $product->provider_name }}</td>
                                                <td class="text-center"><img src="{{ $product->getImgUrlAttribute() }}" alt=""
                                                        width="100px">
                                                </td>
                                                <td class="text-center">
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
