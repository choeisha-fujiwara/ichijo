<div class="pb-4">
    <fieldset>
        <legend class="pb-6 flex justify-start items-start">
            <span class="flex justify-center items-center whitespace-nowrap mr-2 pt-1 pb-1.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
            <span class="block text-justify">神座をあなたの友人や知人にお勧めしたいと思いますか？</span>
        </legend>
        <div class="radio-buttons">
            <label class="">
                <input type="radio" name="q14" value="非常にそう思う" {{ old('q14') == '非常にそう思う' ? 'checked' : '' }} />
                <span class="radio-label">非常にそう思う</span>
            </label>
            <label class="">
                <input type="radio" name="q14" value="そう思う" {{ old('q14') == 'そう思う' ? 'checked' : '' }} />
                <span class="radio-label">そう思う</span>
            </label>
            <label class="">
                <input type="radio" name="q14" value="なんともいえない" {{ old('q14') == 'なんともいえない' ? 'checked' : '' }} />
                <span class="radio-label">なんともいえない</span>
            </label>
            <label class="">
                <input type="radio" name="q14" value="そう思わない" {{ old('q14') == 'そう思わない' ? 'checked' : '' }} />
                <span class="radio-label">そう思わない</span>
            </label>
            <label class="">
                <input type="radio" name="q14" value="全く思わない" {{ old('q14') == '全く思わない' ? 'checked' : '' }} />
                <span class="radio-label">全く思わない</span>
            </label>
        </div>
    </fieldset>
</div>