@extends('layouts.app')

@section('page-title', 'Kampanye')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Data Kampanye</h6>
                    <a href="{{ route('admin.campaigns.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Tambah Kampanye
                    </a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                @if(session('success'))
                    <div class="alert alert-success mx-4 mt-3">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger mx-4 mt-3">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive p-4">
                    <table id="campaigns-table" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Gambar</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Judul</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Badge</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Periode</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $campaign)
                            <tr>
                                <td>
                                    <div class="px-3">
                                        @if($campaign->gambar)
                                            <img src="{{ asset('images/kampanye/' . $campaign->gambar) }}" 
                                                 alt="{{ $campaign->judul }}" 
                                                 class="img-thumbnail" 
                                                 style="width: 80px; height: 80px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                                 style="width: 80px; height: 80px;">
                                                <i class="fas fa-image fa-2x"></i>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $campaign->judul }}</p>
                                    <p class="text-xs text-secondary mb-0">{{ Str::limit($campaign->deskripsi, 50) }}</p>
                                </td>
                                <td>
                                    @if($campaign->badge)
                                        <span class="badge badge-sm bg-gradient-info">{{ $campaign->badge }}</span>
                                    @else
                                        <span class="text-xs text-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <p class="text-xs mb-0">{{ $campaign->tanggal_mulai->format('d M Y') }}</p>
                                    <p class="text-xs text-secondary mb-0">s/d {{ $campaign->tanggal_selesai->format('d M Y') }}</p>
                                </td>
                                <td class="align-middle text-center">
                                    @if($campaign->status == 'active')
                                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                    @elseif($campaign->status == 'inactive')
                                        <span class="badge badge-sm bg-gradient-secondary">Tidak Aktif</span>
                                    @else
                                        <span class="badge badge-sm bg-gradient-danger">Expired</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('admin.campaigns.show', $campaign->id) }}" 
                                       class="btn btn-link text-info mb-0 px-2"
                                       title="Detail">
                                        <i class="fa fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.campaigns.edit', $campaign->id) }}" 
                                       class="btn btn-link text-secondary mb-0 px-2"
                                       title="Edit">
                                        <i class="fa fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.campaigns.destroy', $campaign->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirmDelete(event, '{{ $campaign->judul }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger mb-0 px-2" title="Hapus">
                                            <i class="fa fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-xs text-secondary mb-0">Belum ada data kampanye.</p>
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

@push('scripts')
<script src="{{ asset('assets/js/plugins/datatables.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert.min.js') }}"></script>
<script>
@if(count($campaigns) > 0)
const dataTableSearch = new simpleDatatables.DataTable("#campaigns-table", {
    searchable: true,
    fixedHeight: false,
    perPage: 20
});
@endif

function confirmDelete(event, campaignName) {
    event.preventDefault();
    
    Swal.fire({
        title: "Hapus Kampanye?",
        text: "Apakah Anda yakin ingin menghapus kampanye " + campaignName + "?",
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
