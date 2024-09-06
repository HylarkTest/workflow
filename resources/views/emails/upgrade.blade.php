{{--
Test link
http://hylark.test/dummy-mail?view=upgrade&name=Bob&plan=Ascend
--}}
@component('mail::message', ['name' => $name, 'signOff' => __('mail/closing.onBoard')])

@slot('title')
    @lang('mail/upgrade.title')
@endslot

@lang('mail/upgrade.welcome', ['plan' => $plan]) &nbsp;

<br>
<br>

@lang('mail/upgrade.details')

@endcomponent
