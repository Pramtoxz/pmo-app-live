<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <title>Login - {{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    
    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" crossorigin="anonymous" />
    
    <!-- Soft UI Dashboard CSS -->
    <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css?v=1.0.4') }}" rel="stylesheet" />
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body class="">
    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-75">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                            <div class="card card-plain mt-8">
                                <div class="card-header pb-0 text-left bg-transparent">
                                    <h3 class="font-weight-bolder text-info text-gradient">PMO Admin</h3>
                                    <p class="mb-0">Masukkan email dan password untuk login</p>
                                </div>
                                <div class="card-body">
                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <span class="alert-text text-white">
                                                {{ $errors->first() }}
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    <form role="form" method="POST" action="{{ route('login') }}">
                                        @csrf
                                        <label>Email</label>
                                        <div class="mb-3">
                                            <input type="email" name="email" class="form-control" placeholder="Email" aria-label="Email" value="{{ old('email') }}" required autofocus>
                                        </div>
                                        <label>Password</label>
                                        <div class="mb-3 position-relative">
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Password" aria-label="Password" required>
                                            <button type="button" id="togglePassword" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-secondary" style="z-index: 10; padding: 0.5rem 1rem;">
                                                <i class="fas fa-eye" id="eyeIcon"></i>
                                            </button>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                            <label class="form-check-label" for="rememberMe">Remember me</label>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Verifikasi: Berapa {{ session('captcha_question') }} ?</label>
                                            <input type="number" name="captcha" class="form-control" placeholder="Jawaban" autocomplete="off" required>
                                            @error('captcha')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('{{ asset('assets/img/curved-images/curved6.jpg') }}')"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Core JS Files -->
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/soft-ui-dashboard.min.js?v=1.0.4') }}"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    @endif
    
    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            confirmButtonText: 'OK'
        });
    </script>
    @endif

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>

</html>
