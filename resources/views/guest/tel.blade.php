<div class="pb-8 @error('tel') mb-6 @enderror relative">
    <label for="tel" class="inline-flex pb-1">
        <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
        <span class="block">電話番号</span>
    </label>
    <input id="tel" type="tel" name="tel" value="{{ old('tel') }}" class="w-full h-12 mt-4 border border-gray-300 rounded placeholder-gray-300 leading-none" placeholder="09012345678" />
    <p class="text-red-600 flashing @error('tel') mt-4 -mb-8 @enderror">@error('tel') ※携帯番号を数字で入力してください @enderror</p>
</div>
