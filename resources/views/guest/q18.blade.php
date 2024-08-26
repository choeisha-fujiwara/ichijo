<div class="pb-4">
    <fieldset>
        <legend class="pb-6 flex justify-start items-start">
            <span class="flex justify-center items-center whitespace-nowrap mr-2 pt-1 pb-1.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
            <span class="block text-justify">神座の公式SNSをフォローしていますか？</span>
        </legend>
        <div class="radio-buttons flex flex-wrap justify-start items-center">
            <label class="mr-4">
                <input type="radio" name="q18" value="している" {{ old('q18') == 'している' ? 'checked' : '' }} />
                <span class="radio-label">している</span>
            </label>
            <label class="">
                <input type="radio" name="q18" value="していない" {{ old('q18') == 'していない' ? 'checked' : '' }} />
                <span class="radio-label">していない</span>
            </label>
        </div>
    </fieldset>
</div>