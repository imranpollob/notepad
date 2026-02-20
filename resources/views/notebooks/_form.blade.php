@csrf
@if(isset($notebook))
    @method('put')
@endif

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" class="form-control" id="name" name="name" required maxlength="255"
           value="{{ old('name', $notebook->name ?? '') }}">
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea class="form-control" id="description" name="description" rows="3" maxlength="2000">{{ old('description', $notebook->description ?? '') }}</textarea>
</div>

<div class="form-group">
    <label for="visibility">Visibility</label>
    <select class="form-control" id="visibility" name="visibility">
        @php($selectedVisibility = old('visibility', $notebook->visibility ?? 'private'))
        <option value="private" {{ $selectedVisibility === 'private' ? 'selected' : '' }}>Private</option>
        <option value="unlisted" {{ $selectedVisibility === 'unlisted' ? 'selected' : '' }}>Unlisted (share link only)</option>
        <option value="public" {{ $selectedVisibility === 'public' ? 'selected' : '' }}>Public (shareable)</option>
    </select>
</div>

<button type="submit" class="btn btn-dark btn-sm px-3">{{ isset($notebook) ? 'Update Notebook' : 'Create Notebook' }}</button>
