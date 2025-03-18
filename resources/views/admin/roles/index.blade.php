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
        </div>
        <div class="card " style="border-radius: 20px;">
          <div class="card-header">
            <h3>Role Lists</h3>
          </div>
          <div class="card-body">
            <table id="mytable" class="table table-bordered table-hover">
              <thead>
                <th>#</th>
                <th>Name</th>
                <th>Action</th>
              </thead>
              <tbody>
                @if (isset($roles))
                @if (count($roles) > 0)
                @foreach ($roles as $role)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <span class="d-block">{{ $role->title }}</span>
                  </td>
                  <td>
                    <a class="me-1" href="{{ route('admin.roles.edit', $role->id) }}"
                      data-bs-toggle="tooltip" data-bs-original-title="Edit Agent">
                      <i class="fas fa-edit text-info" style="font-size: 20px;"></i>
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