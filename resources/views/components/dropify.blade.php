<!-- resources/views/components/dropify.blade.php -->
<div class="form-group">
    <label class="form-label" style="text-align: center;">
        <b>{{ $label }}<span class="text-danger"> {{ $astric ? '*' : '' }}</span></b>
    </label>

    <input type="file" name="{{ $name ?? 'file' }}" class="dropify {{ $class ?? '' }}"
           data-height="{{ $height ?? 100 }}" accept="{{ $accept ?? '.jpg, .png, image/jpeg, image/png' }}"
           id="{{ $id ?? 'dropifyInput' }}"
           data-width="{{ $width ?? 300 }}"
           {{ $required ? 'required' : '' }} />
           <span class="text-danger" id="{{ $id }}_error"></span>

    {{-- <span id="{{ $id ?? 'fileError' }}" class="text-danger"></span> --}}

    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
<span><span class="fw-bold">Note: </span> The standard image formats should be JPG, JPEG, and PNG Only.</span>
