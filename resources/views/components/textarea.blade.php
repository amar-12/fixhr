<div class="form-group">
    <label for="{{ $id }}" class="form-label">{{ $label }}<span class="" style="color: red;">&nbsp;{{$astric ?? ''}}</span></label>
    <textarea class="form-control" id="{{ $id }}" name="{{ $name }}" {{ $attributes }}>{{ $value ?? '' }}</textarea>
    <span class="text-danger" id="{{ $id }}_error"></span>
    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
