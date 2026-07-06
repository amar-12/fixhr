@extends('admin.layout.master')
@section('title', 'Dynamic Form Builder')

@section('content')
    <div class="container mt-5">
        <h2 class="text-center text-primary">📝 Dynamic Form Builder</h2>

        <form action="{{ route('forms.store') }}" method="POST" id="dynamicForm" enctype="multipart/form-data">
            @csrf
            <div class="card p-4 mt-4">



                <div class="mb-3">
                    <label class="form-label fw-bold">Form Name</label>
                    <input type="text" name="form_name" id="formName"
                        class="form-control @error('form_name') is-invalid @enderror" placeholder="Enter Form Name"
                        value="{{ old('form_name') }}" required>

                    @error('form_name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>


                <h4 class="text-secondary">📌 Sections</h4>
                <div id="sections-container">
                    <!-- Sections will be added here dynamically -->
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="addSection()">➕ Add
                        Section</button>
                    <button type="submit" class="btn btn-outline-primary btn-sm">💾 Save Form</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('style')
    <style>
        .section-header {
            display: flex;
            justify-content: space-between;
        }

        .field-controls button {
            margin-left: 5px;
        }

        .fields-container {
            margin-top: 15px;
        }
    </style>
@endsection

@section('script')
    <script>
        let sectionCount = 0;

        // Function to add a new section
        function addSection() {
            let sectionId = `section_${sectionCount++}`;
            let sectionDiv = document.createElement('div');
            sectionDiv.className = 'section card p-3 mt-3 position-relative';
            sectionDiv.setAttribute('data-section-id', sectionId);

            sectionDiv.innerHTML = `
                <div class="section-header">
                    <input type="text" name="sections[${sectionId}][section_name]" class="form-control w-50" placeholder="Section Name" required>
                    <div class="field-controls">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addField('${sectionId}')">➕</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="removeSection('${sectionId}')">❌</button>
                    </div>
                </div>
                <div class="fields-container mt-3"></div>
            `;
            document.getElementById("sections-container").appendChild(sectionDiv);
        }

        // Function to remove a section
        function removeSection(sectionId) {
            document.querySelector(`.section[data-section-id='${sectionId}']`).remove();
        }

        // Function to add a new field within a section
        function addField(sectionId) {
            const fieldsContainer = document.querySelector(`.section[data-section-id='${sectionId}'] .fields-container`);
            const fieldIndex = fieldsContainer.children.length;

            let fieldDiv = document.createElement('div');
            fieldDiv.className = 'row mt-2 align-items-center p-2 bg-light rounded';

            fieldDiv.innerHTML = `
                <div class="col-md-2">
                    <input type="text" name="sections[${sectionId}][fields][${fieldIndex}][field_name]" class="form-control" placeholder="Field Name" required>
                </div>

                <div class="col-md-2">
                    <input type="text" name="sections[${sectionId}][fields][${fieldIndex}][colume_name]" class="form-control" placeholder="Column Name" required>
                </div>


                <div class="col-md-2">
                    <select name="sections[${sectionId}][fields][${fieldIndex}][field_type]" class="form-select" onchange="handleFieldTypeChange(this, '${sectionId}', ${fieldIndex})" required>
                        <option value="text">Text</option>
                        <option value="password">Password</option>
                        <option value="email">Email</option>
                        <option value="number">Number</option>
                        <option value="textarea">Textarea</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio</option>
                        <option value="date">Date</option>
                        <option value="select">Dropdown (Select)</option>
                        <option value="file">Image/File Upload</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="text" name="sections[${sectionId}][fields][${fieldIndex}][placeholder]" class="form-control" placeholder="Placeholder">
                </div>
                <div class="col-md-2">
                    <input type="checkbox" name="sections[${sectionId}][fields][${fieldIndex}][required]" class="form-check-input"> Required
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="removeField(this)">❌</button>
                </div>
                <div class="col-md-12 mt-2 additional-options" id="additional-options-${sectionId}-${fieldIndex}" style="display: none;">
                    <!-- Additional options will be dynamically inserted here based on field type -->
                </div>
            `;

            fieldsContainer.appendChild(fieldDiv);
        }

        // Function to remove a field
        function removeField(element) {
            element.closest(".row").remove();
        }

        // Function to handle changes in field type (show additional options)
        function handleFieldTypeChange(select, sectionId, fieldIndex) {
            const fieldType = select.value;
            const additionalOptionsDiv = document.getElementById(`additional-options-${sectionId}-${fieldIndex}`);

            // Clear previous options
            additionalOptionsDiv.innerHTML = '';

            // Handle additional options based on field type
            if (fieldType === 'select' || fieldType === 'radio' || fieldType === 'checkbox') {
                additionalOptionsDiv.innerHTML = `
                    <label class="form-label">Options</label>
                    <input type="text" name="sections[${sectionId}][fields][${fieldIndex}][options]" class="form-control" placeholder="Comma-separated options (e.g. Yes, No)">
                `;
            } else if (fieldType === 'file') {
                additionalOptionsDiv.innerHTML = `
                    <label class="form-label">File Accept Type (e.g. image/*)</label>
                    <input type="text" name="sections[${sectionId}][fields][${fieldIndex}][accept]" class="form-control" placeholder="Enter file types (e.g. image/*)">
                `;
            }

            additionalOptionsDiv.style.display = 'block';
        }
    </script>
@endsection
