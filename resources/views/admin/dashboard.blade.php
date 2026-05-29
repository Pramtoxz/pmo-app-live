@extends('layouts.app')

@section('page-title', 'Dashboard')

@push('styles')
<style>
    .dashboard-container {
        position: relative;
        min-height: 600px;
    }
    .globe-container {
        position: fixed;
        top: 80px;
        right: 0;
        width: 55%;
        height: calc(100vh - 80px);
        overflow: hidden;
        pointer-events: none;
        z-index: 0;
    }
    .globe-container canvas {
        width: 100% !important;
        height: 100% !important;
    }
    .content-wrapper {
        position: relative;
        z-index: 1;
    }
    .content-wrapper .card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
    }
</style>
@endpush

@push('scripts')
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 2000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
</script>
@endif

<!-- Three.js Globe Scripts -->
<script src="{{ asset('assets/js/plugins/threejs.js') }}"></script>
<script src="{{ asset('assets/js/plugins/orbit-controls.js') }}"></script>
<script>
(function() {
    const container = document.getElementById("globe");
    if (!container) return;

    const canvas = container.getElementsByTagName("canvas")[0];
    const globeRadius = 100;
    const globeWidth = 4098 / 2;
    const globeHeight = 1968 / 2;

    function convertFlatCoordsToSphereCoords(x, y) {
        let latitude = ((x - globeWidth) / globeWidth) * -180;
        let longitude = ((y - globeHeight) / globeHeight) * -90;
        latitude = (latitude * Math.PI) / 180;
        longitude = (longitude * Math.PI) / 180;
        const radius = Math.cos(longitude) * globeRadius;

        return {
            x: Math.cos(latitude) * radius,
            y: Math.sin(longitude) * globeRadius,
            z: Math.sin(latitude) * radius
        };
    }

    function makeMagic(points) {
        const { width, height } = container.getBoundingClientRect();
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(45, width / height);
        const renderer = new THREE.WebGLRenderer({
            canvas,
            antialias: true,
            alpha: true
        });
        renderer.setSize(width, height);

        const mergedGeometry = new THREE.Geometry();
        const pointGeometry = new THREE.SphereGeometry(0.5, 1, 1);
        const pointMaterial = new THREE.MeshBasicMaterial({
            color: "#989db5",
        });

        for (let point of points) {
            const { x, y, z } = convertFlatCoordsToSphereCoords(point.x, point.y);
            if (x && y && z) {
                pointGeometry.translate(x, y, z);
                mergedGeometry.merge(pointGeometry);
                pointGeometry.translate(-x, -y, -z);
            }
        }

        const globeShape = new THREE.Mesh(mergedGeometry, pointMaterial);
        scene.add(globeShape);

        camera.orbitControls = new THREE.OrbitControls(camera, canvas);
        camera.orbitControls.enableKeys = false;
        camera.orbitControls.enablePan = false;
        camera.orbitControls.enableZoom = false;
        camera.orbitControls.enableDamping = false;
        camera.orbitControls.enableRotate = true;
        camera.orbitControls.autoRotate = true;
        camera.position.z = -265;

        function animate() {
            camera.orbitControls.update();
            requestAnimationFrame(animate);
            renderer.render(scene, camera);
        }
        animate();
    }

    function hasWebGL() {
        const gl = canvas.getContext("webgl") || canvas.getContext("experimental-webgl");
        return gl && gl instanceof WebGLRenderingContext;
    }

    function init() {
        if (hasWebGL()) {
            fetch("https://raw.githubusercontent.com/creativetimofficial/public-assets/master/soft-ui-dashboard-pro/assets/js/points.json")
                .then(response => response.json())
                .then(data => {
                    makeMagic(data.points);
                });
        }
    }
    init();
})();
</script>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Globe Background -->
    <div class="globe-container" id="globe">
        <canvas></canvas>
    </div>

    <div class="content-wrapper">
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Toko</p>
                                    <h5 class="font-weight-bolder mb-0">
                                        {{ number_format($totalShops) }}
                                    </h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                    <i class="ni ni-shop text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Part</p>
                                    <h5 class="font-weight-bolder mb-0">
                                        {{ number_format($totalParts) }}
                                    </h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                    <i class="ni ni-box-2 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Part Terlaris</p>
                                    <h5 class="font-weight-bolder mb-0">
                                        {{ number_format($popularParts) }}
                                    </h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                    <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Kampanye Aktif</p>
                                    <h5 class="font-weight-bolder mb-0">
                                        {{ number_format($activeCampaigns) }}
                                    </h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                    <i class="ni ni-calendar-grid-58 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Selamat Datang di Admin Panel PMO</h6>
                        <p class="text-sm">
                            Kelola data part, toko, kampanye, dan notifikasi dari sini.
                        </p>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="d-flex flex-column h-100">
                                    <h6 class="font-weight-bolder">Quick Actions</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                            <div class="d-flex align-items-center">
                                                <button class="btn btn-icon-only btn-rounded btn-outline-success mb-0 me-3 btn-sm d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                                <div class="d-flex flex-column">
                                                    <h6 class="mb-1 text-dark text-sm">Generate Part Terlaris</h6>
                                                    <span class="text-xs">Update data part terlaris dari database</span>
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <a href="{{ route('admin.popular-parts.index') }}" class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                                    <i class="ni ni-bold-right" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </li>
                                    </ul>
                                    <h6 class="font-weight-bolder mt-3">System Info</h6>
                                    <p class="text-sm mb-0">
                                        <i class="ni ni-check-bold text-success"></i> Database: Connected<br>
                                        <i class="ni ni-check-bold text-success"></i> Cache: Active<br>
                                        <i class="ni ni-check-bold text-success"></i> Last Update: {{ now()->format('d M Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Collection Cache Status</h6>
                        <p class="text-sm mb-0">Status refresh data collection</p>
                    </div>
                    <div class="card-body p-3">
                        @if($isRefreshing)
                            <div class="text-center py-3">
                                <i class="fas fa-spinner fa-spin text-warning fa-2x mb-2"></i>
                                <p class="text-sm text-warning font-weight-bold mb-0">Sedang memperbarui cache...</p>
                                <p class="text-xs text-secondary">Proses berjalan di background. Refresh halaman beberapa menit lagi.</p>
                            </div>
                        @elseif($lastRefresh)
                            <div class="d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge badge-sm bg-gradient-success">
                                        <i class="fas fa-check"></i> Success
                                    </span>
                                </div>
                                
                                <div class="mb-2">
                                    <p class="text-xs text-secondary mb-0">Terakhir Diperbarui</p>
                                    <h6 class="text-sm font-weight-bold mb-0">
                                        {{ \Carbon\Carbon::parse($lastRefresh->last_refresh_at)->format('d M Y, H:i') }}
                                    </h6>
                                    <span class="text-xs text-secondary">
                                        ({{ \Carbon\Carbon::parse($lastRefresh->last_refresh_at)->diffForHumans() }})
                                    </span>
                                </div>

                                <hr class="horizontal dark my-2">

                                <div class="row">
                                    <div class="col-6">
                                        <p class="text-xs text-secondary mb-0">Toko Diproses</p>
                                        <h6 class="text-sm font-weight-bold mb-0">{{ number_format($lastRefresh->total_shops_processed) }}</h6>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-xs text-secondary mb-0">Total Data</p>
                                        <h6 class="text-sm font-weight-bold mb-0">{{ number_format($lastRefresh->total_records) }}</h6>
                                    </div>
                                </div>

                                <hr class="horizontal dark my-2">

                                <div class="mb-0">
                                    <p class="text-xs text-secondary mb-0">Durasi Proses</p>
                                    <h6 class="text-sm font-weight-bold mb-0">
                                        @php
                                            $minutes = floor($lastRefresh->duration_seconds / 60);
                                            $seconds = $lastRefresh->duration_seconds % 60;
                                        @endphp
                                        @if($minutes > 0)
                                            {{ $minutes }} menit {{ $seconds }} detik
                                        @else
                                            {{ $seconds }} detik
                                        @endif
                                    </h6>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle text-secondary fa-3x mb-3"></i>
                                <p class="text-sm text-secondary mb-0">Belum ada data refresh</p>
                                <p class="text-xs text-secondary">Jalan otomatis jam 03:00 atau refresh manual di halaman Toko.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
