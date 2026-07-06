<div class="mb-3">
    <label for="{{ $id }}" class="form-label">
        {{ $label }}
        @isset($astric)
            <span style="color:red">*</span>
        @endisset
    </label>
    <select name="{{ $name }}" id="{{ $id }}" class="form-select {{$class ?? ''}}"
            @isset($required) required @endisset
            @isset($multiple) multiple @endisset>
            <option value="" @isset($multiple) disabled @endisset >Select {{$label}}</option>
        @foreach ($options as $key => $option)
            <option value="{{ $key }}" @if(isset($selected) && $selected == $key) selected @endif>
                {{ $option }}
            </option>
        @endforeach
    </select>
    <span class="text-danger" id="{{ $id }}_error"></span>
    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
