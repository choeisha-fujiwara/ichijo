<div class="pb-5">
    <fieldset>
        <legend class="inline-flex pb-6">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-red-600 rounded">必須</span>
            <span class="block">性別</span>
        </legend>
        <div class="radio-buttons flex flex-wrap justify-start items-center">
            <label class="w-1/4">
                <input type="radio" name="gender" value="男性" {{ old('gender') == '男性' ? 'checked' : '' }} required />
                <span class="radio-label">男性</span>
            </label>
            <label class="w-1/4">
                <input type="radio" name="gender" value="女性" {{ old('gender') == '女性' ? 'checked' : '' }} />
                <span class="radio-label">女性</span>
            </label>
            <label class="">
                <input type="radio" name="gender" value="答えたくない" {{ old('gender') == '答えたくない' ? 'checked' : '' }} />
                <span class="radio-label">答えたくない</span>
            </label>
        </div>
    </fieldset>
    <p class="text-red-600 flashing @error('gender') mb-2 @enderror">@error('gender') ※選択肢の中から1つお選びください @enderror</p>
</div>
