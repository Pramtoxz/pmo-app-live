<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog {{ $katalog->kode_motor }} - {{ $katalog->nama_motor }}</title>
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
                        honda: { red: '#E31212', dark: '#111111' }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Montserrat', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .header {
            background: linear-gradient(135deg, #E31212 0%, #8B0000 100%);
            color: white;
            padding: 15px 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .pdf-container {
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            bottom: 0;
            background: #f8f9fa;
        }
        .pdf-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .pdf-container object {
            width: 100%;
            height: 100%;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 8px 16px;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.25);
            transform: translateX(-2px);
        }
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #E31212;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .loading {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: #666;
        }
        .loading i {
            font-size: 3rem;
            color: #E31212;
            margin-bottom: 1rem;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="javascript:history.back()" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Kembali</span>
                </a>
                <div>
                    <p class="text-xs opacity-90 uppercase tracking-wider font-semibold">Katalog</p>
                    <h1 class="text-base sm:text-lg font-black uppercase tracking-tight">{{ $katalog->kode_motor }} - {{ $katalog->nama_motor }}</h1>
                </div>
            </div>
            <a href="{{ route('katalog.download', $katalog->id) }}" class="btn-download">
                <i class="fas fa-download"></i>
                <span class="hidden sm:inline">Download</span>
            </a>
        </div>
    </div>

    <div class="pdf-container" id="pdfContainer">
        <div class="loading">
            <i class="fas fa-spinner"></i>
            <p class="text-sm font-semibold">Memuat katalog...</p>
        </div>
    </div>

    <script>
        const pdfUrl = "{{ asset($katalog->pdf_path) }}";
        const fullPdfUrl = "{{ url($katalog->pdf_path) }}";
        const container = document.getElementById('pdfContainer');
        
        function isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }
        
        function loadPDF() {
            if (isMobile()) {
                container.innerHTML = `<iframe src="https://docs.google.com/viewer?url=${encodeURIComponent(fullPdfUrl)}&embedded=true"></iframe>`;
            } else {
                container.innerHTML = `<object data="${pdfUrl}" type="application/pdf">
                    <iframe src="${pdfUrl}"></iframe>
                </object>`;
            }
        }
        
        loadPDF();
    </script>
</body>
</html>
