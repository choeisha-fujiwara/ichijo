<div class="pb-4">
    <fieldset>
        <legend class="inline-flex pb-6">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-red-600 rounded">必須</span>
            <span class="block">直近のご来店はいつでしたか？</span>
        </legend>
        <div class="radio-buttons flex flex-wrap justify-start items-center">
            <label class="w-1/2 mr-2">
                <input type="radio" name="q12" value="初めて来店" {{ old('q12') == '初めて来店' ? 'checked' : '' }} required />
                <span class="radio-label">初めて来店</span>
            </label>
            <label class="">
                <input type="radio" name="q12" value="1か月以内" {{ old('q12') == '1か月以内' ? 'checked' : '' }} />
                <span class="radio-label">1か月以内</span>
            </label>
            <label class="w-1/2 mr-2">
                <input type="radio" name="q12" value="3か月以内" {{ old('q12') == '3か月以内' ? 'checked' : '' }} />
                <span class="radio-label">3か月以内</span>
            </label>
            <label class="">
                <input type="radio" name="q12" value="半年以内" {{ old('q12') == '半年以内' ? 'checked' : '' }} />
                <span class="radio-label">半年以内</span>
            </label>
            <label class="w-1/2 mr-2">
                <input type="radio" name="q12" value="1年以内" {{ old('q12') == '1年以内' ? 'checked' : '' }} />
                <span class="radio-label">1年以内</span>
            </label>
            <label class="">
                <input type="radio" name="q12" value="1年以上前" {{ old('q12') == '1年以上前' ? 'checked' : '' }} />
                <span class="radio-label">1年以上前</span>
            </label>
        </div>
    </fieldset>
    <p class="text-red-600 flashing @error('q12') mb-2 @enderror">@error('q12') ※選択肢の中から1つお選びください @enderror</p>
</div>