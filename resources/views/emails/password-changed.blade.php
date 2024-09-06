{{--
Test link
http://hylark.test/dummy-mail?view=password-changed&name=Bob
--}}
@component('mail::message', ['name' => $name])

    @slot('title')
        @lang('mail/passwordChanged.title')
    @endslot

    @lang('mail/passwordChanged.intro')

@endcomponent
