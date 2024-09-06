{{--
Test link
http://hylark.test/dummy-mail?view=email-updated&name=Bob&supportEmail=support%40hylark.com&oldEmail=email1%40email.com&newEmail=email2%40email.com
--}}
@component('mail::message', ['name' => $name])

@slot('title')
    @lang('mail/emailUpdated.title')
@endslot

<p>
@lang('mail/emailUpdated.intro1', compact('oldEmail', 'newEmail'))
</p>
<p style="margin-top:8px">
@lang('mail/emailUpdated.intro2')
</p>

@php
    $buttonUrl = 'mailto:' . $supportEmail
        . '?Subject=' . rawurlencode(__('mail/emailUpdated.contact-email.subject'))
        . '&Body=' . rawurlencode(__('mail/emailUpdated.contact-email.body'));
@endphp
@component('mail::panel')
@lang('mail/emailUpdated.content.body', ['email' => $supportEmail])
@component('mail::button', ['url' => $buttonUrl])
@lang('mail/emailUpdated.contact-button')
@endcomponent
@endcomponent

@endcomponent
