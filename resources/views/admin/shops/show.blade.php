@extends('layouts.app')

@section('page-title', 'Detail Toko')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Detail Toko: {{ $shop->toko }}</h6>
                    <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info">
                        {{ session('info') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Kode Toko</label>
                            <p class="text-sm">{{ $shop->kd_toko }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Nama Toko</label>
                            <p class="text-sm">{{ $shop->toko }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">No. Telepon</label>
                            <p class="text-sm">{{ $shop->no_telp ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">NPWP</label>
                            <p class="text-sm">{{ $shop->npwp ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Alamat</label>
                            <p class="text-sm">{{ $shop->alamat ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Kategori</label>
                            <p class="text-sm">{{ $shop->kategori ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Kode AHM</label>
                            <p class="text-sm">{{ $shop->kd_ahm ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Status</label>
                            <p class="text-sm">
                                @if($shop->toko_active)
                                    <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                @else
                                    <span class="badge badge-sm bg-gradient-secondary">Tidak Aktif</span>
                                @endif
                            </p>
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
                            <label class="form-label text-xs font-weight-bold text-uppercase">PIN Collection</label>
                            <p class="text-sm">
                                @php
                                    $user = DB::connection('pgsql')->table('pmov2.users')
                                        ->where('fk_toko', $shop->kd_toko)
                                        ->where('role', 'dealer')
                                        ->first();
                                    $hasPin = $user && !empty($user->collection_pin);
                                @endphp
                                @if($hasPin)
                                    <span class="badge badge-sm bg-gradient-success">Sudah Setup</span>
                                @else
                                    <span class="badge badge-sm bg-gradient-secondary">Belum Setup</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <a href="{{ route('admin.shops.edit', $shop->kd_toko) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Toko
                        </a>
                        <form action="{{ route('admin.shops.reset-pin', $shop->kd_toko) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirmResetPin(event)">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fas fa-key"></i> Reset PIN Collection
                            </button>
                        </form>
                        <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/plugins/sweetalert.min.js') }}"></script>
<script>
function confirmResetPin(event) {
    event.preventDefault();
    
    Swal.fire({
        title: "Reset PIN Collection?",
        html: "PIN Collection untuk toko ini akan direset.<br><br>User toko dapat setup PIN baru seperti pertama kali.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#fb6340",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Reset PIN!",
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
