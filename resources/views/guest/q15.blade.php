<div class="pb-3">
    <fieldset>
        <legend class="pb-6 flex">
            <span class="flex justify-start items-center whitespace-nowrap mr-2 pt-1 pb-1.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
            <span class="block">来店時間帯</span>
        </legend>
        <div class="radio-buttons flex-start-label">
            <label>
                <input type="radio" name="q15" value="～12時" {{ old('q15') == '～12時' ? 'checked' : '' }} />
                <span class="radio-label">～12時</span>
            </label>
            <label>
                <input type="radio" name="q15" value="12～14時" {{ old('q15') == '12～14時' ? 'checked' : '' }} />
                <span class="radio-label">12時～14時</span>
            </label>
            <label>
                <input type="radio" name="q15" value="14時～18時" {{ old('q15') == '14時～18時' ? 'checked' : '' }} />
                <span class="radio-label">14時～18時</span>
            </label>
            <label>
                <input type="radio" name="q15" value="18時～22時" {{ old('q15') == '18時～22時' ? 'checked' : '' }} />
                <span class="radio-label">18時～22時</span>
            </label>
            <label>
                <input type="radio" name="q15" value="22時以降" {{ old('q15') == '22時以降' ? 'checked' : '' }} />
                <span class="radio-label">22時以降</span>
            </label>
        </div>
    </fieldset>
</div>