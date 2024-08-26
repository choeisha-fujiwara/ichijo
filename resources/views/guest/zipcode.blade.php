<div class="pb-6 @error('zipcode') mb-6 @enderror relative">
    <label for="zipcode" class="inline-flex pb-1">
        <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
        <span class="block">郵便番号</span>
    </label>
    <div class="w-full flex justify-start items-center">
        <p class="mr-2 pt-4 text-lg">〒</p>
        <input id="zipcode" type="number" value="{{ old('zipcode') }}" name="zipcode" class="p-postal-code w-1/2 h-12 mt-4 border border-gray-300 rounded placeholder-gray-300 leading-none" placeholder="1234567" />
    </div>
</div>