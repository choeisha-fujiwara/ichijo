<x-guest-layout>
	<section class="reservation-form reservation-thanks" aria-labelledby="thanks-title">
		<h2 id="thanks-title">ご予約ありがとうございました</h2>
		<p class="reservation-thanks-lead"><strong>{{ $name ?: 'お客様' }}</strong> 様のご予約を受け付けました。</p>
		<p>ご入力いただいたメールアドレス宛に確認メールをお送りしています。</p>
		<p>内容に誤りがある場合は、会場までご連絡ください。</p>
	</section>
</x-guest-layout>