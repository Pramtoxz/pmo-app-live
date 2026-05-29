@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Tambah Katalog Kendaraan</h6>
                    <a href="{{ route('admin.katalog.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.katalog.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_motor">Kode Motor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kode_motor') is-invalid @enderror" 
                                           id="kode_motor" name="kode_motor" value="{{ old('kode_motor') }}" required>
                                    @error('kode_motor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_motor">Nama Motor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_motor') is-invalid @enderror" 
                                           id="nama_motor" name="nama_motor" value="{{ old('nama_motor') }}" required>
                                    @error('nama_motor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_motor">Tahun Motor</label>
                                    <textarea class="form-control @error('tahun_motor') is-invalid @enderror" 
                                              id="tahun_motor" name="tahun_motor" rows="3" 
                                              placeholder="Contoh: 1984, 1985, 1986, 1987">{{ old('tahun_motor') }}</textarea>
                                    <small class="text-muted">Pisahkan dengan koma</small>
                                    @error('tahun_motor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_rangka">Nomor Rangka</label>
                                    <textarea class="form-control @error('no_rangka') is-invalid @enderror" 
                                              id="no_rangka" name="no_rangka" rows="3" 
                                              placeholder="Contoh: HB3*E / HB4*E atau - jika tidak ada">{{ old('no_rangka') }}</textarea>
                                    <small class="text-muted">Gunakan - jika tidak ada</small>
                                    @error('no_rangka')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kategori">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-control @error('kategori') is-invalid @enderror" 
                                            id="kategori" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="M" {{ old('kategori') == 'M' ? 'selected' : '' }}>Matic</option>
                                        <option value="C" {{ old('kategori') == 'C' ? 'selected' : '' }}>Cub</option>
                                        <option value="S" {{ old('kategori') == 'S' ? 'selected' : '' }}>Sport</option>
                                        <option value="A" {{ old('kategori') == 'A' ? 'selected' : '' }}>Electric</option>
                                    </select>
                                    @error('kategori')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file_pdf">File PDF <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('file_pdf') is-invalid @enderror" 
                                           id="file_pdf" name="file_pdf" accept=".pdf" required>
                                    <small class="text-muted">Max 10MB</small>
                                    @error('file_pdf')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.katalog.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
