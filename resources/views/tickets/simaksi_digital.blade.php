<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Tiket Digital SIMAKSI - {{ $registration->registration_code }} — {{ $profile['expedition_name'] ?? 'SAMSARA' }}</title>
    
    <!-- Tailwind CSS Framework CDN -->
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>

    <!-- Google Fonts: Space Grotesk + JetBrains Mono + Inter -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --font-display: 'Space Grotesk', -apple-system, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --font-body: 'Inter', -apple-system, sans-serif;
        }

        body {
            font-family: var(--font-body);
            background-color: #0A0B0D;
            color: #E2E8F0;
            -webkit-font-smoothing: antialiased;
        }

        .font-display { font-family: var(--font-display); }
        .font-mono-tech { font-family: var(--font-mono); }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .ticket-card {
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
                background: white !important;
                color: black !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-[#0A0B0D] text-stone-200 min-h-screen py-8 px-4 flex flex-col items-center justify-start selection:bg-[#FF5722] selection:text-black">

    <!-- Top Action Toolbar (No Print) -->
    <div class="no-print w-full max-w-2xl mb-6 flex items-center justify-between gap-3">
        <a href="{{ route('portal.home') }}" class="inline-flex items-center gap-2 text-xs font-mono-tech font-bold text-stone-300 hover:text-white bg-[#16191F] hover:bg-[#1E232D] px-4 py-2.5 rounded-xl border border-[#2A303C] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            ← Kembali ke Portal
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 text-xs font-mono-tech font-bold text-stone-300 hover:text-white bg-[#16191F] hover:bg-[#1E232D] px-3.5 py-2.5 rounded-xl border border-[#2A303C] transition shadow-sm">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak</span>
            </button>

            <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); alert('[PETUNJUK SCREENSHOT]\nSurat jalan digital ini telah dioptimasi untuk layar smartphone.\nSilakan ambil tangkapan layar (screenshot) penuh untuk disimpan sebagai bukti izin offline di area minim sinyal.');" class="inline-flex items-center gap-2 text-xs font-mono-tech font-bold text-stone-200 hover:text-white bg-[#16191F] hover:bg-[#1E232D] px-3.5 py-2.5 rounded-xl border border-[#2A303C] transition shadow-sm">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Screenshot</span>
            </button>

            <a href="{{ $registration->publicPdfUrl() }}" class="inline-flex items-center gap-2 text-xs font-mono-tech font-bold text-black bg-[#FF5722] hover:bg-[#F4511E] px-4 py-2.5 rounded-xl shadow-lg shadow-[#FF5722]/20 transition transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Unduh PDF Resmi</span>
            </a>
        </div>
    </div>

    <!-- Main Digital Ticket Card (Obsidian Theme) -->
    <div class="ticket-card w-full max-w-2xl bg-[#121418] border border-[#20242C] rounded-3xl shadow-2xl overflow-hidden backdrop-blur-xl">

        <!-- Header Card Section -->
        <div class="bg-gradient-to-r from-[#181B22] via-[#121418] to-[#121418] p-6 md:p-8 border-b border-[#20242C] relative">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    @if(!empty($profile['app_logo']))
                        <img src="{{ asset('storage/' . $profile['app_logo']) }}" alt="Logo" class="w-12 h-12 rounded-xl object-contain bg-[#16191F] border border-[#262B35] p-1 shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-[#16191F] border border-[#262B35] flex items-center justify-center font-mono-tech text-base font-bold text-[#FF5722] shrink-0">
                            ▲
                        </div>
                    @endif
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-tech font-bold tracking-wide uppercase bg-[#FF5722]/10 text-[#FF5722] border border-[#FF5722]/30 mb-2">
                            <span class="w-2 h-2 rounded-full bg-[#FF5722] animate-pulse"></span>
                            E-Tiket SIMAKSI Digital Resmi
                        </div>
                        <h1 class="text-xl md:text-2xl font-display font-black text-white tracking-wide">{{ $profile['expedition_name'] ?? 'SAMSARA EXPEDITION' }}</h1>
                        <p class="text-xs text-stone-400 mt-1 font-mono-tech">Jalur Resmi Pendakian {{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} ({{ $profile['mountain_elevation'] ?? '2.821 MDPL' }})</p>
                    </div>
                </div>

                <div class="text-right">
                    <div class="font-mono-tech text-sm md:text-base font-bold text-[#FF5722] bg-[#0A0B0D] px-3.5 py-1.5 rounded-xl border border-[#FF5722]/40 tracking-wider inline-block">
                        {{ $registration->registration_code }}
                    </div>
                    <div class="mt-2">
                        @php
                            $badgeStyles = [
                                'approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                'paid' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                'checked_in' => 'bg-[#FF5722]/10 text-[#FF5722] border-[#FF5722]/30',
                                'completed' => 'bg-stone-500/10 text-stone-300 border-stone-500/30',
                                'rejected' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                            ];
                            $style = $badgeStyles[$registration->status] ?? 'bg-amber-500/10 text-amber-400 border-amber-500/30';
                        @endphp
                        <span class="inline-block text-[10px] font-mono-tech font-extrabold uppercase px-3 py-1 rounded-full border {{ $style }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Body -->
        <div class="p-6 md:p-8 space-y-6">

            <!-- KATEGORI OPERASIONAL JALUR: TEKTOK VS CAMP -->
            @if($registration->isTektok())
                <div class="rounded-2xl p-4 bg-cyan-950/40 border border-cyan-500/40 text-cyan-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 font-mono-tech font-bold text-xs">
                                TKT
                            </div>
                            <div>
                                <span class="text-[10px] font-mono-tech uppercase font-bold tracking-widest text-cyan-400 block">// SURAT JALAN KATEGORI</span>
                                <h3 class="font-display font-black text-base text-white">TEKTOK (ONE-DAY SUMMIT)</h3>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono-tech font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 uppercase">
                            03:00 - 07:00 WIB
                        </span>
                    </div>
                    <div class="text-xs text-stone-300 bg-[#0A0B0D]/70 p-3 rounded-xl border border-cyan-500/20 font-mono-tech space-y-1">
                        <p class="text-cyan-300 font-semibold">• Jam Buka Jalur Masuk: <strong>03:00 s/d 07:00 WIB</strong>.</p>
                        <p>• Wajib turun dan melapor check-out di basecamp pada <strong>hari yang sama (maks. 19:00 WIB)</strong>.</p>
                        <p class="text-rose-400 font-bold">• DILARANG mendirikan tenda di seluruh area pos (Pos 1 s/d Puncak).</p>
                    </div>
                </div>
            @else
                <div class="rounded-2xl p-4 bg-emerald-950/40 border border-emerald-500/40 text-emerald-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-mono-tech font-bold text-xs">
                                CMP
                            </div>
                            <div>
                                <span class="text-[10px] font-mono-tech uppercase font-bold tracking-widest text-emerald-400 block">// SURAT JALAN KATEGORI</span>
                                <h3 class="font-display font-black text-base text-white">CAMPING (BERMALAM)</h3>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono-tech font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 uppercase">
                            Buka Jam 07:00+ WIB
                        </span>
                    </div>
                    <div class="text-xs text-stone-300 bg-[#0A0B0D]/70 p-3 rounded-xl border border-emerald-500/20 font-mono-tech space-y-1">
                        <p class="text-emerald-300 font-semibold">• Jam Buka Jalur Masuk: <strong>Mulai pukul 07:00 WIB</strong>.</p>
                        <p>• Area Camp Resmi: <strong>Hanya di Pos 2 (Batu Gendrung - Hutan Pinus) dan Pos 3 (Thun 30)</strong>. Disarankan Pos 3, paling dekat ke puncak.</p>
                        <p class="text-rose-300">• <strong>DILARANG camp di puncak.</strong> Tinggalkan tenda dan perlengkapan di Pos 3 saat summit attack.</p>
                        <p>• Status Lokasi Camp: <strong class="text-white">{{ $registration->camp_location ? 'Terkonfirmasi: ' . $registration->camp_location : ($registration->planned_campsite ? 'Rencana: ' . $registration->planned_campsite : 'Pos 2 / Pos 3 (Bebas)') }}</strong>.</p>
                    </div>
                </div>
            @endif

            <!-- RADAR STATUS CHECKPOINT POS LAPANGAN -->
            <div class="bg-[#0E1013] rounded-2xl p-4 border border-[#20242C] space-y-3 font-mono-tech">
                <div class="flex items-center justify-between border-b border-[#1E2229] pb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">LOG CHECKPOINT RANGER POS</span>
                    </div>
                    <span class="text-[10px] text-stone-400">
                        Posisi Terakhir: <strong class="text-[#FF5722]">{{ $registration->last_seen_pos ? $registration->last_seen_pos . ' [' . ($registration->last_seen_direction === 'ascending' ? '↑ NAIK' : ($registration->last_seen_direction === 'descending' ? '↓ TURUN' : 'CAMP')) . ']' : 'Menunggu Check-in Gerbang' }}</strong>
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                    @php
                        $checkpointLogs = $registration->checkpoints->keyBy('pos_code');
                    @endphp

                    <!-- Basecamp -->
                    <div class="p-2.5 rounded-xl border {{ $registration->status === 'checked_in' || $registration->status === 'completed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300' : 'border-[#222731] bg-[#121418] text-stone-500' }}">
                        <span class="text-[9px] block uppercase font-bold">GERBANG</span>
                        <span class="font-bold text-xs">{{ $registration->checked_in_at ? 'CHECK-IN' : 'BELUM' }}</span>
                        <span class="text-[8px] block mt-0.5 text-stone-400">{{ $registration->checked_in_at ? $registration->checked_in_at->format('H:i') . ' WIB' : '-' }}</span>
                    </div>

                    <!-- Pos 1 -->
                    @php $p1 = $checkpointLogs->get('POS_1'); @endphp
                    <div class="p-2.5 rounded-xl border {{ $p1 ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300' : 'border-[#222731] bg-[#121418] text-stone-500' }}">
                        <span class="text-[9px] block uppercase font-bold">POS 1</span>
                        <span class="font-bold text-xs">{{ $p1 ? ($p1->direction === 'ascending' ? 'LEWAT [↑]' : 'LEWAT [↓]') : 'TRANSIT' }}</span>
                        <span class="text-[8px] block mt-0.5 text-stone-400">{{ $p1 ? $p1->scanned_at->format('H:i') . ' WIB' : 'Pending' }}</span>
                    </div>

                    <!-- Pos 2 -->
                    @php $p2 = $checkpointLogs->get('POS_2'); @endphp
                    <div class="p-2.5 rounded-xl border {{ $p2 ? ($p2->direction === 'camping' ? 'border-purple-500/40 bg-purple-500/10 text-purple-300' : 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300') : 'border-[#222731] bg-[#121418] text-stone-500' }}">
                        <span class="text-[9px] block uppercase font-bold">POS 2 (CAMP 1)</span>
                        <span class="font-bold text-xs">{{ $p2 ? ($p2->direction === 'camping' ? 'CAMP' : ($p2->direction === 'ascending' ? 'LEWAT [↑]' : 'LEWAT [↓]')) : 'LOKASI CAMP' }}</span>
                        <span class="text-[8px] block mt-0.5 text-stone-400">{{ $p2 ? $p2->scanned_at->format('H:i') . ' WIB' : 'Pending' }}</span>
                    </div>

                    <!-- Pos 3 -->
                    @php $p3 = $checkpointLogs->get('POS_3'); @endphp
                    <div class="p-2.5 rounded-xl border {{ $p3 ? ($p3->direction === 'camping' ? 'border-purple-500/40 bg-purple-500/10 text-purple-300' : 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300') : 'border-[#222731] bg-[#121418] text-stone-500' }}">
                        <span class="text-[9px] block uppercase font-bold">POS 3 (CAMP 2)</span>
                        <span class="font-bold text-xs">{{ $p3 ? ($p3->direction === 'camping' ? 'CAMP' : ($p3->direction === 'ascending' ? 'LEWAT [↑]' : 'LEWAT [↓]')) : 'LOKASI CAMP' }}</span>
                        <span class="text-[8px] block mt-0.5 text-stone-400">{{ $p3 ? $p3->scanned_at->format('H:i') . ' WIB' : 'Pending' }}</span>
                    </div>
                </div>
            </div>

            <!-- Barcode Verification Box -->
            <div class="bg-[#0E1013] rounded-2xl p-5 border border-[#20242C] flex flex-col sm:flex-row items-center justify-between gap-5">
                <div class="space-y-1 text-center sm:text-left">
                    <div class="text-xs font-mono-tech font-bold uppercase tracking-wider text-emerald-400 flex items-center justify-center sm:justify-start gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Barcode Verifikasi Pos Gerbang
                    </div>
                    <p class="text-[11px] text-stone-400">Tunjukkan kode ini kepada petugas pendaftaran di Pos Gerbang Basecamp untuk otentikasi cepat.</p>
                </div>
                <div class="bg-white p-3 rounded-xl shadow-md border border-stone-300 text-center flex-shrink-0 flex flex-col items-center">
                    <div class="p-1.5 bg-white rounded-lg flex items-center justify-center">
                        <img src="{{ \App\Support\QrCode::pngDataUri($registration->registration_code, 8, 4) }}" width="128" height="128" alt="QR Code" class="block w-28 h-28 sm:w-32 sm:h-32" style="image-rendering: pixelated;">
                    </div>
                    <div class="font-mono-tech font-black text-black text-xs tracking-widest px-2.5 py-0.5 bg-stone-100 rounded border border-stone-300 mt-1.5">
                        *{{ $registration->registration_code }}*
                    </div>
                    <div class="text-[9px] font-mono-tech font-bold text-stone-600 mt-1 uppercase tracking-wider">Valid Basecamp SAMSARA</div>
                </div>
            </div>

            <!-- FOTO REGU KEBERANGKATAN (SAR & IDENTIFIKASI KESELAMATAN) -->
            <div id="groupPhotoContainer" class="bg-[#0E1013] rounded-2xl p-5 border border-[#20242C] space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-[#1E2229]">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#FF5722] animate-pulse"></span>
                            <h2 class="text-xs font-mono-tech font-bold uppercase text-white tracking-wider flex items-center gap-1.5">
                                FOTO REGU KEBERANGKATAN (FOTO BERSAMA ANGGOTA)
                            </h2>
                        </div>
                        <p class="text-[11px] text-stone-400 mt-0.5">Wajib diambil di basecamp untuk identifikasi visual SAR jika terjadi keadaan darurat.</p>
                    </div>

                    <div id="photoStatusBadge">
                        @if($registration->group_photo_path)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-tech font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                TERVERIFIKASI SISTEM
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-tech font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping"></span>
                                WAJIB DIUNGGAH
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Existing Photo Display -->
                <div id="photoDisplayView" class="{{ $registration->group_photo_path ? '' : 'hidden' }} space-y-3">
                    <div class="relative rounded-xl overflow-hidden border border-[#262B35] bg-[#0A0B0D] group">
                        <img id="groupPhotoImg" src="{{ $registration->group_photo_path ? asset('storage/' . $registration->group_photo_path) : '' }}" alt="Foto Regu {{ $registration->registration_code }}" class="w-full h-auto max-h-96 object-cover object-center rounded-xl">
                        
                        <div class="absolute top-2 right-2 flex items-center gap-1.5 bg-black/80 backdrop-blur-md px-2.5 py-1 rounded-lg border border-white/10 text-[10px] text-stone-300 font-mono-tech">
                            <span class="text-emerald-400">●</span>
                            <span id="uploadedAtText">{{ $registration->group_photo_uploaded_at ? $registration->group_photo_uploaded_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Tervalidasi' }}</span>
                        </div>
                    </div>

                    <!-- Action & Social Share Buttons -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-1 font-mono-tech">
                        <div class="flex flex-wrap items-center gap-2">
                            @php
                                $mountainTitle = $profile['mountain_name'] ?? 'Gunung Cikuray';
                                $expeditionTitle = $profile['expedition_name'] ?? 'SAMSARA EXPEDITION';
                                $waText = rawurlencode("Halo! Rombongan pendakian {$registration->leader_name} ({$registration->total_climbers} Orang) telah resmi check-in di {$expeditionTitle} - {$mountainTitle} pada " . date('d M Y') . ". Foto regu & e-tiket resmi kami dapat dipantau di: " . $registration->publicTicketUrl());
                            @endphp
                            <a href="https://api.whatsapp.com/send?text={{ $waText }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-sm">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.173.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.044.072.044.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.158.572 4.183 1.571 5.928l-1.637 5.981 6.136-1.61c1.683.923 3.618 1.444 5.679 1.444 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
                                <span>Bagikan ke WhatsApp</span>
                            </a>

                            <button type="button" onclick="shareToInstagramOrDownload()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-purple-600 via-pink-600 to-rose-500 hover:opacity-95 text-white text-xs font-bold transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Simpan untuk Story IG</span>
                            </button>
                        </div>

                        <button type="button" onclick="triggerPhotoUpload()" class="text-stone-400 hover:text-white text-xs font-semibold underline decoration-stone-600 underline-offset-4 transition">
                            Ganti / Foto Ulang
                        </button>
                    </div>
                </div>

                <!-- Upload Form View -->
                <div id="photoUploadView" class="{{ $registration->group_photo_path ? 'hidden' : '' }} space-y-3">
                    <div class="border-2 border-dashed border-[#282E3A] hover:border-[#FF5722] rounded-xl p-6 text-center space-y-3 bg-[#121418] transition">
                        <div class="w-12 h-12 rounded-full bg-[#FF5722]/10 border border-[#FF5722]/30 flex items-center justify-center mx-auto text-[#FF5722]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>

                        <div class="space-y-1">
                            <h3 class="text-sm font-bold text-white font-display">Ambil Foto Seluruh Anggota Rombongan</h3>
                            <p class="text-xs text-stone-400 max-w-md mx-auto">
                                Pastikan wajah dan pakaian seluruh <strong>{{ $registration->total_climbers }} orang anggota</strong> terlihat jelas di depan plang basecamp. Sistem akan membubuhkan watermark resmi dan menyimpan foto selama 7 hari.
                            </p>
                        </div>

                        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3 font-mono-tech">
                            <button type="button" onclick="triggerPhotoUpload()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-[#FF5722] hover:bg-[#F4511E] text-black font-extrabold text-xs uppercase tracking-wider transition shadow-lg shadow-[#FF5722]/20 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Buka Kamera / Pilih Foto</span>
                            </button>
                        </div>
                    </div>
                </div>

                <input type="file" id="groupPhotoFileInput" accept="image/*" capture="environment" class="hidden" onchange="handleGroupPhotoSelected(this)">
                
                <div id="uploadProgressBar" class="hidden space-y-2 pt-2">
                    <div class="flex items-center justify-between text-xs text-stone-300 font-mono-tech">
                        <span id="progressText" class="font-medium">Mengompres & Mengunggah Foto...</span>
                        <span id="progressPercent" class="font-bold text-emerald-400">0%</span>
                    </div>
                    <div class="w-full bg-[#181B22] rounded-full h-2 overflow-hidden border border-[#282E3A]">
                        <div id="progressFill" class="bg-gradient-to-r from-emerald-500 to-[#FF5722] h-2 rounded-full transition-all duration-200" style="width: 0%"></div>
                    </div>
                </div>

                <div id="photoUploadAlert" class="hidden p-3 rounded-xl text-xs font-mono-tech font-medium"></div>

                <!-- Notice: Data Retention 7 Days -->
                <div class="text-[10px] text-stone-400 flex items-center gap-1.5 pt-1 border-t border-[#1E2229]">
                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Kebijakan Retensi: Foto regu disimpan terenkripsi selama <strong>7 hari</strong> demi keselamatan SAR, lalu otomatis dimusnahkan.</span>
                </div>
            </div>

            <!-- Grid Informasi Ekspedisi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Data Ketua Rombongan -->
                <div class="bg-[#0E1013] p-4 rounded-2xl border border-[#20242C] space-y-2.5">
                    <div class="text-[10px] font-mono-tech font-bold uppercase text-stone-400 tracking-wider pb-1.5 border-b border-[#1E2229]">
                        Ketua Rombongan
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white">{{ $registration->leader_name }}</div>
                        @php
                            $maskedLeaderNik = strlen($registration->leader_nik) > 8 
                                ? substr($registration->leader_nik, 0, 4) . '******' . substr($registration->leader_nik, -4)
                                : 'Terproteksi';
                            $maskedEmergencyPhone = strlen($registration->emergency_contact_phone) > 6
                                ? substr($registration->emergency_contact_phone, 0, 4) . '****' . substr($registration->emergency_contact_phone, -3)
                                : 'Terproteksi';
                        @endphp
                        <div class="text-xs text-stone-400 font-mono-tech mt-0.5">NIK: {{ $maskedLeaderNik }}</div>
                    </div>
                    <div class="text-xs text-stone-300 flex items-center gap-1.5 font-mono-tech">
                        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        {{ strlen((string) $registration->leader_phone) > 6 ? substr($registration->leader_phone, 0, 4).'****'.substr($registration->leader_phone, -3) : 'Terproteksi' }}
                    </div>
                    <div class="text-[11px] text-stone-400 pt-1 border-t border-[#1E2229]">
                        <span class="text-stone-500">Kontak Darurat:</span> {{ $registration->emergency_contact_name }} ({{ $maskedEmergencyPhone }})
                    </div>
                </div>

                <!-- Data Tanggal & Rombongan -->
                <div class="bg-[#0E1013] p-4 rounded-2xl border border-[#20242C] space-y-2.5">
                    <div class="text-[10px] font-mono-tech font-bold uppercase text-stone-400 tracking-wider pb-1.5 border-b border-[#1E2229]">
                        Jadwal & Kuota
                    </div>
                    <div>
                        <div class="text-xs text-stone-400 font-mono-tech">Tanggal Naik:</div>
                        <div class="text-sm font-bold text-emerald-400">{{ \Carbon\Carbon::parse($registration->hiking_date)->translatedFormat('l, d F Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-stone-400 font-mono-tech">Rencana Turun:</div>
                        <div class="text-xs font-semibold text-stone-200">{{ $registration->return_date ? \Carbon\Carbon::parse($registration->return_date)->translatedFormat('l, d F Y') : 'Hari yang sama (Tek-tok)' }}</div>
                    </div>
                    <div class="text-[11px] text-stone-300 pt-1 border-t border-[#1E2229] flex items-center justify-between font-mono-tech">
                        <span>Total Rombongan:</span>
                        <span class="font-bold text-white bg-[#181B22] px-2.5 py-0.5 rounded-lg border border-[#282E3A]">{{ $registration->total_climbers }} Pendaki</span>
                    </div>
                </div>
            </div>

            <!-- Daftar Anggota Pendaki (Manifest) -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-mono-tech font-bold uppercase text-stone-400 tracking-wider">Manifest Seluruh Anggota</h2>
                    <span class="text-[11px] font-mono-tech text-stone-500">{{ count($members) }} Peserta</span>
                </div>
                <div class="bg-[#0E1013] rounded-2xl border border-[#20242C] overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-[#121418] border-b border-[#20242C] text-stone-400 text-[10px] font-mono-tech uppercase font-bold tracking-wider">
                                <th class="py-2.5 px-3 text-center w-10">No</th>
                                <th class="py-2.5 px-3">Nama Anggota</th>
                                <th class="py-2.5 px-3">NIK</th>
                                <th class="py-2.5 px-3 text-center w-12">L/P</th>
                                <th class="py-2.5 px-3">Kondisi Medis</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#1E2229]">
                            @foreach($members as $idx => $m)
                            @php
                                $maskedMemberNik = strlen($m->nik) > 8
                                    ? substr($m->nik, 0, 4) . '******' . substr($m->nik, -4)
                                    : ($m->nik ?: '-');
                            @endphp
                            <tr class="hover:bg-[#16191F] transition">
                                <td class="py-2 px-3 text-center text-stone-500 font-mono-tech">{{ $idx + 1 }}</td>
                                <td class="py-2 px-3 font-semibold text-stone-200">
                                    {{ $m->full_name }}
                                    @if($m->is_leader)
                                    <span class="ml-1 text-[9px] font-mono-tech font-bold text-[#FF5722] bg-[#FF5722]/10 px-1.5 py-0.5 rounded border border-[#FF5722]/30">KETUA</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 font-mono-tech text-stone-400">{{ $maskedMemberNik }}</td>
                                <td class="py-2 px-3 text-center font-bold text-stone-300">{{ $m->gender }}</td>
                                <td class="py-2 px-3 {{ ($m->medical_conditions && $m->medical_conditions !== 'Tidak Ada') ? 'text-amber-400 font-medium' : 'text-stone-400' }}">
                                    {{ $m->medical_conditions ?: 'Tidak Ada' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rincian Biaya & Logistik -->
            <div class="bg-[#0E1013] p-4 rounded-2xl border border-[#20242C] space-y-2">
                <div class="text-[10px] font-mono-tech font-bold uppercase text-stone-400 tracking-wider pb-1.5 border-b border-[#1E2229] flex items-center justify-between">
                    <span>Rincian Biaya & Layanan</span>
                    <span class="text-emerald-400 font-bold uppercase">{{ $registration->payment_method }}</span>
                </div>
                <div class="space-y-1.5 text-xs text-stone-300 font-mono-tech">
                    <div class="flex justify-between">
                        <span class="text-stone-400">Tiket SIMAKSI ({{ $registration->total_climbers }} Orang)</span>
                        <span class="font-semibold text-white">Rp {{ number_format($registration->simaksi_fee, 0, ',', '.') }}</span>
                    </div>
                    @if($registration->ojek_fee > 0)
                    <div class="flex justify-between">
                        <span class="text-stone-400">Shuttle Ojek Basecamp ({{ $registration->ojek_count }} Tiket)</span>
                        <span class="font-semibold text-white">Rp {{ number_format($registration->ojek_fee, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($registration->parking_fee > 0)
                    <div class="flex justify-between">
                        <span class="text-stone-400">Retribusi Parkir Kendaraan</span>
                        <span class="font-semibold text-white">Rp {{ number_format($registration->parking_fee, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($registration->unique_code > 0)
                    <div class="flex justify-between">
                        <span class="text-stone-400">Kode Unik Verifikasi</span>
                        <span class="font-semibold text-amber-400">+Rp {{ number_format($registration->unique_code, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="pt-2 border-t border-[#1E2229] flex justify-between text-sm font-extrabold text-white">
                        <span>TOTAL LUNAS / BAYAR:</span>
                        <span class="text-[#FF5722] text-base">Rp {{ number_format($registration->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="bg-[#0A0B0D] p-4 border-t border-[#1E2229] text-center text-[10px] font-mono-tech text-stone-500 space-y-1">
            <p>
                Lokasi Basecamp: <span class="text-stone-300">{{ $profile['basecamp_location'] ?? 'Garut, Jawa Barat' }}</span>
                • <a href="{{ $profile['basecamp_maps_url'] ?? '' }}" target="_blank" rel="noopener noreferrer" class="text-[#FF5722] hover:underline font-bold inline-flex items-center gap-0.5">Google Maps ↗</a>
            </p>
            @if(!empty($profile['sar_radio_enabled']) && !empty($profile['sar_frequency']))
                <p>Frekuensi Radio Darurat SAR: <strong class="text-stone-300">{{ $profile['sar_frequency'] }}</strong> | Hotline SAR: <strong class="text-stone-300">{{ $profile['emergency_hotline'] ?? '' }}</strong></p>
            @else
                <p>Hotline Darurat SAR 24 Jam: <strong class="text-stone-300">{{ $profile['emergency_hotline'] ?? '' }}</strong></p>
            @endif
            <p>© {{ date('Y') }} {{ $profile['expedition_name'] ?? 'SAMSARA' }} Portal. Dokumen sah terotentikasi digital.</p>
        </div>

    </div>

    <script>
        const REGISTRATION_CODE = "{{ $registration->registration_code }}";
        const ACCESS_TOKEN = "{{ $registration->access_token }}";
        const UPLOAD_URL = "{{ route('portal.simaksi.upload_group_photo') }}";

        function triggerPhotoUpload() {
            const input = document.getElementById('groupPhotoFileInput');
            if (input) {
                input.value = '';
                input.click();
            }
        }

        function showAlert(msg, isSuccess = true) {
            const alertBox = document.getElementById('photoUploadAlert');
            if (!alertBox) return;
            alertBox.className = 'p-3 rounded-xl text-xs font-mono-tech font-semibold ' + 
                (isSuccess ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-300 border border-rose-500/30');
            alertBox.textContent = msg;
            alertBox.classList.remove('hidden');
            setTimeout(() => {
                alertBox.classList.add('hidden');
            }, 6000);
        }

        function handleGroupPhotoSelected(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];

            const pBar = document.getElementById('uploadProgressBar');
            const pFill = document.getElementById('progressFill');
            const pPercent = document.getElementById('progressPercent');
            const pText = document.getElementById('progressText');

            pBar.classList.remove('hidden');
            pFill.style.width = '10%';
            pPercent.textContent = '10%';
            pText.textContent = 'Memproses & mengoptimasi foto...';

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    pFill.style.width = '30%';
                    pPercent.textContent = '30%';
                    pText.textContent = 'Menyesuaikan resolusi foto...';

                    const maxW = 1400;
                    let w = img.width;
                    let h = img.height;
                    if (w > maxW) {
                        h = Math.round((h * maxW) / w);
                        w = maxW;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, w, h);

                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            pBar.classList.add('hidden');
                            showAlert('Gagal memproses gambar.', false);
                            return;
                        }

                        pFill.style.width = '50%';
                        pPercent.textContent = '50%';
                        pText.textContent = 'Mengunggah & menyematkan watermark resmi...';

                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const formData = new FormData();
                        formData.append('registration_code', REGISTRATION_CODE);
                        formData.append('access_token', ACCESS_TOKEN);
                        formData.append('group_photo', blob, 'group_photo.webp');
                        formData.append('_token', csrfToken);

                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', UPLOAD_URL, true);

                        xhr.upload.onprogress = function(evt) {
                            if (evt.lengthComputable) {
                                const percent = 50 + Math.round((evt.loaded / evt.total) * 45);
                                pFill.style.width = percent + '%';
                                pPercent.textContent = percent + '%';
                            }
                        };

                        xhr.onload = function() {
                            pFill.style.width = '100%';
                            pPercent.textContent = '100%';
                            setTimeout(() => {
                                pBar.classList.add('hidden');
                            }, 500);

                            if (xhr.status >= 200 && xhr.status < 300) {
                                try {
                                    const resp = JSON.parse(xhr.responseText);
                                    if (resp.success) {
                                        const imgEl = document.getElementById('groupPhotoImg');
                                        imgEl.src = resp.photo_url;
                                        
                                        const uploadedAt = document.getElementById('uploadedAtText');
                                        if (uploadedAt) uploadedAt.textContent = resp.uploaded_at;

                                        document.getElementById('photoUploadView').classList.add('hidden');
                                        document.getElementById('photoDisplayView').classList.remove('hidden');

                                        const badge = document.getElementById('photoStatusBadge');
                                        badge.innerHTML = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-tech font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            TERVERIFIKASI SISTEM
                                        </span>`;

                                        showAlert('Foto regu berhasil diunggah & dibubuhi watermark resmi!', true);
                                    } else {
                                        showAlert(resp.message || 'Gagal mengunggah foto.', false);
                                    }
                                } catch (err) {
                                    showAlert('Respon server tidak valid.', false);
                                }
                            } else {
                                try {
                                    const errResp = JSON.parse(xhr.responseText);
                                    showAlert(errResp.message || 'Gagal mengunggah foto ke server.', false);
                                } catch(e) {
                                    showAlert('Gagal mengunggah foto (Kode: ' + xhr.status + ').', false);
                                }
                            }
                        };

                        xhr.onerror = function() {
                            pBar.classList.add('hidden');
                            showAlert('Koneksi terputus saat mengunggah foto. Pastikan internet aktif.', false);
                        };

                        xhr.send(formData);
                    }, 'image/webp', 0.85);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        async function shareToInstagramOrDownload() {
            const imgEl = document.getElementById('groupPhotoImg');
            if (!imgEl || !imgEl.src) {
                showAlert('Foto belum tersedia.', false);
                return;
            }

            try {
                const response = await fetch(imgEl.src);
                const blob = await response.blob();
                const file = new File([blob], `Foto_Regu_${REGISTRATION_CODE}.webp`, { type: 'image/webp' });

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        files: [file],
                        title: `Foto Regu SIMAKSI ${REGISTRATION_CODE}`,
                        text: `Siap mendaki bersama rombongan! E-Tiket Resmi: ${window.location.href}`,
                    });
                } else {
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = `Foto_Regu_${REGISTRATION_CODE}.webp`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    showAlert('Foto ber-watermark berhasil diunduh ke galeri! Siap dibagikan ke Story Instagram Anda.', true);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    const a = document.createElement('a');
                    a.href = imgEl.src;
                    a.target = '_blank';
                    a.download = `Foto_Regu_${REGISTRATION_CODE}.webp`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            }
        }
    </script>
</body>
</html>
