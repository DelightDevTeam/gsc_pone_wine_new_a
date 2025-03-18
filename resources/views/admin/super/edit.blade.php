@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Edit Super</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card  col-lg-6 offset-lg-3 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1"
                style="border-radius: 15px;">
                <div class="card-header">
                    <div class="card-title col-12">
                        <h5 class="d-inline fw-bold">Edit Super</h5>
                        <a href="{{ route('admin.super.index') }}" class="btn btn-primary float-right">
                            <i class="fas fa-arrow-left" style="font-size: 20px;"></i> Back
                        </a>

                    </div>
                    </span>
                    </h3>
                </div>
                <form method="POST" action="{{ route('admin.super.update', $super->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label>SuperId<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="user_name" value="{{ $super->user_name }}"
                                readonly>
                        </div>
                        <div class="form-group">
                            <label>Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ $super->name }}" required>
                        </div>
                        <div class="form-group">
                            <label>Phone<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone" value="{{ $super->phone }}" required>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>
                </form>

            </div>

        </div>
        </div>
    </section>
@endsection
