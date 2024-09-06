{{--
Test link
http://hylark.test/dummy-mail?view=one-time-password&code=1234&browser=firefox&platform=Mac&cityName=London&countryName=UK&time=!now&ip=127.0.0.1&name=Bob
--}}
@component('mail::message', ['name' => $name])

@slot('title')
    @lang('mail/oneTimePassword.title')
@endslot

@lang('mail/oneTimePassword.intro')

<br>

@isset($browser, $platform)

<p>
@lang('mail/oneTimePassword.browser'):
</p>

@lang('mail/oneTimePassword.platform', ['browser' => $browser, 'platform' => $platform])
@endif

<br>

@isset($cityName, $countryName)
@lang('mail/oneTimePassword.location'):
{{ $cityName }}, {{ $countryName }} (@lang('mail/oneTimePassword.estimated'))
@endisset

@lang('mail/oneTimePassword.ip'): {{ $ip }}

{{ $time->rawFormat('D, M j, Y') }}
{{ $time->rawFormat('g:i A (T)') }}

@lang('mail/oneTimePassword.code'):

<div dusk="code"></div>

## {{ $code }}

@lang('mail/oneTimePassword.expires')

@endcomponent
