<div class="pb-8">
    <fieldset>
        <legend class="pb-6 flex">
            <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
            <span class="block">ラーメンについてお気づきの点</span>
        </legend>
        <textarea id="q05" type="text" name="q05" class="@error('q05') bg-red-100 @enderror w-full h-32 pt-3 border border-gray-300 rounded placeholder-gray-300" placeholder="記入してください">{{ old('q05') }}</textarea>
    </fieldset>
    <p class="text-red-600 flashing @error('q05') mt-4 -mb-2 @enderror">@error('q05') ※お気づきの点をご記入ください @enderror</p>
</div>