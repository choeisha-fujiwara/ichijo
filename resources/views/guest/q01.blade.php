<div class="pb-4">
    <fieldset>
        <legend class="inline-flex pb-6">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
            <span class="block">本日ご注文したメニューについて</span>
        </legend>
        <div class="checkbox-buttons flex flex-wrap justify-start items-center">
            <label class="w-1/2 mr-2 checkbox-label {{ is_array(old('q01')) && in_array('ラーメン', old('q01')) ? 'checked' : '' }}">
                <input type="checkbox" name="q01[]" value="ラーメン" {{ is_array(old('q01')) && in_array('ラーメン', old('q01')) ? 'checked' : '' }} />
                <span class="">ラーメン</span>
            </label>
            <label class="checkbox-label {{ is_array(old('q01')) && in_array('餃子', old('q01')) ? 'checked' : '' }}" data-options="option02">
                <input type="checkbox" name="q01[]" value="餃子" {{ is_array(old('q01')) && in_array('餃子', old('q01')) ? 'checked' : '' }} />
                <span class="">餃子</span>
            </label>
            <label class="w-1/2 mr-2 checkbox-label {{ is_array(old('q01')) && in_array('からあげ', old('q01')) ? 'checked' : '' }}">
                <input type="checkbox" name="q01[]" value="からあげ" {{ is_array(old('q01')) && in_array('からあげ', old('q01')) ? 'checked' : '' }} />
                <span class="">からあげ</span>
            </label>
            <label class="checkbox-label {{ is_array(old('q01')) && in_array('炒飯', old('q01')) ? 'checked' : '' }}" data-options="option01">
                <input type="checkbox" name="q01[]" value="炒飯" {{ is_array(old('q01')) && in_array('炒飯', old('q01')) ? 'checked' : '' }} />
                <span class="">炒飯</span>
            </label>
            <label class="w-1/2 mr-2 checkbox-label {{ is_array(old('q01')) && in_array('季節メニュー', old('q01')) ? 'checked' : '' }}">
                <input type="checkbox" name="q01[]" value="季節メニュー" {{ is_array(old('q01')) && in_array('季節メニュー', old('q01')) ? 'checked' : '' }} />
                <span class="">季節メニュー</span>
            </label>
        </div>
    </fieldset>
</div>