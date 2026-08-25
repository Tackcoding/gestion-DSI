{{-- L'application est entierement interne : pas de page publique. --}}
@php
    return redirect()->route(auth()->check() ? 'tableau-de-bord' : 'login');
@endphp
