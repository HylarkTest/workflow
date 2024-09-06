<table>
    <tr>
        <td>
            <p
                style="
                    font-weight: 600;
                    padding-bottom: 4px;
                "
            >
                @if(isset($slot) && (string) $slot)
                    {{ $slot }}
                @else
                    @php
                        $closing = collect(__('mail/closing.general'))->random();
                    @endphp
                    {{ $closing }}
                @endisset
            </p>
        </td>
    </tr>

    <tr>
        <td>
            <p style="padding-bottom: 8px">
                @lang('mail/closing.friends')
            </p>
        </td>
    </tr>

    <tr>
        <td>
            <img
                src="{{ global_asset('branding/FlyingUpBird_72dpi.png') }}"
                height="60"
                width="auto"
                style="height: 60px;"
            >
        </td>
    </tr>
</table>
