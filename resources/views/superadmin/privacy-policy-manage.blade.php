@extends('superadmin.layout.master')
@section('content')
<div class="p-0 mt-3">
    <div class="row">
        <div class="col-md-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                <li class="active"><span><b>Privacy Policy Management</b></span></li>
            </ol>
        </div>
        <div class="col-md-6"></div>
        <div class="col-md-2">
            <div class="page-rightheader ms-md-auto">
                <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                    <div class="d-lg-flex d-block ms-auto">
                        <div class="btn-list">
                            <button id="addRowBtn" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPolicyModal">+ Add Row</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add/Edit Policy Modal -->
<div class="modal fade" id="addPolicyModal" tabindex="-1" aria-labelledby="addPolicyModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPolicyModalLabel">Add Privacy Policy</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addPolicyForm">
          <div class="mb-3">
            <label for="modalTitle" class="form-label">Title</label>
            <input type="text" class="form-control" id="modalTitle" required placeholder="Enter title">
          </div>
          <div class="mb-3">
            <label for="modalDesc" class="form-label">Description</label>
            <textarea class="form-control" id="modalDesc" rows="4" required placeholder="Enter description"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="savePolicyBtn">Save</button>
      </div>
    </div>
  </div>
</div>
<div class="datatable mt-3">
    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Privacy Policy List</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom align-middle" id="privacyPolicyTable" style="min-width: 800px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="max-width: 5%;">S.No</th>
                                    <th style="max-width: 10%;">Title</th>
                                    <th style="max-width: 80%;">Description</th>
                                    <th style="max-width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Existing policies will be loaded here by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    #privacyPolicyTable input.form-control, #privacyPolicyTable textarea.form-control {
        font-size: 1.1rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
    }
    #addRowBtn {
        font-size: 1rem;
        padding: 0.4rem 1.2rem;
    }
    .card-title {
        font-size: 1.3rem;
        font-weight: 600;
    }
    .edit-icon {
        color: #0d6efd;
        cursor: pointer;
        font-size: 1.3rem;
        transition: color 0.2s;
    }
    .edit-icon:hover {
        color: #0a58ca;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#privacyPolicyTable tbody');
    const addRowBtn = document.getElementById('addRowBtn');
    const savePolicyBtn = document.getElementById('savePolicyBtn');
    const modalTitle = document.getElementById('modalTitle');
    const modalDesc = document.getElementById('modalDesc');
    const addPolicyModalLabel = document.getElementById('addPolicyModalLabel');
    let policies = [];
    let addPolicyModal = null;
    let editIndex = null;
    if (window.bootstrap) {
        addPolicyModal = new bootstrap.Modal(document.getElementById('addPolicyModal'));
    } else {
        addPolicyModal = new window.bootstrap.Modal(document.getElementById('addPolicyModal'));
    }

    // Fetch existing policies
    fetch('/privacy-policy')
        .then(res => res.json())
        .then(data => {
            policies = data.result || [];
            renderRows();
        });

    function renderRows() {
        tableBody.innerHTML = '';
        policies.forEach((policy, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${idx + 1}</td>
                <td>${policy.pp_title ? escapeHtml(policy.pp_title) : ''}</td>
                <td>${policy.pp_description ? escapeHtml(truncateText(policy.pp_description, 240)) : ''}</td>
                <td class="text-center">
                    <i class="fa fa-edit edit-icon" data-idx="${idx}" title="Edit"></i>
                </td>
            `;
            tableBody.appendChild(tr);
        });
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        var map = {
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    // Truncate text to a maximum length
    function truncateText(text, maxLength) {
        if (text.length > maxLength) {
            return text.substring(0, maxLength) + '...';
        }
        return text;
    }
    // Add new policy
    addRowBtn.addEventListener('click', function() {
        editIndex = null;
        addPolicyModalLabel.textContent = 'Add Privacy Policy';
        modalTitle.value = '';
        modalDesc.value = '';
    });

    // Edit policy
    tableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-icon')) {
            editIndex = parseInt(e.target.getAttribute('data-idx'));
            const policy = policies[editIndex];
            addPolicyModalLabel.textContent = 'Edit Privacy Policy';
            modalTitle.value = policy.pp_title;
            modalDesc.value = policy.pp_description;
            addPolicyModal.show();
        }
    });

    // Save (add or edit)
    savePolicyBtn.addEventListener('click', function() {
        const title = modalTitle.value.trim();
        const desc = modalDesc.value.trim();
        if (!title || !desc) {
            modalTitle.classList.toggle('is-invalid', !title);
            modalDesc.classList.toggle('is-invalid', !desc);
            return;
        }
        if (editIndex === null) {   
            // Add new
            fetch('/privacy-policy', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({pp_title: title, pp_description: desc})
            })
            .then(res => res.json())
            .then(newPolicy => {
                policies.push(newPolicy.result); // Use only the actual policy object
                renderRows();
                addPolicyModal.hide();
            });
        } else {
            // Edit existing
            const policy = policies[editIndex];
            fetch(`/superadmin/privacy-policy/${policy.pp_id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({pp_title: title, pp_description: desc})
            })
            .then(() => {
                policies[editIndex].pp_title = title;
                policies[editIndex].pp_description = desc;
                renderRows();
                addPolicyModal.hide();
            });
        }
        modalTitle.classList.remove('is-invalid');
        modalDesc.classList.remove('is-invalid');
    });
});
</script>
@endsection 