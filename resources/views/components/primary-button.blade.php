<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primaire']) }}>
    {{ $slot }}
</button>
