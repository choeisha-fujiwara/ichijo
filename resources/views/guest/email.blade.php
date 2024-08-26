<div class="pb-9 @error('email') mb-6 @enderror relative">
    <label for="mail" class="inline-flex pb-1">
        <span class="flex justify-center items-center mr-2 pb-0.5 px-2 text-xs text-white bg-red-600 rounded">必須</span>
        <span class="block">メールアドレス</span>
    </label>
    <input id="mail" type="email" name="email" value="{{ old('email') }}" class="@error('email') bg-red-100 @enderror w-full h-12 mt-4 border border-gray-300 rounded placeholder-gray-300 leading-none" placeholder="mail@example.com" required />
    <p class="text-red-600 flashing @error('email') mt-4 -mb-8 @enderror">@error('email') ※正しいメールアドレスをご記入ください @enderror</p>
</div>
