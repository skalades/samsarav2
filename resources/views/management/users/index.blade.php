@extends('management.layout')

@section('title', 'Petugas & Akses')
@section('page_title', 'Petugas & Akses')

@section('content')
@php
  $levelMeta = [
    'superadmin' => ['tone' => 'brand', 'icon' => 'shield'],
    'kasir' => ['tone' => 'success', 'icon' => 'cash'],
    'gatekeeper' => ['tone' => 'info', 'icon' => 'gate'],
    'ranger' => ['tone' => 'violet', 'icon' => 'pin'],
  ];
@endphp

<x-ui.page-header title="Petugas & Akses" subtitle="Empat level akses: setiap petugas hanya melihat menu sesuai tugasnya" eyebrow="Pengaturan">
  <x-slot:actions>
    <x-ui.button icon="plus" onclick="openUserModal()">Tambah petugas</x-ui.button>
  </x-slot:actions>
</x-ui.page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
  @foreach($roles as $key => $role)
    <a href="{{ route('management.users.index', $level === $key ? [] : ['level' => $key]) }}" class="p-4 rounded-2xl ring-1 transition {{ $level === $key ? 'bg-raised ring-brand/40' : 'bg-surface ring-line hover:ring-line-strong' }}">
      <div class="flex items-center justify-between">
        <x-ui.badge :tone="$levelMeta[$key]['tone']">{{ $role['label'] }}</x-ui.badge>
        <span class="font-display font-bold text-xl text-ink">{{ $counts[$key] ?? 0 }}</span>
      </div>
      <p class="text-[11px] text-muted mt-2 leading-snug">{{ $role['description'] }}</p>
    </a>
  @endforeach
</div>

