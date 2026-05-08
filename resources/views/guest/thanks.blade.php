<x-guest-layout>
	<section class="reservation-form reservation-thanks" aria-labelledby="thanks-title">
		<h2 id="thanks-title">ご予約ありがとうございました</h2>
		<p class="reservation-thanks-lead"><strong>{{ $name ?: 'お客様' }}</strong> 様のご予約を受け付けました。</p>
		@if (($autoReplySent ?? true) === true)
			<p>ご入力いただいたメールアドレス宛に確認メールをお送りしています。</p>
		@else
			<p>確認メールの送信でエラーが発生した可能性があります。内容確認が必要な場合は会場までご連絡ください。</p>
		@endif
		<p>内容に誤りがある場合は、会場までご連絡ください。</p>
		@if ($backUrl)
			<a href="{{ $backUrl }}" class="reservation-thanks-back">元のページに戻る</a>
		@endif
	</section>
</x-guest-layout>