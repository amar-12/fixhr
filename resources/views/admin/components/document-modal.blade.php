<div class="modal fade" id="documentsModal{{ $id }}"
    tabindex="-1"
    aria-labelledby="documentsModalLabel{{ $id }}"
    aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content tx-size-sm">
            <div class="modal-header">
                <h5 class="modal-title" id="documentsModalLabel{{ $id }}">
                    Uploaded Documents
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="text-start">
                    @foreach ($documents as $dkey => $document)
                        @php
                            // Locate the position of "PlanDetail_" in the file path
                            $position = strpos($document, $componentString);
                            // Extract the portion of the path after "PlanDetail_"
                            if ($position !== false) {
                                $displayName = substr($document, $position);
                            } else {
                                // If "PlanDetail_" is not found, use the full file name
                                $displayName = basename($document);
                            }
                        @endphp
                        <li>
                            <a href="{{ asset($document) }}" target="_blank" class="text-primary">
                                {{ $displayName }} - View file {{ $dkey + 1 }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
