@extends('layouts.app')

@section('page-title', 'Edit Kampanye')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Edit Kampanye</h6>
                    <a href="{{ route('admin.campaigns.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('admin.campaigns.update', $campaign->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="judul">Judul Kampanye <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                       id="judul" name="judul" value="{{ old('judul', $campaign->judul) }}" required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="badge">Badge</label>
                                <input type="text" class="form-control @error('badge') is-invalid @enderror" 
                                       id="badge" name="badge" value="{{ old('badge', $campaign->badge) }}" 
                                       placeholder="Contoh: PROMO, NEW, HOT">
                                @error('badge')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="deskripsi">Deskripsi Singkat</label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                          id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $campaign->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="deskripsi_lengkap">Deskripsi Lengkap</label>
                                <textarea class="form-control @error('deskripsi_lengkap') is-invalid @enderror" 
                                          id="deskripsi_lengkap" name="deskripsi_lengkap" rows="5">{{ old('deskripsi_lengkap', $campaign->deskripsi_lengkap) }}</textarea>
                                @error('deskripsi_lengkap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="syarat_ketentuan">Syarat & Ketentuan</label>
                                <textarea class="form-control @error('syarat_ketentuan') is-invalid @enderror" 
                                          id="syarat_ketentuan" name="syarat_ketentuan" rows="5">{{ old('syarat_ketentuan', $campaign->syarat_ketentuan) }}</textarea>
                                @error('syarat_ketentuan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="gambar">Gambar Kampanye</label>
                                
                                @if($campaign->gambar)
                                    <div class="mb-2">
                                        <img src="{{ asset('images/kampanye/' . $campaign->gambar) }}" 
                                             alt="{{ $campaign->judul }}" 
                                             class="img-thumbnail" 
                                             style="max-width: 100%;">
                                        <p class="text-xs text-secondary mt-1">Gambar saat ini</p>
                                    </div>
                                @endif
                                
                                <input type="file" class="form-control @error('gambar') is-invalid @enderror" 
                                       id="gambar" name="gambar" accept="image/*" onchange="previewImage(event)">
                                @error('gambar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: JPG, PNG, GIF. Max: 2MB. Kosongkan jika tidak ingin mengubah gambar.</small>
                                
                                <div class="mt-3" id="imagePreview" style="display: none;">
                                    <img id="preview" src="" alt="Preview" class="img-thumbnail" style="max-width: 100%;">
                                    <p class="text-xs text-info mt-1">Preview gambar baru</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                       id="tanggal_mulai" name="tanggal_mulai" 
                                       value="{{ old('tanggal_mulai', $campaign->tanggal_mulai->format('Y-m-d\TH:i')) }}" required>
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                       id="tanggal_selesai" name="tanggal_selesai" 
                                       value="{{ old('tanggal_selesai', $campaign->tanggal_selesai->format('Y-m-d\TH:i')) }}" required>
                                @error('tanggal_selesai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="active" {{ old('status', $campaign->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ old('status', $campaign->status) == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                    <option value="expired" {{ old('status', $campaign->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Kampanye
                            </button>
                            <a href="{{ route('admin.campaigns.index') }}" class="btn btn-secondary">
                                Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endpush
@endsection
