<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-white" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('admin.dashboard') }}">
             <img src="{{ asset('assets/images/lg_honda.jpg') }}" alt="Logo Honda" class="h-16 md:h-12 object-contain">
                 <span class="ms-1 font-weight-bold">PT. Menara Agung</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Part Management</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.popular-parts.*') ? 'active' : '' }}" href="{{ route('admin.popular-parts.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-chart-bar-32 text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Part Terlaris</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.category-images.*') ? 'active' : '' }}" href="{{ route('admin.category-images.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-image text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Gambar Kelompok</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.katalog.*') ? 'active' : '' }}" href="{{ route('admin.katalog.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-book-bookmark text-danger text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Katalog Kendaraan</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Dealer Management</h6>
            </li>
           <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.shops.*') ? 'active' : '' }}" href="{{ route('admin.shops.index') }}">
        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-shop text-info text-sm opacity-10"></i>
        </div>
        <span class="nav-link-text ms-1">Data Toko</span>
    </a>
</li>
            <li class="nav-item">
               <a class="nav-link {{ request()->routeIs('admin.sales-spv.*') ? 'active' : '' }}" href="{{ route('admin.sales-spv.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Sales & Supervisor</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Campaign</h6>
            </li>
          <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }}" href="{{ route('admin.campaigns.index') }}">
        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-calendar-grid-58 text-danger text-sm opacity-10"></i>
        </div>
        <span class="nav-link-text ms-1">Kampanye</span>
    </a>
</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-bell-55 text-info text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Notifikasi</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
