{{--
Test link
http://hylark.test/dummy-mail?view=billed-user-left-base&baseName=Acme&fullName=Bob&leftUser=John&expiresAt=2020-01-01&link=https://hylark.test
--}}
@component('mail::message', ['name' => $fullName])

@slot('title')
    @lang('mail/billedUserLeftBase.title', ['baseName' => $baseName])
@endslot

@lang('mail/billedUserLeftBase.whyReceiving', ['baseName' => $baseName])

<br>
<br>

@lang('mail/billedUserLeftBase.previousUser', ['baseName' => $baseName, 'leftUser' => $leftUser])

<br>

@lang('mail/billedUserLeftBase.canceled', ['date' => $expiresAt, 'leftUser' => $leftUser])

<br>
<br>
@lang('mail/billedUserLeftBase.update', ['baseName' => $baseName])

<br>
@component('mail::button', ['url' => $link])
    @lang('mail/billedUserLeftBase.renew')
@endcomponent

@endcomponent
