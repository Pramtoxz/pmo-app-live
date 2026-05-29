@extends('layouts.app')

@section('page-title', 'Part Terlaris')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Kelola Part Terlaris</h6>
                    <button type="button" class="btn btn-primary btn-sm" onclick="generatePopularParts()">
                        <i class="fas fa-sync me-1"></i> Generate Part Terlaris
                    </button>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-4">
                    <table id="popular-parts-table" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Peringkat</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kode Part</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Part</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Qty Terjual</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total Order</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Generate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($popularParts as $item)
                            <tr>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">#{{ $item->peringkat }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $item->kode_part }}</p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $item->part ? $item->part->nm_part : '-' }}</p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ number_format($item->total_qty_terjual) }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <span class="badge badge-sm bg-gradient-success">{{ number_format($item->total_order) }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ $item->tanggal_generate ? $item->tanggal_generate->format('d M Y') : '-' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-xs text-secondary mb-0">Belum ada data part terlaris. Klik tombol "Generate Part Terlaris" untuk memulai.</p>
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
// Initialize DataTable
@if(count($popularParts) > 0)
const dataTableSearch = new simpleDatatables.DataTable("#popular-parts-table", {
    searchable: true,
    fixedHeight: false,
    perPage: 20
});
@endif

function generatePopularParts() {
    Swal.fire({
        title: "Generate Part Terlaris?",
        text: "Proses ini akan menghapus data lama dan generate data baru dari database.",
        icon: "warning", 
        showCancelButton: true,
        confirmButtonColor: "#2dce89", 
        cancelButtonColor: "#f5365c",  
        confirmButtonText: "Ya, Generate!",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Generating...",
                text: "Mohon tunggu, sedang generate data part terlaris",
                icon: "info",
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });
            
            fetch('{{ route('admin.popular-parts.generate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    limit: 100
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: "Berhasil!",
                        text: "Berhasil generate " + data.total + " part terlaris!",
                        icon: "success"
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: data.message,
                        icon: "error"
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: "Error!",
                    text: "Terjadi kesalahan: " + error.message,
                    icon: "error"
                });
            });
        }
    });
}
</script>
@endpush
@endsection
