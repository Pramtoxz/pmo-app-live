@extends('layouts.app')

@section('page-title', 'Detail Sales/Supervisor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Detail: {{ $salesSpv->nama }}</h6>
                    <a href="{{ route('admin.sales-spv.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Kode NPK</label>
                            <p class="text-sm">{{ $salesSpv->kode_npk ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Nama</label>
                            <p class="text-sm">{{ $salesSpv->nama }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">No. HP</label>
                            <p class="text-sm">{{ $salesSpv->no_hp ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Email User</label>
                            <p class="text-sm">{{ $userEmail }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Jabatan</label>
                            <p class="text-sm">
                                <span class="badge badge-sm bg-gradient-{{ $salesSpv->jabatan == 'spv' ? 'primary' : 'info' }}">
                                    {{ strtoupper($salesSpv->jabatan) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Status</label>
                            <p class="text-sm">
                                @if($salesSpv->aktif)
                                    <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                @else
                                    <span class="badge badge-sm bg-gradient-secondary">Tidak Aktif</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <a href="{{ route('admin.sales-spv.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
