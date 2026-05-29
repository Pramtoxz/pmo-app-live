@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Test Push Notification</h6>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#sendToUser">Kirim ke User</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#sendToShop">Kirim ke Toko</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#sendAnnouncement">Pengumuman (Semua)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#sendStock">Notifikasi Stok</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3">
                        <div id="sendToUser" class="tab-pane fade show active">
                            <form id="sendToUserForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Pilih User</label>
                                    <select class="form-select" name="user_id" required>
                                        <option value="">Loading...</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Judul <small class="text-muted">(max 100 karakter)</small></label>
                                    <input type="text" class="form-control" name="title" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pesan <small class="text-muted">(max 500 karakter)</small></label>
                                    <textarea class="form-control" name="message" rows="3" maxlength="500" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tipe</label>
                                    <select class="form-select" name="type">
                                        <option value="general">General</option>
                                        <option value="order">Order</option>
                                        <option value="campaign">Campaign</option>
                                        <option value="payment">Payment</option>
                                        <option value="delivery">Delivery</option>
                                        <option value="stock">Stock</option>
                                        <option value="announcement">Announcement</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Kirim Notifikasi</button>
                            </form>
                        </div>

                        <div id="sendToShop" class="tab-pane fade">
                            <form id="sendToShopForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Pilih Toko</label>
                                    <select class="form-select" name="kd_toko" required>
                                        <option value="">Loading...</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Judul <small class="text-muted">(max 100 karakter)</small></label>
                                    <input type="text" class="form-control" name="title" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pesan <small class="text-muted">(max 500 karakter)</small></label>
                                    <textarea class="form-control" name="message" rows="3" maxlength="500" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tipe</label>
                                    <select class="form-select" name="type">
                                        <option value="general">General</option>
                                        <option value="order">Order</option>
                                        <option value="campaign">Campaign</option>
                                        <option value="payment">Payment</option>
                                        <option value="delivery">Delivery</option>
                                        <option value="stock">Stock</option>
                                        <option value="announcement">Announcement</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Kirim Notifikasi</button>
                            </form>
                        </div>

                        <div id="sendAnnouncement" class="tab-pane fade">
                            <form id="sendAnnouncementForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Judul <small class="text-muted">(max 100 karakter)</small></label>
                                    <input type="text" class="form-control" name="title" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pesan <small class="text-muted">(max 500 karakter)</small></label>
                                    <textarea class="form-control" name="message" rows="3" maxlength="500" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning">Kirim ke Semua Toko</button>
                            </form>
                        </div>

                        <div id="sendStock" class="tab-pane fade">
                            <form id="sendStockForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Pilih Toko</label>
                                    <select class="form-select" name="kd_toko" required>
                                        <option value="">Loading...</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Part Number</label>
                                    <input type="text" class="form-control" name="part_number" maxlength="50" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Part</label>
                                    <input type="text" class="form-control" name="part_name" maxlength="200" required>
                                </div>
                                <button type="submit" class="btn btn-info">Kirim Notifikasi Stok</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    fetch('{{ route('admin.notifications.users-with-token') }}', {
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(response => response.json())
    .then(data => {
        const userSelect = document.querySelector('#sendToUserForm select[name="user_id"]');
        userSelect.innerHTML = '<option value="">Pilih User</option>';
        data.data.forEach(user => {
            const shopName = user.shop && user.shop.nama ? ` - ${user.shop.nama}` : ' - (Toko tidak terdaftar)';
            userSelect.innerHTML += `<option value="${user.id}">${user.name} (${user.email})${shopName}</option>`;
        });
    });

    fetch('{{ route('admin.notifications.shops') }}', {
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(response => response.json())
    .then(data => {
        const shopSelects = document.querySelectorAll('select[name="kd_toko"]');
        shopSelects.forEach(select => {
            select.innerHTML = '<option value="">Pilih Toko</option>';
            data.data.forEach(shop => {
                select.innerHTML += `<option value="${shop.kd_toko}">${shop.kd_toko} - ${shop.toko}</option>`;
            });
        });
    });

    document.getElementById('sendToUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendNotification('{{ route('admin.notifications.send-to-user') }}', new FormData(this), this);
    });

    document.getElementById('sendToShopForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendNotification('{{ route('admin.notifications.send-to-shop') }}', new FormData(this), this);
    });

    document.getElementById('sendAnnouncementForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendNotification('{{ route('admin.notifications.send-announcement') }}', new FormData(this), this);
    });

    document.getElementById('sendStockForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendNotification('{{ route('admin.notifications.send-stock') }}', new FormData(this), this);
    });

    function sendNotification(url, formData, form) {
        Swal.fire({
            title: 'Mengirim...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message });
                form.reset();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message });
            }
        })
        .catch(error => {
            Swal.fire({ icon: 'error', title: 'Error!', text: error.message });
        });
    }
});
</script>
@endpush
