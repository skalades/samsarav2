{{-- Design system SAMSARA OPS: token warna, tipografi, mode tampilan (dipakai layout & login) --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#0B0D10">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="SAMSARA OPS">
<meta name="application-name" content="SAMSARA OPS">

{{-- PWA Manifest & App Icons --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/icons/apple-touch-icon.png') }}">
<link rel="icon" type="image/svg+xml" href="{{ asset('assets/icons/icon.svg') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/icons/icon-192.png') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

<script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
<script>
  (function () {
    // Mode tampilan: 'auto' (ikut lebar layar) atau 'mobile' (paksa versi mobile di layar apa pun)
    var mode = 'auto';
    @if($withViewMode ?? true)
    try { mode = localStorage.getItem('samsara_view_mode') || 'auto'; } catch (e) {}
    @endif
    window.SAMSARA_VIEW_MODE = mode;

    var config = {
      theme: {
        extend: {
          colors: {
            canvas: '#0B0D10',
            surface: '#12151A',
            raised: '#181C22',
            hover: '#1F242C',
            line: '#232932',
            'line-strong': '#2F3641',
            ink: '#ECEFF3',
            muted: '#A0A9B6',
            subtle: '#6E7887',
            brand: { DEFAULT: '#FF5722', hover: '#FF6A3D', soft: '#FF8A65' },
          },
          fontFamily: {
            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            display: ['"Space Grotesk"', 'Inter', 'sans-serif'],
            code: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
          },
        },
      },
    };

    if (mode === 'mobile') {
      document.documentElement.classList.add('force-mobile');
      // Matikan semua breakpoint agar seluruh halaman memakai tata letak mobile
      config.theme.screens = { sm: '99990px', md: '99991px', lg: '99992px', xl: '99993px', '2xl': '99994px' };
    }

    tailwind.config = config;
  })();
</script>

<style>
  html, body { background-color: #0B0D10; }
  @if($lockViewport ?? true)
  /* Shell aplikasi (backoffice): viewport terkunci, hanya area konten yang menggulir */
  html, body { height: 100%; height: 100dvh; overflow: hidden; }
  @endif
  body {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    color: #ECEFF3;
    -webkit-font-smoothing: antialiased;
    -webkit-tap-highlight-color: transparent;
  }
  .font-display { font-family: 'Space Grotesk', 'Inter', sans-serif; }
  .font-code { font-family: 'JetBrains Mono', ui-monospace, monospace; letter-spacing: -0.01em; }
  /* Gaya lama "terminal" kini memakai Inter + angka tabular agar lebih mudah dibaca */
  .font-mono-tech { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; font-variant-numeric: tabular-nums; }

  ::-webkit-scrollbar { width: 8px; height: 8px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #2F3641; border-radius: 8px; border: 2px solid #0B0D10; }
  ::-webkit-scrollbar-thumb:hover { background: #3B4350; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { scrollbar-width: none; }

  .safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
  .safe-top { padding-top: env(safe-area-inset-top); }

  input, select, textarea { color-scheme: dark; }
  .field { width: 100%; height: 2.75rem; border-radius: 0.75rem; background: #0B0D10; border: 1px solid #2F3641; padding: 0 0.875rem; font-size: 1rem; color: #ECEFF3; outline: none; transition: border-color .15s, box-shadow .15s; }
  @media (min-width: 1024px) { .field { font-size: 0.9375rem; } }
  textarea.field { height: auto; padding: 0.625rem 0.875rem; }
  .field:focus { border-color: #FF5722; box-shadow: 0 0 0 3px rgba(255, 87, 34, .15); }
  .field::placeholder { color: #6E7887; }
  .label { display: block; font-size: 0.75rem; font-weight: 600; color: #A0A9B6; margin-bottom: 0.375rem; }

  details > summary { list-style: none; }
  details > summary::-webkit-details-marker { display: none; }

  /* Mode mobile dipaksa di layar lebar: bingkai selebar ponsel di tengah */
  html.force-mobile, html.force-mobile body {
    background-color: #050607;
    height: 100%;
    height: 100dvh;
    overflow: hidden;
  }
  html.force-mobile #appShell {
    width: 100%;
    max-width: 440px;
    height: 100%;
    height: 100dvh;
    margin-left: auto;
    margin-right: auto;
    border-left: 1px solid #232932;
    border-right: 1px solid #232932;
    box-shadow: 0 0 80px rgba(0, 0, 0, .7);
  }
</style>

<script src="{{ asset('assets/js/currency-input.js') }}?v={{ filemtime(public_path('assets/js/currency-input.js')) }}"></script>
<script src="{{ asset('assets/js/pwa.js') }}?v={{ filemtime(public_path('assets/js/pwa.js')) }}" defer></script>
