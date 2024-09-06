{{--
    Test link
    http://hylark.test/dummy-mail?view=complete-registration&fullName=Bob&url=https://hylark.test/login
--}}
@component('mail::message', ['fullName' => $fullName, 'signOff' => __('mail/closing.hope')])

@slot('title')
    @lang('mail/completeRegistration.title')
@endslot

@lang('mail/completeRegistration.intro')
<br>
@lang('mail/completeRegistration.heads-up')
@lang('mail/completeRegistration.delete')
<br>
<br>

@lang('mail/completeRegistration.link')

@component('mail::button', ['url' => config('app.url').'/login'])
    @lang('mail/completeRegistration.action')
@endcomponent

@lang('mail/completeRegistration.closing')

@endcomponent
