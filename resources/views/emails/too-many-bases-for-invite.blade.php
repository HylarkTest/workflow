{{--
Test link
http://hylark.test/dummy-mail?view=too-many-bases-for-invite&baseName=Acme&baseImage=https://picsum.photos/200&inviterName=John%20Doe
--}}
@component('mail::message', ['signOff' => __('mail/closing.general.2')])

@slot('title')
    You have been invited to join "{{ $baseName }}" on Hylark
@endslot

@slot('subtitle')

@component('mail::collabBaseHeader', ['baseImage' => $baseImage, 'baseName' => $baseName])
@endcomponent

@endslot

<p>
    <strong>{{ $inviterName }}</strong> has sent you an invite to join <strong>{{ $baseName }}</strong> on Hylark.
</p>

<br>

<p>
    This email address is already associated with the maximum number of bases allowed per account on Hylark. If you'd like to join <strong>{{ $baseName }}</strong>, please review the other collaborative bases associated with your account to open up a slot for <strong>{{ $baseName }}</strong>.
</p>

<br>

<p>
    Once that is done, you can request another invite from <strong>{{ $inviterName }}</strong>.
</p>

@endcomponent
