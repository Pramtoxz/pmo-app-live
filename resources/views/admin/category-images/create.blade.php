@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0">Tambah Gambar Kelompok Part</h6>
                        <a href="{{ route('admin.category-images.index') }}" class="btn btn-secondary btn-sm ms-auto">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.category-images.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_kelompok" class="form-control-label">Kelompok Part <span class="text-danger">*</span></label>
                                    <select name="kode_kelompok" id="kode_kelompok" class="form-control @error('kode_kelompok') is-invalid @enderror" required>
                                        <option value="">-- Pilih Kelompok --</option>
                                        @foreach($availableCategories as $cat)
                                            <option value="{{ $cat->kd_detail_sub_kelompok_part }}" 
                                                    data-nama="{{ $cat->detail_sub_kelompok_part }}"
                                                    {{ old('kode_kelompok') == $cat->kd_detail_sub_kelompok_part ? 'selected' : '' }}>
                                                {{ $cat->kd_detail_sub_kelompok_part }} - {{ $cat->detail_sub_kelompok_part }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kode_kelompok')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_kelompok" class="form-control-label">Nama Kelompok <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_kelompok" id="nama_kelompok" 
                                           class="form-control @error('nama_kelompok') is-invalid @enderror" 
                                           value="{{ old('nama_kelompok') }}" 
                                           required readonly>
                                    @error('nama_kelompok')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="gambar" class="form-control-label">Gambar <span class="text-danger">*</span></label>
                                    <input type="file" name="gambar" id="gambar" 
                                           class="form-control @error('gambar') is-invalid @enderror" 
                                           accept="image/*" required>
                                    <small class="form-text text-muted">Format: JPG, PNG, GIF, WEBP. Max: 2MB</small>
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
                                              class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto fill nama kelompok
document.getElementById('kode_kelompok').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const namaKelompok = selectedOption.getAttribute('data-nama');
    document.getElementById('nama_kelompok').value = namaKelompok || '';
    document.getElementById('deskripsi').value = namaKelompok || '';
});

// Preview gambar
document.getElementById('gambar').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    preview.innerHTML = '';
    
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 300px;">';
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
@endpush
@endsection
