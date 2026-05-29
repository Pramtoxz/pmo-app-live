@extends('layouts.app')

@section('page-title', 'Sales & Supervisor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Data Sales & Supervisor</h6>
                    <div>
                        <button type="button" class="btn btn-success btn-sm me-2 text-white" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import me-1"></i> Import Excel
                        </button>
                        <a href="{{ route('admin.sales-spv.export') }}" class="btn btn-info btn-sm" id="exportBtn">
                            <i class="fas fa-file-export me-1"></i> Export Excel
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                @if(session('success'))
                    <div class="alert alert-success mx-4 mt-3">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger mx-4 mt-3">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive p-4">
                    <table id="sales-spv-table" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kode NPK</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jabatan</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. HP</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesSpv as $item)
                            <tr>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0 px-3">{{ $item->nama }}</p>
                                </td>
                                <td>
                                    <p class="text-xs mb-0">{{ $item->kode_npk }}</p>
                                </td>
                                <td>
                                    <span class="badge badge-sm {{ $item->jabatan == 'spv' ? 'bg-gradient-primary' : 'bg-gradient-info' }}">
                                        {{ strtoupper($item->jabatan) }}
                                    </span>
                                </td>
                                <td>
                                    <p class="text-xs mb-0">{{ $item->no_hp ?: '-' }}</p>
                                </td>
                                <td class="align-middle text-center">
                                    @if($item->aktif)
                                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                    @else
                                        <span class="badge badge-sm bg-gradient-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('admin.sales-spv.show', $item->id) }}" 
                                       class="btn btn-link text-info mb-0 px-2"
                                       title="Detail">
                                        <i class="fa fa-eye text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.sales-spv.destroy', $item->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirmDelete(event, '{{ $item->nama }}')">
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
                                    <p class="text-xs text-secondary mb-0">Belum ada data sales & supervisor.</p>
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

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Sales & Supervisor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.sales-spv.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small class="text-white">
                            <strong>Format Excel:</strong><br>
                            Kolom: nama, email, spv/salesman, nohp, kode<br>
                            - Download template dengan klik "Export Excel"<br>
                            - Import akan update data berdasarkan KODE<br>
                            - Jabatan: isi "spv" atau "salesman"
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="file">Pilih File Excel</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-upload"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/plugins/datatables.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert.min.js') }}"></script>
<script>
// Initialize DataTable
@if(count($salesSpv) > 0)
const dataTableSearch = new simpleDatatables.DataTable("#sales-spv-table", {
    searchable: true,
    fixedHeight: false,
    perPage: 20
});
@endif

// Konfirmasi Export
document.getElementById('exportBtn').addEventListener('click', function(e) {
    e.preventDefault();
    const exportUrl = this.href;
    
    Swal.fire({
        title: "Export Data Sales & Supervisor?",
        text: "File Excel akan didownload dengan data sales dan supervisor terbaru",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#17c1e8",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Export!",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = exportUrl;
        }
    });
});

function confirmDelete(event, name) {
    event.preventDefault();
    
    Swal.fire({
        title: "Hapus Data?",
        text: "Apakah Anda yakin ingin menghapus " + name + "?",
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
