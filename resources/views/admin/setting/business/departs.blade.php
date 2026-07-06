@extends('admin.layout.master')

@section('title', 'Department Settings')

{{-- @section('script')

@endsection --}}

@section('content')
<div class="container">
    <h2>Add Employee</h2>
    <form action="{{url('save-new-desig')}}" method="POST">
        @csrf

        <!-- Department Dropdown -->
        <div class="form-group">
            <label for="department">Department</label>
            <select name="department_id" id="department" class="form-control" required>
                <option value="" disabled selected>Select Department</option>
                @foreach($departments as $department)
                    <option value="{{ $department->dept_id }}">{{ $department->dept_name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Designation Multi-input Field -->
        <div class="form-group">
            <label for="designations">Designations</label>
            <div id="designation-wrapper">
                <div class="input-group mb-2 designation-group">
                    <select name="designations[]" class="form-control designation-select">
                        <option value="" disabled selected>Select Designation</option>
                        @foreach($designations as $designation)
                            <option value="{{ $designation->desig_id }}">{{ $designation->desig_name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="new_designations[]" class="form-control ml-2" placeholder="Or add new designation">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-success add-designation">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-outline-primary">Submit</button>
    </form>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle adding new designation field
        document.querySelector('.add-designation').addEventListener('click', function () {
            var newDesignationField = `
                <div class="input-group mb-2 designation-group">
                    <select name="designations[]" class="form-control designation-select">
                        <option value="" disabled selected>Select Designation</option>
                        @foreach($designations as $designation)
                            <option value="{{ $designation->desig_id }}">{{ $designation->desig_name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="new_designations[]" class="form-control ml-2" placeholder="Or add new designation">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-danger  remove-designation">-</button>
                    </div>
                </div>
            `;
            document.getElementById('designation-wrapper').insertAdjacentHTML('beforeend', newDesignationField);

            // Handle removal of a designation field
            document.querySelectorAll('.remove-designation').forEach(function (button) {
                button.addEventListener('click', function () {
                    this.closest('.designation-group').remove();
                });
            });
        });
    });
</script>
@endsection
