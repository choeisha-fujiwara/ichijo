<x-guest-layout>
    <div class="content mx-auto">
        <div class="preface py-8 px-6 border-b text-white">
            <div class="mx-auto text-center pb-8">
                <h1 class="pb-6 text-xl leading-relaxed">{{ @$name }}<br/>お客様アンケート</h1>
                <img src="{{ asset('images/logo.png') }}" class="w-1/2 m-auto" alt="神座">
            </div>
        </div>
        <div class="w-full">
            <p class="pt-8 pb-6 text-xl text-center">お客様アンケートへのご協力、<br>誠にありがとうございました。</p>
            <p class="text-justify px-6 pb-8 leading-7 text-base font-normal">ご回答いただいた内容は、商品の品質向上・サービス改善に向けての貴重なご意見としてお取り扱いいたします。<br>今後とも、末長くご愛顧賜りますよう何卒よろしくお願い申し上げます。</p>
        </div>
        <div class="mx-auto text-center pb-8">
            <img src="{{ asset('images/logo-yoko.png') }}" class="w-1/2 m-auto" alt="神座">
        </div>
    </div>
</x-guest-layout>