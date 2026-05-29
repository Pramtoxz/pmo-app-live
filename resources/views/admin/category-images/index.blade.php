@extends('layouts.app')

@section('page-title', 'Gambar Kelompok Part')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Gambar Kelompok Part</h6>
                    <a href="{{ route('admin.category-images.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Tambah Gambar
                    </a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                @if(session('success'))
                    <div class="alert alert-success mx-4 mt-3">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive p-4">
                    <table id="category-images-table" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kode</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Kelompok</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Gambar</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Deskripsi</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0 px-3">{{ $category->kode_kelompok }}</p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $category->nama_kelompok }}</p>
                                </td>
                                <td>
                                    @if($category->gambar)
                                        <img src="{{ url('images/category/' . $category->gambar) }}" 
                                             alt="{{ $category->nama_kelompok }}" 
                                             class="img-thumbnail cursor-pointer" 
                                             style="width: 80px; height: 80px; object-fit: cover;"
                                             onclick="showImageModal('{{ url('images/category/' . $category->gambar) }}', '{{ $category->nama_kelompok }}')">
                                    @else
                                        <span class="badge badge-sm bg-secondary">Belum ada gambar</span>
                                    @endif
                                </td>
                                <td>
                                    <p class="text-xs mb-0">{{ Str::limit($category->deskripsi, 50) }}</p>
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('admin.category-images.edit', $category->id) }}" 
                                       class="btn btn-link text-secondary mb-0 px-2">
                                        <i class="fa fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.category-images.destroy', $category->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirmDelete(event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger mb-0 px-2">
                                            <i class="fa fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-xs text-secondary mb-0">Belum ada data. Klik tombol "Tambah Gambar" untuk memulai.</p>
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

<!-- Modal untuk menampilkan gambar -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Preview Gambar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/plugins/datatables.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert.min.js') }}"></script>
<script>
// Initialize DataTable
@if(count($categories) > 0)
const dataTableSearch = new simpleDatatables.DataTable("#category-images-table", {
    searchable: true,
    fixedHeight: false,
    perPage: 20
});
@endif

function showImageModal(imageUrl, imageName) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('modalImage').alt = imageName;
    document.getElementById('imageModalLabel').textContent = imageName;
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

function confirmDelete(event) {
    event.preventDefault();
    
    Swal.fire({
        title: "Hapus Gambar?",
        text: "Data yang dihapus tidak dapat dikembalikan!",
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
