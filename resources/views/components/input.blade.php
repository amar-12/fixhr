<!-- resources/views/components/input.blade.php -->
<div class="form-group {{ $groupClass ?? '' }}">
    <label for="{{ $id }}" class="form-label text-nowrap">{{ $label }}<span class="" style="color: red;">&nbsp;{{$astric ?? ''}}</span></label>
    <input type="{{ $type ?? 'text' }}" name="{{ $name }}" id="{{ $id }}" value="{{ $value ?? '' }}"
        class="form-control {{ $class ?? '' }}" placeholder="{{ $placeholder ?? '' }}" {{ $attributes }}>
    <span class="text-danger" id="{{ $id }}_error"></span>
    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
