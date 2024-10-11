@if ($paginator->hasPages())
    <div id="pagination" class="pt-4">
        @if ($paginator->onFirstPage())
            <span class="blocks" aria-disabled="true">&laquo;</span>
        @else
            <a class="blocks" wire:click="previousPage" wire:loading.attr="disabled" rel="prev">&laquo;</a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <a class="blocks" aria-disabled="true">
                    {{ $element }}
                </a>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <a wire:click.prevent="setPage({{$page}})" class="blocks active" aria-current="page">
                            {{ $page }}
                        </a>
                    @else
                        <a href="{{ $url }}"
                           wire:click.prevent="setPage({{$page}})"
                           class="blocks"
                           aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->onLastPage())
            <span class="blocks" aria-disabled="true">&raquo;</span>
        @else
            <a class="blocks" wire:click="nextPage" wire:loading.attr="disabled" rel="next">&raquo;</a>
        @endif
    </div>
@endif
