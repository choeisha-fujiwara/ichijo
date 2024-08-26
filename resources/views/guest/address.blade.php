<div class="pb-8 @error('address') mb-6 @enderror relative">
    <label for="address" class="inline-flex pb-1">
        <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-gray-400 rounded">任意</span>
        <span class="block">ご住所</span>
    </label>
    <input id="address" type="text" name="address" value="{{ old('address') }}" class="p-region p-locality p-street-address p-extended-address w-full h-12 mt-4 mb-2 border border-gray-300 rounded placeholder-gray-300 leading-none" placeholder="大阪府大阪市北区中之島3丁目6番32号" />
</div>