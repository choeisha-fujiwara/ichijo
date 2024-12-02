<div class="pb-4">
    <fieldset>
        <legend class="pb-6 flex">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-red-600 rounded">必須</span>
            <span class="block">{{ $shop->shop_category == '麺屋道頓' ? 'つけ麺' : 'ラーメン' }}の味について</span>
        </legend>
        <div class="radio-buttons">
            <label class="">
                <input type="radio" name="q02" value="非常に満足した" {{ old('q02') == '非常に満足した' ? 'checked' : '' }} required />
                <span class="radio-label w-full">非常に満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q02" value="満足した" {{ old('q02') == '満足した' ? 'checked' : '' }} />
                <span class="radio-label">満足した</span>
            </label>
            <label class="">
                <input type="radio" name="q02" value="どちらでもない" {{ old('q02') == 'どちらでもない' ? 'checked' : '' }} />
                <span class="radio-label">どちらでもない</span>
            </label>
            <label class="">
                <input type="radio" name="q02" value="満足できなかった" {{ old('q02') == '満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">満足できなかった</span>
            </label>
            <label class="">
                <input type="radio" name="q02" value="まったく満足できなかった" {{ old('q02') == 'まったく満足できなかった' ? 'checked' : '' }} />
                <span class="radio-label">まったく満足できなかった</span>
            </label>
        </div>
    </fieldset>
    <p class="text-red-600 flashing @error('q02') mb-2 @enderror">@error('q02') ※選択肢の中から1つお選びください @enderror</p>
</div>