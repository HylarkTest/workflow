{{--
Test link
http://hylark.test/dummy-mail?view=activate-account&name=Bob&url=https://hylark.test
--}}
@component('mail::message', ['name' => $name, 'signOff' => __('mail/closing.onBoard')])

@slot('title')
    @lang('mail/activateAccount.title')
@endslot

@lang('mail/activateAccount.welcome') &nbsp;
<br>
@lang('mail/activateAccount.nearlyReady')
<br>
<br>

@lang('mail/activateAccount.clickToVerify')

@component('mail::button', ['url' => $url])
    @lang('mail/activateAccount.verify')
@endcomponent

@lang('mail/activateAccount.expired')

[@lang('mail/activateAccount.resend')]({{ route('verification.send.get') }})
<br>
<br>
@lang('mail/activateAccount.closing')

@endcomponent
