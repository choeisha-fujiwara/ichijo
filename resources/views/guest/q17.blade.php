<div class="pb-4 pt-0.5">
    <fieldset>
        <legend class="pb-6 flex justify-start items-start">
            <span class="flex justify-center items-center whitespace-nowrap mr-2 pt-1 pb-1.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
            <span class="block text-justify">同伴者</span>
        </legend>
        <div class="checkbox-buttons flex flex-wrap justify-start items-center">
            <label class="w-1/2 mr-2 checkbox-label {{ is_array(old('q17')) && in_array('家族', old('q17')) ? 'checked' : '' }}">
                <input type="checkbox" name="q17[]" value="家族" {{ is_array(old('q17')) && in_array('家族', old('q17')) ? 'checked' : '' }} />
                <span class="">家族</span>
            </label>
            <label class="checkbox-label {{ is_array(old('q17')) && in_array('友人', old('q17')) ? 'checked' : '' }}">
                <input type="checkbox" name="q17[]" value="友人" {{ is_array(old('q17')) && in_array('友人', old('q17')) ? 'checked' : '' }} />
                <span class="">友人</span>
            </label>
            <label class="w-1/2 mr-2 checkbox-label {{ is_array(old('q17')) && in_array('パートナー', old('q17')) ? 'checked' : '' }}">
                <input type="checkbox" name="q17[]" value="パートナー" {{ is_array(old('q17')) && in_array('パートナー', old('q17')) ? 'checked' : '' }} />
                <span class="">パートナー</span>
            </label>
            <label class="checkbox-label {{ is_array(old('q17')) && in_array('仕事', old('q17')) ? 'checked' : '' }}">
                <input type="checkbox" name="q17[]" value="仕事" {{ is_array(old('q17')) && in_array('仕事', old('q17')) ? 'checked' : '' }} />
                <span class="">仕事</span>
            </label>
            <label class="w-1/2 mr-2 checkbox-label {{ is_array(old('q17')) && in_array('その他', old('q17')) ? 'checked' : '' }}">
                <input type="checkbox" name="q17[]" value="その他" {{ is_array(old('q17')) && in_array('その他', old('q17')) ? 'checked' : '' }} />
                <span class="">その他</span>
            </label>
        </div>
    </fieldset>
</div>