<x-ui.card :padding="false">
  <div class="divide-y divide-line">
    @forelse($users as $u)
      @php $meta = $levelMeta[$u->level()]; @endphp
      <div class="flex items-center gap-3 px-4 py-3 {{ $u->isActive() ? '' : 'opacity-60' }}">
        <span class="w-10 h-10 rounded-xl bg-raised ring-1 ring-line flex items-center justify-center text-muted shrink-0"><x-ui.icon :name="$meta['icon']" class="w-5 h-5" /></span>
        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-center gap-2">
            <p class="text-sm font-semibold text-ink truncate">{{ $u->name }}</p>
            <x-ui.badge :tone="$meta['tone']" size="xs">{{ $u->roleLabel() }}</x-ui.badge>
            @unless($u->isActive())<x-ui.badge tone="danger" size="xs">Nonaktif</x-ui.badge>@endunless
          </div>
          <p class="text-xs text-muted truncate">{{ $u->email }}{{ $u->phone ? ' · '.$u->phone : '' }}</p>
          <p class="text-[11px] text-subtle">Login terakhir: {{ $u->last_login_at?->diffForHumans() ?? 'belum pernah' }}</p>
        </div>
        <div class="flex items-center gap-1 shrink-0">
          <button type="button" class="h-9 px-3 rounded-lg text-xs font-semibold text-muted hover:text-ink hover:bg-hover"
            onclick="openUserModal({{ \Illuminate\Support\Js::from(['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'phone' => $u->phone, 'role' => $u->level()]) }})">Ubah</button>
          @if($u->id !== auth()->id())
            <form method="POST" action="{{ route('management.users.toggle', $u->id) }}" onsubmit="return confirm('{{ $u->isActive() ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $u->name }}?')">
              @csrf
              <button type="submit" class="h-9 px-3 rounded-lg text-xs font-semibold {{ $u->isActive() ? 'text-rose-300 hover:bg-rose-500/10' : 'text-emerald-300 hover:bg-emerald-500/10' }}">{{ $u->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}</button>
            </form>
          @endif
        </div>
      </div>
    @empty
      <x-ui.empty icon="users" title="Belum ada petugas di level ini" />
    @endforelse
  </div>
</x-ui.card>

{{-- Modal tambah / ubah --}}
<div id="userModal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-end sm:items-center justify-center sm:p-4" onclick="if(event.target === this) closeUserModal()">
  <form id="userForm" method="POST" action="{{ route('management.users.store') }}" class="w-full sm:max-w-lg bg-surface ring-1 ring-line-strong rounded-t-3xl sm:rounded-2xl shadow-2xl max-h-[92vh] overflow-y-auto">
    @csrf
    <input type="hidden" name="_method" id="userFormMethod" value="POST">
    <div class="px-5 py-4 border-b border-line flex items-center justify-between">
      <h3 id="userModalTitle" class="font-display font-semibold text-lg text-ink">Tambah petugas</h3>
      <button type="button" onclick="closeUserModal()" class="w-9 h-9 rounded-full bg-raised ring-1 ring-line text-muted flex items-center justify-center"><x-ui.icon name="x" class="w-4 h-4" /></button>
    </div>
    <div class="p-5 space-y-4">
      <div><label class="label">Nama lengkap</label><input type="text" name="name" id="uf_name" required class="field"></div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="label">Email login</label><input type="email" name="email" id="uf_email" required class="field"></div>
        <div><label class="label">No. HP</label><input type="text" name="phone" id="uf_phone" class="field" inputmode="tel"></div>
      </div>
      <div>
        <label class="label">Level akses</label>
        <div class="grid grid-cols-2 gap-2">
          @foreach($roles as $key => $role)
            <label class="relative cursor-pointer select-none">
              <input type="radio" name="role" value="{{ $key }}" class="peer absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10 role-radio" @checked($key === 'kasir')>
              <span class="block p-3 rounded-xl ring-1 ring-line-strong bg-raised peer-checked:ring-brand peer-checked:bg-brand/10 pointer-events-none">
                <span class="block text-sm font-semibold text-ink">{{ $role['label'] }}</span>
                <span class="block text-[11px] text-muted leading-snug mt-0.5">{{ $role['description'] }}</span>
              </span>
            </label>
          @endforeach
        </div>
      </div>
      <div>
        <label class="label" id="uf_password_label">Kata sandi (min. 8 karakter)</label>
        <input type="password" name="password" id="uf_password" class="field" autocomplete="new-password">
      </div>
    </div>
    <div class="px-5 py-4 border-t border-line flex gap-2 justify-end safe-bottom">
      <x-ui.button variant="secondary" onclick="closeUserModal()">Batal</x-ui.button>
      <x-ui.button type="submit" icon="check">Simpan</x-ui.button>
    </div>
  </form>
</div>

@push('scripts')
<script>
  const storeUrl = @json(route('management.users.store'));
  const updateUrlTpl = @json(route('management.users.update', '__ID__'));

  function openUserModal(user = null) {
    const form = document.getElementById('userForm');
    document.getElementById('userModalTitle').innerText = user ? 'Ubah petugas' : 'Tambah petugas';
    form.action = user ? updateUrlTpl.replace('__ID__', user.id) : storeUrl;
    document.getElementById('userFormMethod').value = user ? 'PUT' : 'POST';
    document.getElementById('uf_name').value = user?.name ?? '';
    document.getElementById('uf_email').value = user?.email ?? '';
    document.getElementById('uf_phone').value = user?.phone ?? '';
    document.getElementById('uf_password').value = '';
    document.getElementById('uf_password').required = !user;
    document.getElementById('uf_password_label').innerText = user ? 'Kata sandi baru (kosongkan bila tidak diubah)' : 'Kata sandi (min. 8 karakter)';
    document.querySelectorAll('.role-radio').forEach(r => r.checked = r.value === (user?.role ?? 'kasir'));
    document.getElementById('userModal').classList.remove('hidden');
  }
  function closeUserModal() { document.getElementById('userModal').classList.add('hidden'); }
</script>
@endpush
@endsection
