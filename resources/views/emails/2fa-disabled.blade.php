{{--
Test link
http://hylark.test/dummy-mail?view=2fa-disabled&name=Bob
--}}
@component('mail::message', ['name' => $name])

    @slot('title')
        @lang('mail/2faDisabled.title')
    @endslot

    @lang('mail/2faDisabled.intro')

@endcomponent
