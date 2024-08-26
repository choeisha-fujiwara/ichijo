<x-guest-layout>
    <div class="content mx-auto">
        <div class="preface py-8 px-6 border-b text-white">
            <div class="mx-auto text-center">
                <h1 class="pb-6 text-xl leading-relaxed">{{ @$user->name }}<br/>お客様アンケート</h1>
                <img src="{{ asset('images/logo.png') }}" class="w-1/2 m-auto" alt="神座">
            </div>
            <div class="pt-6">
                <p class="text-justify">
                    <span class="block mb-2 leading-relaxed">必要事項をご記入の上、送信ボタンをクリックしてください。<br/>率直なご意見をよろしくお願い致します。</span>
                    <span class="text-xs font-normal leading-snug">※アプリ・来店ポイント・QRコードに関するお問い合わせは、アプリ内（ホーム画面最下部の左下）「お問い合わせ」をご利用いただくとスムーズです。</span>
                </p>
            </div>
            @if (count($errors) > 0) <p class="pt-6 text-center text-red-600 flashing">※入力内容をご確認ください</p> @endif
        </div>
        <div class="w-full">
            <form action="/" method="POST" class="h-adr" novalidate>
            @csrf
                <span class="p-country-name" style="display:none;">Japan</span>
                <div class="px-6 pt-8 pb-6">
                    @include('guest.q01')
                    @include('guest.q02')
                    @include('guest.q03')
                    @include('guest.q04')
                    @include('guest.q05')
                    @include('guest.q06')
                    @include('guest.q07')
                    @include('guest.q08')
                    @include('guest.q09')
                    @include('guest.q10')
                    @include('guest.q11')
                    @include('guest.q12')
                    @include('guest.q13')
                    @include('guest.q14')
                    @include('guest.q15')
                    @include('guest.q16')
                    @include('guest.q17')
                    @include('guest.q18')
                    @include('guest.q19')
                    @include('guest.q20')
                </div>
                <div class="px-6 py-8 border-t border-gray-300 border-dashed">
                    @include('guest.email')
                    @include('guest.gender')
                    @include('guest.age')
                    @include('guest.name')
                    @include('guest.tel')
                    @include('guest.zipcode')
                    @include('guest.address')
                    <input type="hidden" name="shop_id" value="{{ $user->id, old('shop_id') }}" />
                    <input type="hidden" name="shop_name" value="{{ $user->name, old('shop_name') }}" />
                    <input type="hidden" name="area" value="{{ $user->area, old('area') }}" />
                    <div class="acceptance relative flex justify-start items-center flex-col pt-2">
                        <label class="acceptance-label relative cursor-pointer" for="acceptance">個人情報の取り扱いに同意</label>
                        <input class="acceptance hidden-object" type="checkbox" value="同意" id="acceptance" name="acceptance" {{ old('acceptance') == '同意' ? 'checked' : '' }} />
                        <input class="submit-btn w-1/2 h-12 relative rounded bg-gray-400 text-white text-center transition pointer-events-none order-last" type="submit" value="送信" />
                        <p class="pt-7 pb-8 text-center font-normal leading-relaxed">※<a href="https://rsj.co.jp/etc/privacy.php" class="underline" target="_blank">個人情報の取り扱い</a>に同意の上、送信<br/>いただきますようお願いいたします。</p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>