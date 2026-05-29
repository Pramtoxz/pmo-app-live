@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Tambah Toko Baru</h6>
                        <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.shops.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kd_toko" class="form-control-label">Kode Toko <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kd_toko') is-invalid @enderror" 
                                           id="kd_toko" name="kd_toko" value="{{ old('kd_toko') }}" 
                                           placeholder="Contoh: T001" required maxlength="10">
                                    @error('kd_toko')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="toko" class="form-control-label">Nama Toko <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('toko') is-invalid @enderror" 
                                           id="toko" name="toko" value="{{ old('toko') }}" 
                                           placeholder="Nama Toko" required maxlength="255">
                                    @error('toko')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_telp" class="form-control-label">No. Telepon</label>
                                    <input type="text" class="form-control @error('no_telp') is-invalid @enderror" 
                                           id="no_telp" name="no_telp" value="{{ old('no_telp') }}" 
                                           placeholder="08123456789" maxlength="20">
                                    @error('no_telp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="npwp" class="form-control-label">NPWP</label>
                                    <input type="text" class="form-control @error('npwp') is-invalid @enderror" 
                                           id="npwp" name="npwp" value="{{ old('npwp') }}" 
                                           placeholder="00.000.000.0-000.000" maxlength="20">
                                    @error('npwp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="alamat" class="form-control-label">Alamat</label>
                                    <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                              id="alamat" name="alamat" rows="3" 
                                              placeholder="Alamat lengkap toko">{{ old('alamat') }}</textarea>
                                    @error('alamat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kategori" class="form-control-label">Kategori</label>
                                    <input type="text" class="form-control @error('kategori') is-invalid @enderror" 
                                           id="kategori" name="kategori" value="{{ old('kategori') }}" 
                                           placeholder="Kategori toko" maxlength="50">
                                    @error('kategori')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kd_ahm" class="form-control-label">Kode AHM</label>
                                    <input type="text" class="form-control @error('kd_ahm') is-invalid @enderror" 
                                           id="kd_ahm" name="kd_ahm" value="{{ old('kd_ahm') }}" 
                                           placeholder="Kode AHM" maxlength="10">
                                    @error('kd_ahm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-control-label">Status Toko</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="toko_active" 
                                               name="toko_active" {{ old('toko_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="toko_active">
                                            <strong>Toko Aktif</strong>
                                            <small class="text-muted d-block">Toko baru secara default aktif</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
