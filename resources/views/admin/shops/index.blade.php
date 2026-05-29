@extends('layouts.app')

@section('page-title', 'Data Toko')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Data Toko</h6>
                        @if($lastRefresh)
                            <p class="text-xs text-secondary mb-0">
                                <i class="fas fa-sync text-success"></i>
                                Cache terakhir diperbarui: {{ \Carbon\Carbon::parse($lastRefresh->last_refresh_at)->format('d M Y, H:i') }}
                                ({{ \Carbon\Carbon::parse($lastRefresh->last_refresh_at)->diffForHumans() }})
                            </p>
                        @else
                            <p class="text-xs text-secondary mb-0">
                                <i class="fas fa-info-circle"></i> 
                                Cache belum pernah diperbarui
                            </p>
                        @endif
                    </div>
                    <div>
                        <form action="{{ route('admin.collection.refresh-cache') }}" method="POST" class="d-inline" onsubmit="return confirmRefreshCache(event)">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm me-2">
                                <i class="fas fa-sync me-1"></i> Refresh Collection Cache
                            </button>
                        </form>
                        <button type="button" class="btn btn-success btn-sm me-2 text-white" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import me-1"></i> Import Excel
                        </button>
                        <a href="{{ route('admin.shops.export') }}" class="btn btn-info btn-sm me-2" id="exportBtn">
                            <i class="fas fa-file-export me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.shops.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Tambah Toko
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
                @if(session('info'))
                    <div class="alert alert-info mx-4 mt-3">
                        {{ session('info') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger mx-4 mt-3">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive p-4">
                    <table id="shops-table" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kode Toko</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Toko</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. Telp</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shops as $shop)
                            <tr>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0 px-3">{{ $shop->kd_toko }}</p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $shop->toko }}</p>
                                </td>
                                <td>
                                    <p class="text-xs mb-0">{{ $shop->no_telp ?: '-' }}</p>
                                </td>
                                <td class="align-middle text-center">
                                    @if($shop->toko_active)
                                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                    @else
                                        <span class="badge badge-sm bg-gradient-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('admin.shops.show', $shop->kd_toko) }}" 
                                       class="btn btn-link text-info mb-0 px-2"
                                       title="Detail">
                                        <i class="fa fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.shops.edit', $shop->kd_toko) }}" 
                                       class="btn btn-link text-secondary mb-0 px-2"
                                       title="Edit">
                                        <i class="fa fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.shops.reset-pin', $shop->kd_toko) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirmResetPin(event, '{{ $shop->toko }}')">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-warning mb-0 px-2" title="Reset PIN Collection">
                                            <i class="fa fa-key text-xs"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.shops.destroy', $shop->kd_toko) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirmDelete(event, '{{ $shop->toko }}')">
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
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-xs text-secondary mb-0">Belum ada data toko.</p>
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
                <h5 class="modal-title" id="importModalLabel">Import Data Toko & Sales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.shops.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small class="text-white">
                            <strong>Format Excel (2 Sheet):</strong><br>
                            <strong>Sheet 1 - Toko:</strong> NAMA TOKO, SALESMAN, spv, KODE, email, nohp, STATUS<br>
                            <strong>Sheet 2 - Sales & Supervisor:</strong> nama, email, spv/salesman, nohp, kode<br>
                            - Download template dengan klik "Export Excel"<br>
                            - STATUS: AKTIF atau NONAKTIF (default: AKTIF jika kosong)<br>
                            - Import akan update data berdasarkan KODE
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
@if(count($shops) > 0)
const dataTableSearch = new simpleDatatables.DataTable("#shops-table", {
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
        title: "Export Data Toko?",
        text: "File Excel akan didownload dengan data toko dan sales/supervisor terbaru",
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

function confirmDelete(event, shopName) {
    event.preventDefault();
    
    Swal.fire({
        title: "Hapus Toko?",
        text: "Apakah Anda yakin ingin menghapus toko " + shopName + "?",
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

function confirmResetPin(event, shopName) {
    event.preventDefault();
    
    Swal.fire({
        title: "Reset PIN Collection?",
        html: "PIN Collection untuk toko <strong>" + shopName + "</strong> akan direset.<br><br>User toko dapat setup PIN baru seperti pertama kali.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#fb6340",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Reset PIN!",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.submit();
        }
    });
    
    return false;
}

function confirmRefreshCache(event) {
    event.preventDefault();
    
    Swal.fire({
        title: "Refresh Collection Cache?",
        html: "Proses akan berjalan di <strong>background</strong> untuk semua toko aktif.<br><br>" +
              "• Proses membutuhkan waktu beberapa menit<br>" +
              "• Halaman tidak akan freeze/hang<br>" +
              "• Status akan diperbarui di dashboard<br>" +
              "• Tidak membebani server",
        icon: "info",
        showCancelButton: true,
        confirmButtonColor: "#fb6340",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Refresh!",
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
