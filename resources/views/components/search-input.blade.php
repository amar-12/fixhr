@props([
    'label',
    'wireModel',
    'results'       => [],
    'selectMethod',
    'idField',
    'nameField',
    'selected'      => null,
    'errorField'    => null,
    'mainCol'       => 'col-md-6',
])

<div class="{{ $mainCol }}">
    <div x-data="{ open: false }" @click.away="open = false">

        {{-- Label above --}}
        <label class="ef-field-label d-block mb-1" style="text-align:left; line-height:1.4;">{{ $label }}</label>

        {{-- Input wrap --}}
        <div class="position-relative">
            <div class="ef-input-wrap">
                <input
                    type="text"
                    wire:model.live="{{ $wireModel }}"
                    @click="open = true"
                    @focus="open = true"
                    class="ef-control"
                    placeholder="Search…"
                    style="padding-right: 32px;"
                >
                <span class="ef-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </span>
            </div>{{-- /.ef-input-wrap --}}

            {{-- Results dropdown --}}
            <ul class="ef-results" x-show="open" x-transition>
                @forelse ($results as $item)
                    <li
                        wire:click="{{ $selectMethod }}({{ $item[$idField] }}, '{{ $item[$nameField] }}')"
                        @click="open = false"
                    >
                        {{ $item[$nameField] }}{{ !empty($item['emp_code']) ? ' (' . $item['emp_code'] . ')' : '' }}
                    </li>
                @empty
                    <li class="ef-no-result">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                             fill="currentColor" viewBox="0 0 16 16" class="me-1">
                            <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1L14 4.5zM8.646 6.646a.5.5 0 1 0-.707.708L9.293 8l-1.354 1.354a.5.5 0 1 0 .707.707L10 8.707l1.354 1.354a.5.5 0 1 0 .707-.707L10.707 8l1.354-1.354a.5.5 0 0 0-.707-.707L10 7.293z"/>
                        </svg>
                        No results found
                    </li>
                @endforelse
            </ul>
        </div>{{-- /.position-relative --}}

        {{-- Selected badge --}}
        {{-- @if ($selected)
            <span class="ef-badge">
                {{ $selected }}
                <button class="ef-badge-remove"
                        wire:click="{{ $selectMethod }}(null, '')"
                        title="Remove">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" width="10" height="10">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </span>
        @endif --}}

        {{-- Validation error --}}
        @if ($errorField && $errors->has($errorField))
            <span class="text-danger" style="font-size:11px;">
                {{ $errors->first($errorField) }}
            </span>
        @endif

    </div>{{-- /x-data --}}
</div>{{-- /.mainCol --}}