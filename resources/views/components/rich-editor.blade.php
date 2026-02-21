@props([
    'initialData' => '',
    'initialTitle' => '',
    'placeholder' => 'Start writing...',
    'statusText' => 'Start Typing',
    'titlePlaceholder' => 'Optional Title',
    'showValidationError' => false,
])

<div {{ $attributes->merge(['class' => 'rich-editor-shell']) }}>
    <div class="form-group mb-3">
        <div id="data-editor" data-placeholder="{{ $placeholder }}"></div>
        <textarea id="data" name="data" class="d-none">{{ $initialData }}</textarea>
        @if($showValidationError)
            @error('data')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        @endif
    </div>

    <div class="form-group">
        <input type="text"
               id="title"
               name="title"
               maxlength="255"
               class="form-control"
               value="{{ $initialTitle }}"
               placeholder="{{ $titlePlaceholder }}">
    </div>

    <div class="bottom-panel">
        <div id="save-status" class="badge badge-secondary">{{ $statusText }}</div>
        <div>
            {{ $actions ?? '' }}
        </div>
    </div>
</div>
