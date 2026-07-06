<div>
    <!-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison -->
    <div class="card-header p-3">
        <h3 class="card-title">{{$moduleName}} Approval Or Reject</h3>
    </div>
    <div class="card-body">
        <form id="approvalEmployeeMappingForm" method="POST" action="{{$actionUrl}}">
            @csrf
            <input type="hidden" name="log_request_id" value="{{ $primaryId }}">
            <input type="hidden" name="log_module_id" value="{{ $moduleId }}">
            <div class="form-group">
                <div class="row">
                    <label class="form-label mb-0 mt-2">Message<span class="text-danger">&nbsp;*</span></label>
                    <div class="col-md-12 col-lg-12">
                        <textarea rows="2" name="log_description" class="form-control" id="log_description"></textarea>
                    </div>
                </div>
                <div class="card-footer px-0 d-flex float-end">
                    @foreach ($masterApproveBtn as $item)
                        <button type="button" class="btn ms-3 approve-btn"
                            style="background-color: {{ json_decode($item->m_other)->color }}"
                            data-status="{{ $item->m_id }}">{{ $item->m_name }}</button>
                    @endforeach
                </div>
            </div>
        </form>
    </div>
</div>
