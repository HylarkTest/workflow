<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,600;0,800;1,400;1,600;1,800&display=swap" rel="stylesheet">
</head>
<body>
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>

    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    {{ $header ?? '' }}

                    <!-- Email Body -->
                    <tr>
                        <td
                            class="body"
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                        >
                            <table
                                class="inner-body"
                                align="center"
                                width="570"
                                cellpadding="0"
                                cellspacing="0"
                                role="presentation"
                            >
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell">
                                        {{ $title ?? '' }}
                                    </td>
                                </tr>

                                @isset($subtitle)
                                <tr>
                                    <td class="content-cell">
                                        {{ $subtitle }}
                                    </td>
                                </tr>
                                @endisset

                                <tr>
                                    <td class="content-cell">
                                        {{ $greeting ?? '' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="content-cell">
                                        {{ Illuminate\Mail\Markdown::parse($slot) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="content-cell">
                                        {{ $closing ?? '' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{ $footer ?? '' }}
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
