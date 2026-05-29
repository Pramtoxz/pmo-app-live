@extends('layouts.app')

@section('page-title', 'Katalog Kendaraan')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Katalog Kendaraan</h6>
                    <a href="{{ route('admin.katalog.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Upload Katalog
                    </a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                @if(session('success'))
                    <div class="alert alert-success mx-4 mt-3">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger mx-4 mt-3">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive p-4">
                    <table id="katalog-table" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kode Motor</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Motor</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kategori</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">File PDF</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($katalogs as $katalog)
                            <tr>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0 px-3">{{ $katalog->kode_motor }}</p>
                                </td>
                                <td>
                                    <p class="text-xs mb-0">{{ $katalog->nama_motor }}</p>
                                </td>
                                <td>
                                    <p class="text-xs mb-0">
                                        <span class="badge badge-sm bg-gradient-info">{{ ucfirst($katalog->kategori) }}</span>
                                    </p>
                                </td>
                                <td>
                                    <p class="text-xs mb-0">{{ $katalog->nama_file }}</p>
                                </td>
                                <td class="align-middle text-center">
                                    @if($katalog->is_active)
                                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                    @else
                                        <span class="badge badge-sm bg-gradient-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('katalog.view', $katalog->id) }}" 
                                       class="btn btn-link text-info mb-0 px-2"
                                       title="Lihat PDF"
                                       target="_blank">
                                        <i class="fa fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.katalog.edit', $katalog->id) }}" 
                                       class="btn btn-link text-secondary mb-0 px-2"
                                       title="Edit">
                                        <i class="fa fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.katalog.destroy', $katalog->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirmDelete(event, '{{ $katalog->kode_motor }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger mb-0 px-2" title="Hapus">
                                            <i class="fa fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-xs text-secondary mb-0">Belum ada katalog.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/plugins/datatables.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert.min.js') }}"></script>
<script>
@if(count($katalogs) > 0)
const dataTableSearch = new simpleDatatables.DataTable("#katalog-table", {
    searchable: true,
    fixedHeight: false,
    perPage: 20
});
@endif

function confirmDelete(event, kodeMotor) {
    event.preventDefault();
    
    Swal.fire({
        title: "Hapus Katalog?",
        text: "Apakah Anda yakin ingin menghapus katalog " + kodeMotor + "?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f5365c",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.submit();
        }
    });
    
    return false;
}
</script>
@endpush
@endsection
