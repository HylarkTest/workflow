{{--
Test link
http://hylark.test/dummy-mail?view=import-finished&filename=contacts.csv
--}}
@component('mail::message', ['signOff' => __('mail/closing.general.2')])

@slot('title')
    @lang('mail/importFinished.title')
@endslot

<p>
    Your import from the file <strong>{{ $filename }}</strong> has finished. You can now access your data on Hylark.
</p>

@endcomponent
