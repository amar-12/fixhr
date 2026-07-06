@extends('admin.layout.master')
@section('title', 'Add New Asset')

@section('content')
    <!-- Add CSRF Token Meta Tag -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New Asset</h5>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#assetTypeModal">
                            <i class="bi bi-plus me-1"></i> New Type
                        </button>

                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#brandModal">
                            <i class="bi bi-gear me-1"></i> Brand
                        </button>

                        <button type="button" class="btn btn-outline-info btn-sm rounded-pill d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#categoryModal">
                            <i class="bi bi-list me-1"></i> Categories
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('assets.store') }}" id="assetForm" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            {{-- 1. Asset Type --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="asset_type_id" class="form-label">Asset Type</label>
                                    <select name="asset_type_id" id="asset_type_id"
                                        class="form-select select2 @error('asset_type_id') is-invalid @enderror" required>
                                        <option value="">Select Asset Type</option>
                                        @foreach ($assetTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ old('asset_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('asset_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 2. Vendor --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="vendor_name" class="form-label">Vendor</label>
                                    <input type="text" name="vendor_name" id="vendor_name"
                                        class="form-control @error('vendor_name') is-invalid @enderror"
                                        value="{{ old('vendor_name') }}" required>
                                    @error('vendor_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 3. PO No. --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="po_no" class="form-label">PO No.</label>
                                    <input type="text" name="po_no" id="po_no"
                                        class="form-control @error('po_no') is-invalid @enderror"
                                        value="{{ old('po_no') }}" required>
                                    @error('po_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 4. PO Date --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="purchase_date" class="form-label">PO Date</label>
                                    <input type="date" name="purchase_date" id="purchase_date"
                                        class="form-control @error('purchase_date') is-invalid @enderror"
                                        value="{{ old('purchase_date') }}" required>
                                    @error('purchase_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 5. Invoice No. --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="invoice_no" class="form-label">Invoice No.</label>
                                    <input type="text" name="invoice_no" id="invoice_no"
                                        class="form-control @error('invoice_no') is-invalid @enderror"
                                        value="{{ old('invoice_no') }}" required>
                                    @error('invoice_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 6. Invoice Date --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="invoice_date" class="form-label">Invoice Date</label>
                                    <input type="date" name="invoice_date" id="invoice_date"
                                        class="form-control @error('invoice_date') is-invalid @enderror"
                                        value="{{ old('invoice_date') }}" required>
                                    @error('invoice_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 7. Upload Invoice --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="invoice_file" class="form-label">Upload Invoice</label>
                                    <input type="file" name="invoice_file" id="invoice_file"
                                        class="form-control @error('invoice_file') is-invalid @enderror"
                                        accept=".pdf,.jpg,.jpeg,.png" required>
                                    @error('invoice_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 8. Purchase Amount --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="purchase_value" class="form-label">Amount (₹)</label>
                                    <input type="number" name="purchase_value" id="purchase_value"
                                        class="form-control @error('purchase_value') is-invalid @enderror"
                                        value="{{ old('purchase_value') }}" step="0.01" min="0" required>
                                    @error('purchase_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 9. Warranty --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="warranty_months" class="form-label">Warranty (Months)</label>
                                    <input type="number" name="warranty_months" id="warranty_months"
                                        class="form-control @error('warranty_months') is-invalid @enderror"
                                        value="{{ old('warranty_months') }}" min="0" max="120" required>
                                    @error('warranty_months')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Basic Asset Fields -->
                        <div id="basic-asset-fields">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="serial_number" class="form-label">Serial Number</label>
                                        <input type="text" name="serial_number" id="serial_number"
                                            class="form-control @error('serial_number') is-invalid @enderror"
                                            value="{{ old('serial_number') }}">
                                        @error('serial_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="model_number" class="form-label">Model Number</label>
                                        <input type="text" name="model_number" id="model_number"
                                            class="form-control @error('model_number') is-invalid @enderror"
                                            value="{{ old('model_number') }}">
                                        @error('model_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                 <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="model_number" class="form-label">AMC Expiry Date</label>
                                        <input type="date" name="amc_expiry_date" id="amc_expiry_date"
                                            class="form-control @error('amc_expiry_date') is-invalid @enderror"
                                            value="{{ old('amc_expiry_date') }}">
                                        @error('amc_expiry_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Component-Based Asset Fields -->
                        <div id="component-asset-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="component_serial_number" class="form-label">Asset Set Serial
                                            Number</label>
                                        <input type="text" name="component_serial_number" id="component_serial_number"
                                            class="form-control @error('component_serial_number') is-invalid @enderror"
                                            value="{{ old('component_serial_number') }}">
                                        @error('component_serial_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="component_model_number" class="form-label">Asset Set Model</label>
                                        <input type="text" name="component_model_number" id="component_model_number"
                                            class="form-control @error('component_model_number') is-invalid @enderror"
                                            value="{{ old('component_model_number') }}">
                                        @error('component_model_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Fields -->
                        <div id="dynamic-fields-container"></div>

                        {{-- Notes --}}
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>

                        <div class="modal-footer">
                            <a href="{{ route('assets.stock') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Asset</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Asset Type Modal -->
    <div class="modal fade" id="assetTypeModal" tabindex="-1" aria-labelledby="assetTypeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assetTypeModalLabel">Create Asset Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="assetTypeForm">
                            @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type_name" class="form-label">Asset Type Name</label>
                                    <input type="text" name="name" id="type_name" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type_description" class="form-label">Description</label>
                                    <input type="text" name="description" id="type_description" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Brand Dropdown -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brand_id" class="form-label">Select Brand</label>
                                    <select name="brand_id" id="brand_id" class="form-control" required>
                                        <option value="">-- Select Brand --</option>
                                        @foreach ($assetbrand as $brand)
                                            <option value="{{ $brand->br_id }}">{{ $brand->br_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Category Dropdown -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Select Category</label>
                                    <select name="category_id" id="category_id" class="form-control" required>
                                        <option value="">-- Select Category --</option>
                                        @foreach ($assetcategory as $category)
                                            <option value="{{ $category->ac_id }}">{{ $category->ac_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveAssetType()">Create Asset Type</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Component Modal -->
    <div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="brandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="brandModalLabel">Create Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="brandForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="br_name" class="form-label">Brand Name</label>
                                    <input type="text" name="br_name" id="br_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="br_description" class="form-label">Description</label>
                                    <input type="text" name="br_description" id="br_description"
                                        class="form-control">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="saveBrand()">Save Brand</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add / Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="categoryForm">
                        <!-- Category Name -->
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="categoryName" name="name"
                                placeholder="Enter category name" required>
                        </div>

                        <!-- Category Description -->
                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="categoryDescription" name="description" rows="3"
                                placeholder="Enter category description"></textarea>
                        </div>

                        <!-- Hidden ID (for edit) -->
                        <input type="hidden" id="categoryId" name="id">
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" onclick="saveCategory()">Save Category</button>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }

            if (e.ctrlKey && e.key.toLowerCase() === 'j') {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        window.addEventListener('beforeunload', function(e) {
            if (document.activeElement && document.activeElement.tagName === 'INPUT') {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>


    <script>
        $(document).ready(function() {
            $('#asset_type_id').select2({
                placeholder: "Select Asset Type",
                allowClear: true,
                width: '100%' // Make it full width
            });
        });
    </script>

    <script>
        // Base URL for API endpoints
        const baseUrl = '{{ url('/') }}';

        // Utility function to escape HTML to prevent XSS
        function escapeHtml(str) {
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(
                /'/g, '&#039;');
        }

        $(document).ready(function() {
            console.log('Asset creation page loaded');

            // Initialize
            Promise.all([loadFieldTypes()]).then(() => {
                addSimpleField(); // Add initial field
                addComponentField(); // Add initial component field
                addCategoryOption(); // Add initial category option
            });

            // Asset type change handler
            // $('#asset_type_id').change(function() {
            //     const selectedTypeId = $(this).val();
            //     const container = $('#dynamic-fields-container');
            //     const basicFields = $('#basic-asset-fields');
            //     const componentFields = $('#component-asset-fields');

            //     // Clear previous content
            //     container.empty();
            //     basicFields.hide();
            //     componentFields.hide();

            //     if (!selectedTypeId) {
            //         return;
            //     }

            //     // Get asset type structure
            //     $.get(`${baseUrl}/asset-types/${selectedTypeId}/structure`)
            //         .done(function(response) {
            //             console.log('Asset type structure:', response);

            //             if (response.is_component_based) {
            //                 componentFields.show();
            //                 renderComponentFields(response.structure);
            //             } else {
            //                 basicFields.show();
            //                 renderSimpleFields(response.structure);
            //             }
            //         })
            //         .fail(function(xhr) {
            //             console.error('Failed to load asset type structure:', xhr);
            //             showToast('Error loading asset type structure', 'error');
            //         });
            // });

            // Structure type change handler
            $('input[name="structure_type"]').change(function() {
                const structureType = $(this).val();
                if (structureType === 'simple') {
                    $('#simple-fields-section').show();
                    $('#component-fields-section').hide();
                } else {
                    $('#simple-fields-section').hide();
                    $('#component-fields-section').show();
                }
            });
        });

        // Global variables
        let fieldTypes = [];
        let components = [];
        let simpleFieldIndex = 0;
        let componentFieldIndex = 0;
        let categoryOptionIndex = 0;

        // Load field types
        function loadFieldTypes() {
            return $.get(`${baseUrl}/api/field-types`)
                .done(function(data) {
                    fieldTypes = data;
                    console.log('Field types loaded:', fieldTypes.length);
                    if (fieldTypes.length === 0) {
                        showToast('No field types available. Please contact support.', 'error');
                    }
                })
                .fail(function(xhr) {
                    console.error('Failed to load field types:', xhr);
                    showToast('Failed to load field types', 'error');
                    fieldTypes = []; // Ensure fieldTypes is defined even on failure
                });
        }

        // Load components
       

        // Load components for selection
     
        // Add simple field
        function addSimpleField(oldData = null) {
            let html = `
        <div class="field-row mb-3 p-3 border rounded" data-index="${simpleFieldIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-secondary mb-0">Field ${simpleFieldIndex + 1}</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSimpleField(${simpleFieldIndex})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Field Name</label>
                        <input type="text" name="fields[${simpleFieldIndex}][name]" class="form-control" 
                               placeholder="e.g., Brand, Model" value="${oldData ? escapeHtml(oldData.name || '') : ''}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Field Type</label>
                        <select name="fields[${simpleFieldIndex}][field_type_id]" class="form-select field-type-select" required>
                            <option value="">Select Type</option>
                            ${fieldTypes.length === 0 ? '<option value="" disabled>No field types available</option>' : ''}
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Required</label>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="fields[${simpleFieldIndex}][is_required]" 
                                   class="form-check-input" value="1" ${oldData && oldData.is_required ? 'checked' : 'checked'}>
                            <label class="form-check-label">Required field</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

            $('#simple-fields-container').append(html);

            // Populate field types for this field
            const select = $(`select[name="fields[${simpleFieldIndex}][field_type_id]"]`);
            fieldTypes.forEach(function(type) {
                const selected = oldData && oldData.field_type_id == type.id ? 'selected' : '';
                select.append(`<option value="${type.id}" ${selected}>${escapeHtml(type.label)}</option>`);
            });

            simpleFieldIndex++;
        }

        // Remove simple field
        function removeSimpleField(index) {
            const fieldRows = $('.field-row').length;
            if (fieldRows <= 1) {
                showToast('At least one field is required', 'error');
                return;
            }
            $(`.field-row[data-index="${index}"]`).remove();
        }

        // Add component field
        function addComponentField(oldData = null) {
            let html = `
        <div class="component-field-row mb-3 p-3 border rounded" data-index="${componentFieldIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-secondary mb-0">Field ${componentFieldIndex + 1}</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeComponentField(${componentFieldIndex})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Field Name</label>
                        <input type="text" name="fields[${componentFieldIndex}][name]" class="form-control" 
                               placeholder="e.g., Brand, Model" value="${oldData ? escapeHtml(oldData.name || '') : ''}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Field Type</label>
                        <select name="fields[${componentFieldIndex}][field_type_id]" class="form-select" required>
                            <option value="">Select Type</option>
                            ${fieldTypes.length === 0 ? '<option value="" disabled>No field types available</option>' : ''}
                        </select>
                    </div>
                </div>
            </div>
        </div>
    `;

            $('#component-fields-container').append(html);

            // Populate field types for this field
            const select = $(`select[name="fields[${componentFieldIndex}][field_type_id]"]`);
            fieldTypes.forEach(function(type) {
                const selected = oldData && oldData.field_type_id == type.id ? 'selected' : '';
                select.append(`<option value="${type.id}" ${selected}>${escapeHtml(type.label)}</option>`);
            });

            componentFieldIndex++;
        }

        // Remove component field
        function removeComponentField(index) {
            const fieldRows = $('.component-field-row').length;
            if (fieldRows <= 1) {
                showToast('At least one field is required', 'error');
                return;
            }
            $(`.component-field-row[data-index="${index}"]`).remove();
        }

        // Add category option
        function addCategoryOption(oldData = null) {
            let html = `
        <div class="option-row mb-2" data-index="${categoryOptionIndex}">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="options[${categoryOptionIndex}][value]" class="form-control" 
                           placeholder="Value (e.g., dell)" value="${oldData ? escapeHtml(oldData.value || '') : ''}" required>
                </div>
                <div class="col-md-5">
                    <input type="text" name="options[${categoryOptionIndex}][label]" class="form-control" 
                           placeholder="Label (e.g., Dell)" value="${oldData ? escapeHtml(oldData.label || '') : ''}" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCategoryOption(${categoryOptionIndex})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

            $('#category-options-container').append(html);
            categoryOptionIndex++;
        }

        // Remove category option
        function removeCategoryOption(index) {
            const optionRows = $('.option-row').length;
            if (optionRows <= 1) {
                showToast('At least one option is required', 'error');
                return;
            }
            $(`.option-row[data-index="${index}"]`).remove();
        }

        // Save asset type
        function saveAssetType() {
            $.ajax({
                url: "{{ route('asset-types.store') }}", 
                type: "POST",
                data: $('#assetTypeForm').serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $('#assetTypeForm')[0].reset();
                            $('#assetTypeModal').modal('hide');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Something went wrong',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = "Something went wrong!";
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).join("\n");
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        // Save component
        function saveComponent() {
            console.log('Save Component clicked');

            const form = document.getElementById('componentForm');
            const formData = new FormData(form);

            // Log form data
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: `${baseUrl}/api/components`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Component created successfully:', response);
                    showToast('Component created successfully!', 'success');
                    $('#componentModal').modal('hide');
                    resetComponentForm();
                },
                error: function(xhr) {
                    console.error('Error creating component:', xhr);
                    const errorMsg = xhr.responseJSON?.message || 'Error creating component';
                    showToast(escapeHtml(errorMsg), 'error');
                }
            });
        }

        function saveCategory() {
            let formData = {
                name: $('#categoryName').val(),
                description: $('#categoryDescription').val(),
                _token: '{{ csrf_token() }}'
            };

            // Add id only if it exists (update mode)
            if ($('#categoryId').val()) {
                formData.id = $('#categoryId').val();
            }

            $.ajax({
                url: '{{ route('categories.save') }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $('#categoryForm')[0].reset();
                            $('#categoryId').val('');
                            $('#categoryModal').modal('hide');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Something went wrong',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'An error occurred. Please try again.';
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        errorMsg = Object.values(errors).map(err => err.join(', ')).join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: errorMsg,
                        confirmButtonText: 'OK'
                    });
                }
            });
        }


        function saveBrand() {
            $.ajax({
                url: "{{ route('brands.store') }}",
                type: "POST",
                data: $('#brandForm').serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $('#brandForm')[0].reset();
                            $('#brandModal').modal('hide');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Something went wrong',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = "Something went wrong!";
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).join("\n");
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            });
        }


        // Refresh asset types dropdown
        function refreshAssetTypes() {
            $.get(`${baseUrl}/api/asset-types`)
                .done(function(data) {
                    const select = $('#asset_type_id');
                    const currentValue = select.val();

                    select.empty().append('<option value="">Select Asset Type</option>');

                    data.forEach(function(type) {
                        select.append(`<option value="${type.id}">${escapeHtml(type.name)}</option>`);
                    });

                    if (currentValue) {
                        select.val(currentValue);
                    }

                    console.log('Asset types refreshed:', data.length);
                })
                .fail(function(xhr) {
                    console.error('Failed to refresh asset types:', xhr);
                    showToast('Failed to refresh asset types', 'error');
                });
        }

        // Reset forms
        function resetAssetTypeForm() {
            document.getElementById('assetTypeForm').reset();
            $('#simple-fields-container').empty();
            $('#components-container').empty();
            simpleFieldIndex = 0;
            addSimpleField();
        }

        function resetComponentForm() {
            document.getElementById('componentForm').reset();
            $('#component-fields-container').empty();
            componentFieldIndex = 0;
            addComponentField();
        }

        function resetCategoryForm() {
            document.getElementById('categoryForm').reset();
            $('#category-options-container').empty();
            categoryOptionIndex = 0;
            addCategoryOption();
        }

        // Render dynamic fields
        function renderSimpleFields(fields) {
            let html =
                '<div class="row"><div class="col-md-12"><h6 class="text-primary mb-3">Specifications</h6></div></div><div class="row">';

            fields.forEach(function(field, index) {
                if (index > 0 && index % 2 === 0) {
                    html += '</div><div class="row">';
                }

                html += `
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="specifications_${field.slug}" class="form-label">${escapeHtml(field.name)}</label>
        `;

                if (field.html_type === 'select') {
                    html +=
                        `<select name="specifications[${field.slug}]" id="specifications_${field.slug}" class="form-select" ${field.is_required ? 'required' : ''}>`;
                    html += `<option value="">Select ${escapeHtml(field.name)}</option>`;
                    Object.entries(field.options).forEach(([value, label]) => {
                        const selected = old('specifications.' + field.slug) === value ? 'selected' : '';
                        html +=
                            `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
                    });
                    html += `</select>`;
                } else {
                    html += `<input type="${field.html_type === 'input' ? 'text' : field.html_type}" 
                            name="specifications[${field.slug}]" 
                            id="specifications_${field.slug}" 
                            class="form-control" 
                            placeholder="${escapeHtml(field.name)}"
                            value="${old('specifications.' + field.slug) ? escapeHtml(old('specifications.' + field.slug)) : ''}"
                            ${field.is_required ? 'required' : ''}>`;
                }

                html += `
                </div>
            </div>
        `;
            });

            html += '</div>';
            $('#dynamic-fields-container').html(html);
        }

        function renderComponentFields(components) {
            let html = '';

            components.forEach(function(component, componentIndex) {
                html += `
            <div class="component-section mb-4 p-3 border rounded">
                <h6 class="text-primary mb-3">${escapeHtml(component.name)}</h6>
                <div class="row">
        `;

                component.fields.forEach(function(field) {
                    const fieldName = `components[${componentIndex}][${field.slug}]`;

                    html += `
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">${escapeHtml(field.name)}</label>
            `;

                    if (field.html_type === 'selectDir') {
                        html +=
                            `<select name="${fieldName}" class="form-select" ${field.is_required ? 'required' : ''}>`;
                        html += `<option value="">Select ${escapeHtml(field.name)}</option>`;
                        Object.entries(field.options).forEach(([value, label]) => {
                            const selected = old('components.' + componentIndex + '.' + field
                                .slug) === value ? 'selected' : '';
                            html +=
                                `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
                        });
                        html += `</select>`;
                    } else {
                        html += `<input type="${field.html_type === 'input' ? 'text' : field.html_type}" 
                                name="${fieldName}" 
                                class="form-control" 
                                placeholder="${escapeHtml(field.name)}"
                                value="${old('components.' + componentIndex + '.' + field.slug) ? escapeHtml(old('components.' + componentIndex + '.' + field.slug)) : ''}"
                                ${field.is_required ? 'required' : ''}>`;
                    }

                    html += `
                    </div>
                </div>
            `;
                });

                html += `
                </div>
            </div>
        `;
            });

            $('#dynamic-fields-container').html(html);
        }

        // Toast notification function
        function showToast(message, type = 'info') {
            // Remove existing toasts to prevent stacking
            $('.toast').remove();

            const toastClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-info');
            const toast = `
        <div class="toast align-items-center text-white ${toastClass} border-0" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">${escapeHtml(message)}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

            $('body').append(toast);
            $('.toast').last().toast('show');

            // Remove toast after 5 seconds
            setTimeout(function() {
                $('.toast').last().remove();
            }, 5000);
        }

        // Restore old input values for dynamic fields (if any)
        $(document).ready(function() {
            @if (old('fields'))
                @foreach (old('fields') as $index => $field)
                    addSimpleField({{ json_encode($field) }});
                @endforeach
            @endif

            @if (old('options'))
                @foreach (old('options') as $index => $option)
                    addCategoryOption({{ json_encode($option) }});
                @endforeach
            @endif

            @if (old('fields') && request()->is('components*'))
                @foreach (old('fields') as $index => $field)
                    addComponentField({{ json_encode($field) }});
                @endforeach
            @endif
        });

        $('#invoice').on('change', function() {
            let formData = new FormData();
            formData.append('invoice', this.files[0]);

            fetch('{{ route('parse.invoice') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .then(res => res.json())
                .then(data => {
                    let f = data.extracted;

                    $('#purchase_no').val(f.purchase_no);
                    $('#purchase_from').val(f.vendor);
                    $('#invoice_no').val(f.invoice_no);
                    $('#purchase_date').val(f.purchase_date);
                    $('#invoice_date').val(f.invoice_date);
                    $('#purchase_value').val(f.purchase_value);
                    $('#warranty').val(f.warranty_months);
                });
        });
    </script>
@endpush
