<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Motor {{ $kategoriName }} - PT. Menara Agung</title>
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
        .katalog-card {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .katalog-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(204,0,0,0.15);
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
                        <a href="{{ route('katalog.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-honda-red transition-colors mb-2">
                            <i class="fas fa-arrow-left text-sm"></i>
                            <span class="text-[10px] font-bold tracking-widest uppercase">Kembali</span>
                        </a>
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-honda-red leading-none tracking-tight uppercase">
                            Motor<br>
                            <span class="text-honda-red">{{ $kategoriName }}</span>
                        </h1>
                    </div>
                    <div class="mt-4">
                        <img src="{{ asset('assets/images/lg_honda.jpg') }}" alt="Logo Honda" class="h-12 sm:h-16 object-contain">
                    </div>
                </div>

                <div class="inline-flex items-center gap-2 bg-white border border-gray-200 rounded-full px-4 py-2 mt-4 shadow-sm">
                    <svg class="w-4 h-4 text-honda-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="searchInput" placeholder="Cari motor..." class="text-xs font-medium border-none outline-none bg-transparent w-full">
                </div>
            </div>

            <div class="pb-24 space-y-4 relative z-10">
                @forelse($katalogs as $katalog)
                    <div class="katalog-card bg-white rounded-2xl p-5 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100" 
                         data-search="{{ strtolower($katalog->kode_motor . ' ' . $katalog->nama_motor . ' ' . $katalog->tahun_motor . ' ' . $katalog->no_rangka) }}">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="bg-gradient-to-br from-honda-red to-red-700 p-3 rounded-xl flex-shrink-0">
                                <i class="fas fa-file-pdf text-white text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-base uppercase tracking-wide text-honda-dark">{{ $katalog->kode_motor }}</h3>
                                <p class="text-sm text-gray-600 font-medium">{{ $katalog->nama_motor }}</p>
                            </div>
                        </div>

                        @if($katalog->tahun_motor_array && count($katalog->tahun_motor_array) > 0)
                            <div class="mb-3 pb-3 border-b border-gray-100">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Tahun Produksi</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($katalog->tahun_motor_array as $tahun)
                                        <span class="inline-block bg-gray-50 border border-gray-200 px-3 py-1 rounded-lg text-xs font-semibold text-gray-700">{{ $tahun }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($katalog->no_rangka_array && count($katalog->no_rangka_array) > 0)
                            <div class="mb-4 pb-3 border-b border-gray-100">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Nomor Rangka</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($katalog->no_rangka_array as $rangka)
                                        <span class="inline-block bg-gray-50 border border-gray-200 px-3 py-1 rounded-lg text-xs font-mono font-semibold text-gray-700">{{ $rangka }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-3">
                            <a href="{{ route('katalog.download', $katalog->id) }}" 
                               class="flex-1 bg-gradient-to-r from-honda-red to-red-700 text-white px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wide text-center hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-download"></i>
                                <span>Download PDF</span>
                            </a>
                            <a href="{{ route('katalog.view', $katalog->id) }}" 
                               class="flex-1 bg-gray-800 text-white px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wide text-center hover:bg-gray-900 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-eye"></i>
                                <span>Lihat Online</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl p-12 text-center shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100">
                        <div class="text-gray-300 mb-4">
                            <i class="fas fa-inbox text-6xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">Belum ada katalog untuk kategori ini</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="absolute bottom-0 right-0 w-64 h-24 bg-honda-dark racing-stripe-bottom opacity-10"></div>
        <div class="absolute -bottom-4 -right-4 w-64 h-24 bg-honda-red racing-stripe-bottom opacity-90"></div>

    </main>

    <script>
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.katalog-card');
            
            cards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>

</body>
</html>
