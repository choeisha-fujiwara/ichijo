<div class="pb-4">
    <fieldset>
        <legend class="pb-6 flex">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
            <span class="block">チャーシューの味について</span>
        </legend>
        <div class="radio-buttons">
            <label class="">
                <input type="radio" name="q04" value="非常に満足した" {{ old('q04') == '非常に満足した' ? 'checked' : '' }} />
                <span class="radio-label">非常に満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q04" value="満足した" {{ old('q04') == '満足した' ? 'checked' : '' }} />
                <span class="radio-label">満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q04" value="どちらでもない" {{ old('q04') == 'どちらでもない' ? 'checked' : '' }} />
                <span class="radio-label">どちらでもない</span>
            </label>
            <label class="">
                <input type="radio" name="q04" value="満足できなかった" {{ old('q04') == '満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">満足できなかった</span>
            </label>
            <label class="">
                <input type="radio" name="q04" value="まったく満足できなかった" {{ old('q04') == 'まったく満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">まったく満足できなかった</span>
            </label>
        </div>
    </fieldset>
    <p class="text-red-600 flashing @error('q04') mb-2 @enderror">@error('q04') ※選択肢の中から1つお選びください @enderror</p>
</div>