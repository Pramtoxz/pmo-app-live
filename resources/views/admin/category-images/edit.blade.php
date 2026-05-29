@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0">Edit Gambar Kelompok Part</h6>
                        <a href="{{ route('admin.category-images.index') }}" class="btn btn-secondary btn-sm ms-auto">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.category-images.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_kelompok" class="form-control-label">Kode Kelompok</label>
                                    <input type="text" class="form-control" value="{{ $category->kode_kelompok }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_kelompok" class="form-control-label">Nama Kelompok <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_kelompok" id="nama_kelompok" 
                                           class="form-control @error('nama_kelompok') is-invalid @enderror" 
                                           value="{{ old('nama_kelompok', $category->nama_kelompok) }}" 
                                           required>
                                    @error('nama_kelompok')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="gambar" class="form-control-label">Gambar</label>
                                    @if($category->gambar)
                                        <div class="mb-2">
                                            <img src="{{ url('images/category/' . $category->gambar) }}" 
                                                 alt="{{ $category->nama_kelompok }}" 
                                                 class="img-thumbnail cursor-pointer" 
                                                 style="max-width: 300px;"
                                                 onclick="showImageModal('{{ url('images/category/' . $category->gambar) }}', '{{ $category->nama_kelompok }}')">
                                        </div>
                                    @endif
                                    <input type="file" name="gambar" id="gambar" 
                                           class="form-control @error('gambar') is-invalid @enderror" 
                                           accept="image/*">
                                    <small class="form-text text-muted">Format: JPG, PNG, GIF, WEBP. Max: 2MB. Kosongkan jika tidak ingin mengubah gambar.</small>
                                    @error('gambar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="preview" class="mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="deskripsi" class="form-control-label">Deskripsi</label>
                                    <textarea name="deskripsi" id="deskripsi" rows="3" 
                                              class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $category->deskripsi) }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </div>
                    </form>
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
<script>
function showImageModal(imageUrl, imageName) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('modalImage').alt = imageName;
    document.getElementById('imageModalLabel').textContent = imageName;
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

// Preview gambar
document.getElementById('gambar').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    preview.innerHTML = '';
    
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail cursor-pointer" style="max-width: 300px;" onclick="showImageModal(\'' + e.target.result + '\', \'Preview\')">';
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
@endpush
@endsection
