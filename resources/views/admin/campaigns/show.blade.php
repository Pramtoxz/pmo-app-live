@extends('layouts.app')

@section('page-title', 'Detail Kampanye')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Detail Kampanye</h6>
                    <div>
                        <a href="{{ route('admin.campaigns.edit', $campaign->id) }}" class="btn btn-info btn-sm me-2">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.campaigns.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($campaign->gambar)
                            <img src="{{ asset('images/kampanye/' . $campaign->gambar) }}" 
                                 alt="{{ $campaign->judul }}" 
                                 class="img-fluid rounded">
                        @else
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded" 
                                 style="height: 300px;">
                                <i class="fas fa-image fa-5x"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="col-md-8">
                        <div class="mb-3">
                            <h5 class="mb-1">{{ $campaign->judul }}</h5>
                            @if($campaign->badge)
                                <span class="badge bg-gradient-info">{{ $campaign->badge }}</span>
                            @endif
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="text-sm mb-1"><strong>Status:</strong></p>
                                @if($campaign->status == 'active')
                                    <span class="badge bg-gradient-success">Aktif</span>
                                @elseif($campaign->status == 'inactive')
                                    <span class="badge bg-gradient-secondary">Tidak Aktif</span>
                                @else
                                    <span class="badge bg-gradient-danger">Expired</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p class="text-sm mb-1"><strong>Periode:</strong></p>
                                <p class="text-sm mb-0">{{ $campaign->tanggal_mulai->format('d M Y H:i') }}</p>
                                <p class="text-sm text-secondary">s/d {{ $campaign->tanggal_selesai->format('d M Y H:i') }}</p>
                            </div>
                        </div>

                        @if($campaign->deskripsi)
                        <div class="mb-3">
                            <p class="text-sm mb-1"><strong>Deskripsi Singkat:</strong></p>
                            <p class="text-sm">{{ $campaign->deskripsi }}</p>
                        </div>
                        @endif

                        @if($campaign->deskripsi_lengkap)
                        <div class="mb-3">
                            <p class="text-sm mb-1"><strong>Deskripsi Lengkap:</strong></p>
                            <p class="text-sm" style="white-space: pre-line;">{{ $campaign->deskripsi_lengkap }}</p>
                        </div>
                        @endif

                        @if($campaign->syarat_ketentuan)
                        <div class="mb-3">
                            <p class="text-sm mb-1"><strong>Syarat & Ketentuan:</strong></p>
                            <p class="text-sm" style="white-space: pre-line;">{{ $campaign->syarat_ketentuan }}</p>
                        </div>
                        @endif

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <p class="text-xs text-secondary mb-0">Dibuat: {{ $campaign->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-xs text-secondary mb-0">Diupdate: {{ $campaign->updated_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
