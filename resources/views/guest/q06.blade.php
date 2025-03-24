<div class="option01 required hidden-items {{ old('q01_a4') ? 'active' : null }}">
    <fieldset>
        <legend class="pb-6 flex">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-red-600 rounded">必須</span>
            <span class="block">炒飯について</span>
        </legend>
        <div class="radio-buttons">
            <input type="radio" name="q06" value="未選択" class="fried-rice @error('q06') err @enderror" checked {{ old('q06') == '未選択' ? 'checked' : '' }} />
            <label class="">
                <input type="radio" name="q06" value="非常に満足した" {{ old('q06') == '非常に満足した' ? 'checked' : '' }} />
                <span class="radio-label">非常に満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q06" value="満足した" {{ old('q06') == '満足した' ? 'checked' : '' }} />
                <span class="radio-label">満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q06" value="どちらでもない" {{ old('q06') == 'どちらでもない' ? 'checked' : '' }} />
                <span class="radio-label">どちらでもない</span>
            </label>
            <label class="">
                <input type="radio" name="q06" value="満足できなかった" {{ old('q06') == '満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">満足できなかった</span>
            </label>
            <label class="">
                <input type="radio" name="q06" value="まったく満足できなかった" {{ old('q06') == 'まったく満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">まったく満足できなかった</span>
            </label>
        </div>
    </fieldset>
    <p class="text-red-600 flashing @error('q06') mb-2 @enderror">@error('q06') ※選択肢の中から1つお選びください @enderror</p>
</div>