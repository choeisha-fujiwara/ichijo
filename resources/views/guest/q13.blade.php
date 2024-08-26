<div class="pb-4">
    <fieldset>
        <legend class="pb-6 flex">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
            <span class="block">神座にまた来たいと思いますか？</span>
        </legend>
        <div class="radio-buttons">
            <label class="">
                <input type="radio" name="q13" value="必ずまた来たいと思う" {{ old('q13') == '必ずまた来たいと思う' ? 'checked' : '' }} />
                <span class="radio-label">必ずまた来たいと思う</span>
            </label>
            <label class="">
                <input type="radio" name="q13" value="また来たいと思う" {{ old('q13') == 'また来たいと思う' ? 'checked' : '' }} />
                <span class="radio-label">また来たいと思う</span>
            </label>
            <label class="">
                <input type="radio" name="q13" value="なんともいえない" {{ old('q13') == 'なんともいえない' ? 'checked' : '' }} />
                <span class="radio-label">なんともいえない</span>
            </label>
            <label class="">
                <input type="radio" name="q13" value="もう来ないと思う" {{ old('q13') == 'もう来ないと思う' ? 'checked' : '' }} />
                <span class="radio-label">もう来ないと思う</span>
            </label>
            <label class="">
                <input type="radio" name="q13" value="絶対にもう来ないと思う" {{ old('q13') == '絶対にもう来ないと思う' ? 'checked' : '' }} />
                <span class="radio-label">絶対にもう来ないと思う</span>
            </label>
        </div>
    </fieldset>
</div>