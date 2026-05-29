@extends('layouts.app')

@section('page-title', 'Edit Katalog')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Edit Katalog</h6>
                    <a href="{{ route('admin.katalog.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.katalog.update', $katalog->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Kode PTM</label>
                        <input type="text" class="form-control" value="{{ $katalog->kd_ptm }}" disabled>
                        <small class="text-muted">Kode PTM tidak dapat diubah</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File PDF Saat Ini</label>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-sm">{{ $katalog->nama_file_pdf }} ({{ $katalog->ukuran_file_mb }} MB)</span>
                            <a href="{{ route('katalog.view', $katalog->id) }}" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="file_pdf" class="form-label">Upload PDF Baru (Opsional)</label>
                        <input type="file" class="form-control" id="file_pdf" name="file_pdf" accept=".pdf">
                        <small class="text-muted">Format: PDF, Max: 10MB. Kosongkan jika tidak ingin mengubah file.</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ $katalog->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.katalog.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
