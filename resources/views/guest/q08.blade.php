<div class="option02 hidden-items {{ old('q01_a2') ? 'active' : null }}">
    <fieldset>
        <legend class="pb-6 flex">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
            <span class="block">餃子の味について</span>
        </legend>
        <div class="radio-buttons gyoza">
            <label class="">
                <input type="radio" name="q08" value="非常に満足した" {{ old('q08') == '非常に満足した' ? 'checked' : '' }} />
                <span class="radio-label">非常に満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q08" value="満足した" {{ old('q08') == '満足した' ? 'checked' : '' }} />
                <span class="radio-label">満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q08" value="どちらでもない" {{ old('q08') == 'どちらでもない' ? 'checked' : '' }} />
                <span class="radio-label">どちらでもない</span>
            </label>
            <label class="">
                <input type="radio" name="q08" value="満足できなかった" {{ old('q08') == '満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">満足できなかった</span>
            </label>
            <label class="">
                <input type="radio" name="q08" value="まったく満足できなかった" {{ old('q08') == 'まったく満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">まったく満足できなかった</span>
            </label>
        </div>
    </fieldset>
</div>