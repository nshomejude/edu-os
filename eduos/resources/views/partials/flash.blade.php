@if (session('flash'))
    <div class="flash">{{ session('flash') }}</div>
@endif
@if (session('flash_error'))
    <div class="flash error">{{ session('flash_error') }}</div>
@endif
