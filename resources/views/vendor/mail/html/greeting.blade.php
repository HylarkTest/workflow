@aware(['name'])

<p
    style="
        font-weight: 600;
    "
>
    @isset($name)
        @lang('mail/greeting.hello', ['name' => $name])
    @else
        @lang('mail/greeting.hi')
    @endisset
</p>

