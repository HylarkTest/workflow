{{-- TBREVIEWED

The links need to be added in once the home pages are done --}}

<tr>
    <td>
        <table
            class="footer"
            align="center"
            width="570"
            cellpadding="0"
            cellspacing="0"
            role="presentation"
        >
            <tr class="content-cell" align="center">
                <td>
                    <a
                        href="{{ config('app.landing_url') }}"
                        style="
                            display: block;
                            margin-bottom: 6px;
                        "
                    >
                        <img
                            src="{{ global_asset('images/logos/40h_logo.png') }}"
                            height="24"
                            width="auto"
                            style="
                                height: 24px;
                            "
                        >
                    </a>
                </td>
            <tr>

            <tr class="content-cell" align="center">
                <td>
                    <table style="margin-bottom: 20px;">
                        <tr>
                            <td>
                                <a href="{{ config('app.landing_url') }}/cookies">
                                    @lang('mail/footer.cookies')
                                </a>
                            </td>
                            <td>
                                |
                            </td>
                            <td>
                                <a href="{{ config('app.landing_url') }}/terms-and-conditions">
                                    @lang('mail/footer.terms')
                                </a>
                            </td>
                            <td>
                                |
                            </td>
                            <td>
                                <a href="{{ config('app.landing_url') }}/privacy-policy">
                                    @lang('mail/footer.privacy')
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="content-cell" align="center">
                <td>
                    <p style="margin-bottom: 6px;">
                        <strong>Let's connect. We're easy to click with.</strong>
                    </p>
                </td>
            </tr>

            <tr class="content-cell" align="center">
                <td>
                    <table style="margin-bottom: 20px;">
                        @component('mail::socialLinks')
                        @endcomponent
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table style="margin-bottom: 10px;">
                        <tr>
                            <td>
                                <p>
                                    @lang('mail/footer.member')
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <p>
                                    @lang('mail/footer.reachUs')
                                        <a
                                            href="mailTo:{{ config('mail.from.hello') }}"
                                        >
                                            <strong>{{ config('mail.from.hello') }}</strong>
                                        </a>
                                    .
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <p>Copyright © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </td>
            </tr>
        </table>
    </td>
</tr>
