{{--
Test link
http://hylark.test/dummy-mail?view=collab-invite&baseName=Acme&baseImage=https://picsum.photos/200&inviterName=John%20Doe&inviteLink=invite&resendLink=resend
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
    If you already have a Hylark account, please use the button below to accept the invite to <strong>{{ $baseName }}</strong>.
</p>

<br>

<p>
    No Hylark account yet? The button will take you to sign up and accept the invite once your new account is created.
<p>

@component('mail::button', ['url' => $inviteLink])
    Join "{{ $baseName }}"
@endcomponent

<br>
<br>

The link to join "{{ $baseName }}" will expire in 24 hours. Missed it? No problem. The button below will resend the invite link.

@component('mail::button', ['url' => $resendLink])
    Resend invite
@endcomponent

@endcomponent
