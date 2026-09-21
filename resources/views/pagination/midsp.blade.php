{{-- Pagination Livewire aux couleurs de la charte.
     Usage dans une vue Livewire : {{ $evenements->links('pagination.midsp') }} --}}
@php
    $nomPage = $paginator->getPageName();
@endphp
<div>
    @if ($paginator->hasPages())
        <nav class="pagination" role="navigation" aria-label="Pagination">
            <span>
                @if (method_exists($paginator, 'total'))
                    {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
                @else
                    Page {{ $paginator->currentPage() }}
                @endif
            </span>

            <div class="pagination-pages">
                @if ($paginator->onFirstPage())
                    <button type="button" class="pagination-page" disabled aria-label="Page précédente">
                        <x-ui.icone nom="fleche-gauche" />
                    </button>
                @else
                    <button type="button" class="pagination-page"
                            wire:click="previousPage('{{ $nomPage }}')" wire:loading.attr="disabled"
                            aria-label="Page précédente">
                        <x-ui.icone nom="fleche-gauche" />
                    </button>
                @endif

                @if (isset($elements))
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="pagination-points" aria-hidden="true">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="pagination-page" aria-current="page" wire:key="page-{{ $nomPage }}-{{ $page }}">{{ $page }}</span>
                                @else
                                    <button type="button" class="pagination-page"
                                            wire:click="gotoPage({{ $page }}, '{{ $nomPage }}')" wire:loading.attr="disabled"
                                            wire:key="page-{{ $nomPage }}-{{ $page }}"
                                            aria-label="Aller à la page {{ $page }}">{{ $page }}</button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @endif

                @if ($paginator->hasMorePages())
                    <button type="button" class="pagination-page"
                            wire:click="nextPage('{{ $nomPage }}')" wire:loading.attr="disabled"
                            aria-label="Page suivante">
                        <x-ui.icone nom="fleche-gauche" class="rotate-180" />
                    </button>
                @else
                    <button type="button" class="pagination-page" disabled aria-label="Page suivante">
                        <x-ui.icone nom="fleche-gauche" class="rotate-180" />
                    </button>
                @endif
            </div>
        </nav>
    @endif
</div>
