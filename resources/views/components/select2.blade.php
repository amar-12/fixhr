<!-- resources/views/components/select.blade.php -->
<div class="form-group">
    <label class="form-label" for="{{ $id }}">{{ $label }} <span style="color:red;">*</span></label>
    <select name="{{ $name }}" id="{{ $id }}" class="form-control custom-select select2 {{ $class ?? '' }}">
        <option value="" selected disabled>{{ $placeholder ?? 'Select an option' }}</option>
        @foreach($options as $value => $option)
            <option value="{{ $value }}" {{ (isset($selected) && $selected == $value) ? 'selected' : '' }}>{{ $option }}</option>
        @endforeach
    </select>
    <span class="text-danger text-danger-select2" id="{{ $id }}_error"></span>
</div>
