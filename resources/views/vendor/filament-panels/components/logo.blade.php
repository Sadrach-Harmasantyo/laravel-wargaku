<div
    class="flex space-x-2 w-full justify-center items-center"
    style="width: 100%; {{ (request()->is('login') || request()->is('admin/login')) ? 'margin-bottom: 20px;' : '' }}"
>
    <img
        src="{{ asset('images/logo-wargaku.png') }}"
        alt="Logo-Wargaku"
        width="75"
    />

    @if (!request()->is('login') && !request()->is('admin/login'))
        <h1 class="text-xl font-bold" style="color: #2563eb">WargaKu</h1>
    @endif
</div>
