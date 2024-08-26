<div class="pb-3">
    <fieldset>
        <legend class="pb-6 flex">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-red-600 rounded">必須</span>
            <span class="block">接客について</span>
        </legend>
        <div class="radio-buttons">
            <label class="">
                <input type="radio" name="q09" value="非常に満足した" {{ old('q09') == '非常に満足した' ? 'checked' : '' }} required />
                <span class="radio-label">非常に満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q09" value="満足した" {{ old('q09') == '満足した' ? 'checked' : '' }} />
                <span class="radio-label">満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q09" value="どちらでもない" {{ old('q09') == 'どちらでもない' ? 'checked' : '' }} />
                <span class="radio-label">どちらでもない</span>
            </label>
            <label class="">
                <input type="radio" name="q09" value="満足できなかった" {{ old('q09') == '満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">満足できなかった</span>
            </label>
            <label class="">
                <input type="radio" name="q09" value="まったく満足できなかった" {{ old('q09') == 'まったく満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">まったく満足できなかった</span>
            </label>
        </div>
    </fieldset>
    <p class="text-red-600 flashing @error('q09') mb-2 @enderror">@error('q09') ※選択肢の中から1つお選びください @enderror</p>
</div>