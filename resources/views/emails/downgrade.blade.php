{{--
Test link
http://hylark.test/dummy-mail?view=downgrade&name=Bob
--}}
@component('mail::message', ['name' => $name, 'signOff' => __('mail/closing.onBoard')])

@slot('title')
    @lang('mail/downgrade.title')
@endslot

@lang('mail/downgrade.welcome') &nbsp;

<br>
<br>

@lang('mail/downgrade.details')

@endcomponent
