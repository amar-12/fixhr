<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label"
    aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog {{ $size }}">
        <div class="modal-content">
            <!-- Form wrapper -->
            <form id="{{ $formId ?? $id . 'Form' }}"
                  method="{{ $method ?? 'POST' }}"
                  action="{{ $action ?? '#' }}"
                  enctype="{{ $enctype ?? 'multipart/form-data' }}">
                <div class="modal-header">
                    <input type="hidden" name="id">
                    <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button" aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    {{ $slot }}
                </div>
                <div class="modal-footer">
                    @isset($footer)
                        {{ $footer }}
                    @else
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-outline-primary" id="{{ $submitButtonId ?? 'saveButton' }}">
                            {{ $submitButtonText ?? '.' }}
                        </button>
                    @endisset
                </div>
            </form>
        </div>
    </div>
</div>


{{-- <div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label"
    aria-hidden="true"  data-bs-backdrop="static">
    <div class="modal-dialog {{ $size }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            <div class="modal-footer">
                @isset($footer)
                    {{ $footer }}
                @else
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                @endisset
            </div>
        </div>
    </div>
</div> --}}
{{-- Below Example How To Use --}}
{{-- <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#customFooterModal">Custom Footer Modal</button>

<!-- Trigger Buttons -->
<button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#basicModal">Basic Modal</button>
<button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#smallModal">Small Modal</button>
<button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#largeModal">Large Modal</button>
<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#scrollingModal">Scrolling Modal</button> --}}
