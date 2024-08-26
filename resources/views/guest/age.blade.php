<div class="pb-3">
    <fieldset>
        <legend class="inline-flex pb-6">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-red-600 rounded">必須</span>
            <span class="block">年代</span>
        </legend>
        <div class="radio-buttons flex flex-wrap justify-between items-center">
            <label class="w-1/3 pb-1">
                <input type="radio" name="age" value="10代" {{ old('age') == '10代' ? 'checked' : '' }} required />
                <span class="radio-label">10代</span>
            </label>
            <label class="w-1/3 pb-1">
                <input type="radio" name="age" value="20代" {{ old('age') == '20代' ? 'checked' : '' }} />
                <span class="radio-label">20代</span>
            </label>
            <label class="w-1/3 pb-1">
                <input type="radio" name="age" value="30代" {{ old('age') == '30代' ? 'checked' : '' }} />
                <span class="radio-label">30代</span>
            </label>
            <label class="w-1/3 pb-1">
                <input type="radio" name="age" value="40代" {{ old('age') == '40代' ? 'checked' : '' }} />
                <span class="radio-label">40代</span>
            </label>
            <label class="w-1/3 pb-1">
                <input type="radio" name="age" value="50代" {{ old('age') == '50代' ? 'checked' : '' }} />
                <span class="radio-label">50代</span>
            </label>
            <label class="w-1/3 pb-1">
                <input type="radio" name="age" value="60代〜" {{ old('age') == '60代〜' ? 'checked' : '' }} />
                <span class="radio-label">60代〜</span>
            </label>
            <label class="pb-1 mr-auto">
                <input type="radio" name="age" value="答えたくない" {{ old('age') == '答えたくない' ? 'checked' : '' }} />
                <span class="radio-label">答えたくない</span>
            </label>
        </div>
    </fieldset>
    <p class="text-red-600 flashing @error('gender') mb-2 -mt-1 @enderror">@error('gender') ※選択肢の中から1つお選びください @enderror</p>
</div>