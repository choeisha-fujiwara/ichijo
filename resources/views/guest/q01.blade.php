<div class="pb-4">
    <fieldset>
        <legend class="inline-flex pb-6">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
            <span class="block">本日ご注文したメニューについて</span>
        </legend>
        <div class="checkbox-buttons flex flex-wrap justify-start items-center">
            <label class="w-1/2 mr-2 checkbox-label {{ old('q01_a1') ? 'checked' : null }}">
                <input type="checkbox" name="q01_a1" value="{{ $shop->shop_category == '神座' ? 'ラーメン' : 'つけ麺' }}" {{ old('q01_a1') ? 'checked' : null }} />
                <span class="">{{ $shop->shop_category == '神座' ? 'ラーメン' : 'つけ麺' }}</span>
            </label>
            <label class="checkbox-label {{ old('q01_a2') ? 'checked' : null; }}" data-options="option02">
                <input type="checkbox" name="q01_a2" value="餃子" {{ old('q01_a2') ? 'checked' : null }} />
                <span class="">餃子</span>
            </label>
            <label class="w-1/2 mr-2 checkbox-label {{ old('q01_a3') ? 'checked' : null; }}">
                <input type="checkbox" name="q01_a3" value="からあげ" {{ old('q01_a3') ? 'checked' : null }} />
                <span class="">からあげ</span>
            </label>
            <label class="checkbox-label {{ old('q01_a4') ? 'checked' : '' }}" data-options="option01">
                <input type="checkbox" name="q01_a4" value="炒飯" {{ old('q01_a4') ? 'checked' : null }} />
                <span class="">炒飯</span>
            </label>
            <label class="w-1/2 mr-2 checkbox-label {{ old('q01_a5') ? 'checked' : null }}">
                <input type="checkbox" name="q01_a5" value="季節メニュー" {{ old('q01_a5') ? 'checked' : null }} />
                <span class="">季節メニュー</span>
            </label>
        </div>
    </fieldset>
</div>