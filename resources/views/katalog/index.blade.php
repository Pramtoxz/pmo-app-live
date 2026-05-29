<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Suku Cadang Motor Honda - PT. Menara Agung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Montserrat', 'sans-serif'],
                    },
                    colors: {
                        honda: { red: '#E31212', dark: '#111111', white: '#FFFFFF' }
                    }
                }
            }
        }
    </script>
    <style>
        body { -webkit-font-smoothing: antialiased; }
        .racing-stripe-top {
            clip-path: polygon(0 0, 100% 0, 0 100%);
        }
        .racing-stripe-bottom {
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
        }
        .category-card {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <main class="w-full bg-gray-100 min-h-screen relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-48 h-32 bg-honda-white racing-stripe-top opacity-90"></div>
        <div class="absolute top-0 left-4 w-48 h-32 bg-honda-white racing-stripe-top opacity-20"></div>

        <div class="container mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="pt-12 pb-6 relative z-10">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h2 class="text-[10px] font-bold tracking-widest text-gray-600 uppercase mb-1">PT. Menara Agung</h2>
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-honda-red leading-none tracking-tight uppercase">
                            Katalog<br>
                            <span class="text-honda-red">Suku Cadang</span>
                        </h1>
                    </div>
                    <div class="mt-4">
                        <img src="{{ asset('assets/images/lg_honda.jpg') }}" alt="Logo Honda" class="h-12 sm:h-16 object-contain">
                    </div>
                </div>

                <div class="inline-flex items-center gap-2 bg-white border border-gray-200 rounded-full px-4 py-2 mt-4 shadow-sm">
                    <svg class="w-4 h-4 text-honda-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="text-[10px] font-bold text-gray-700 uppercase tracking-wider">
                        Pilih Kategori Motor
                    </span>
                </div>
            </div>

            <div class="pb-24 space-y-4 relative z-10">

                <a href="{{ route('katalog.kategori', 'matic') }}" class="block">
                    <div class="category-card bg-white rounded-2xl p-5 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="bg-gradient-to-br from-honda-red to-red-700 p-4 rounded-xl flex-shrink-0">
                                    <i class="fas fa-motorcycle text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg uppercase tracking-wide text-honda-dark">Motor Matic</h3>
                                    <p class="text-xs text-gray-500 font-medium">{{ $maticCount }} Katalog Tersedia</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="hidden sm:inline-block text-xs font-bold text-honda-red uppercase">Lihat Katalog</span>
                                <i class="fas fa-chevron-right text-honda-red"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('katalog.kategori', 'cub') }}" class="block">
                    <div class="category-card bg-white rounded-2xl p-5 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="bg-gradient-to-br from-blue-600 to-blue-800 p-4 rounded-xl flex-shrink-0">
                                    <i class="fas fa-motorcycle text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg uppercase tracking-wide text-honda-dark">Motor Cub</h3>
                                    <p class="text-xs text-gray-500 font-medium">{{ $cubCount }} Katalog Tersedia</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="hidden sm:inline-block text-xs font-bold text-honda-red uppercase">Lihat Katalog</span>
                                <i class="fas fa-chevron-right text-honda-red"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('katalog.kategori', 'sport') }}" class="block">
                    <div class="category-card bg-white rounded-2xl p-5 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="bg-gradient-to-br from-orange-600 to-orange-800 p-4 rounded-xl flex-shrink-0">
                                    <i class="fas fa-motorcycle text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg uppercase tracking-wide text-honda-dark">Motor Sport</h3>
                                    <p class="text-xs text-gray-500 font-medium">{{ $sportCount }} Katalog Tersedia</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="hidden sm:inline-block text-xs font-bold text-honda-red uppercase">Lihat Katalog</span>
                                <i class="fas fa-chevron-right text-honda-red"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('katalog.kategori', 'electric') }}" class="block">
                    <div class="category-card bg-white rounded-2xl p-5 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="bg-gradient-to-br from-green-600 to-green-800 p-4 rounded-xl flex-shrink-0">
                                    <i class="fas fa-bolt text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg uppercase tracking-wide text-honda-dark">Motor Electric</h3>
                                    <p class="text-xs text-gray-500 font-medium">{{ $electricCount }} Katalog Tersedia</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="hidden sm:inline-block text-xs font-bold text-honda-red uppercase">Lihat Katalog</span>
                                <i class="fas fa-chevron-right text-honda-red"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <div class="bg-white rounded-2xl p-6 shadow-md border-l-4 border-honda-red mt-6">
                    <div class="mb-4">
                        <h3 class="font-bold text-sm uppercase tracking-wide text-honda-dark mb-2">Informasi</h3>
                        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed text-justify font-medium mb-3">
                            Katalog suku cadang motor Honda berisi informasi lengkap mengenai part number, nama part, dan ilustrasi untuk memudahkan Anda dalam mencari suku cadang yang dibutuhkan.
                        </p>
                        <ul class="space-y-2 text-xs sm:text-sm text-gray-600 font-medium">
                            <li class="flex items-start gap-2">
                                <span class="text-honda-red font-bold">•</span>
                                <span>Pilih kategori motor sesuai tipe kendaraan Anda</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-honda-red font-bold">•</span>
                                <span>Download PDF untuk melihat katalog secara offline</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-honda-red font-bold">•</span>
                                <span>Lihat online untuk akses cepat tanpa download</span>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="bg-red-50 p-3 rounded-full flex-shrink-0">
                                <i class="fas fa-mobile-alt text-honda-red text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="text-[10px] uppercase tracking-widest text-gray-500 font-bold">Powered By</h4>
                                <h4 class="text-[8px] uppercase tracking-widest text-gray-500 font-bold">Depart IT PT. Menara Agung</h4>
                                <p class="text-xl font-black text-honda-dark tracking-tight">Part Mobile Ordering Revolution</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-center gap-6 pt-4 border-t border-gray-200">
                            <img src="{{ asset('assets/images/lg_honda.jpg') }}" alt="Honda" class="h-12 object-contain">
                            <img src="{{ asset('assets/images/ma_horizontal.png') }}" alt="Menara Agung" class="h-8 object-contain">
                            <img src="{{ asset('assets/images/logo_pmo.png') }}" alt="PMO Logo" class="h-12 object-contain">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="absolute bottom-0 right-0 w-64 h-24 bg-honda-dark racing-stripe-bottom opacity-10"></div>
        <div class="absolute -bottom-4 -right-4 w-64 h-24 bg-honda-red racing-stripe-bottom opacity-90"></div>

    </main>

</body>
</html>
