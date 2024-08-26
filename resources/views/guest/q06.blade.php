<div class="option01 required hidden-items">
    <fieldset>
        <legend class="pb-6 flex">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-red-600 rounded">必須</span>
            <span class="block">炒飯について</span>
        </legend>
        <div class="radio-buttons">
            <label class="">
                <input type="radio" name="q06" value="非常に満足した" {{ old('q04') == '非常に満足した' ? 'checked' : '' }} />
                <span class="radio-label">非常に満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q06" value="満足した" {{ old('q04') == '満足した' ? 'checked' : '' }} />
                <span class="radio-label">満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q06" value="どちらでもない" {{ old('q04') == 'どちらでもない' ? 'checked' : '' }} />
                <span class="radio-label">どちらでもない</span>
            </label>
            <label class="">
                <input type="radio" name="q06" value="満足できなかった" {{ old('q04') == '満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">満足できなかった</span>
            </label>
            <label class="">
                <input type="radio" name="q06" value="まったく満足できなかった" {{ old('q04') == 'まったく満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">まったく満足できなかった</span>
            </label>
        </div>
    </fieldset>
</div>