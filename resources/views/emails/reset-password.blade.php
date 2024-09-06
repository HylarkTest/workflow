{{--
Test link
http://hylark.test/dummy-mail?view=reset-password&name=Bob&url=https://hylark.test
--}}
@component('mail::message', ['name' => $name])

@slot('title')
    @lang('mail/resetPassword.title')
@endslot

@lang('mail/resetPassword.intro')

@component('mail::button', ['url' => $url])
    @lang('mail/resetPassword.reset')
@endcomponent

@lang('mail/resetPassword.expire', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')])

@component('mail::panel')
    **@lang('mail/resetPassword.securityTip')**
    @php
        $tip = collect(__('mail/resetPassword.tips'))->random();
    @endphp
    <p
        style="
            margin-top: 4px;
        "
    >
        {{ $tip }}
    </p>
@endcomponent

@endcomponent
