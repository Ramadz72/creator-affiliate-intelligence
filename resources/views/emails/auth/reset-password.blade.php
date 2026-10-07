<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >

    <title>Reset Password - {{ $appName }}</title>

    <style>
        /* ---------------------------------------
         * Base
         * --------------------------------------- */

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            background: #f4f6f8;
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;
            color: #111827;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        img {
            border: 0;
            display: block;
            max-width: 100%;
        }

        a {
            text-decoration: none;
        }

        /* ---------------------------------------
         * Responsive
         * --------------------------------------- */

        @media only screen and (max-width: 620px) {
            .email-wrapper {
                padding: 16px !important;
            }

            .email-container {
                width: 100% !important;
                border-radius: 16px !important;
            }

            .content {
                padding: 32px 24px !important;
            }

            .header {
                padding: 28px 24px !important;
            }

            .security-card {
                padding: 18px !important;
            }

            .fallback-url {
                word-break: break-all !important;
            }

            .button {
                width: 100% !important;
            }
        }

        @media (prefers-color-scheme: dark) {
            .dark-bg {
                background: #111827 !important;
            }

            .dark-card {
                background: #1f2937 !important;
            }

            .dark-text {
                color: #f9fafb !important;
            }

            .dark-muted {
                color: #9ca3af !important;
            }
        }
    </style>
</head>

<body>

<table
    role="presentation"
    width="100%"
    class="dark-bg"
    style="background:#f4f6f8;"
>
    <tr>
        <td
            align="center"
            class="email-wrapper"
            style="padding:40px 20px;"
        >

            <!-- Main Container -->
            <table
                role="presentation"
                width="600"
                class="email-container dark-card"
                style="
                    width:600px;
                    max-width:600px;
                    background:#ffffff;
                    border-radius:20px;
                    overflow:hidden;
                    box-shadow:0 10px 40px rgba(15,23,42,0.08);
                "
            >

                <!-- =========================================
                     HEADER
                ========================================== -->

                <tr>
                    <td
                        class="header"
                        style="
                            padding:32px 40px;
                            border-bottom:1px solid #eef0f3;
                        "
                    >

                        <table role="presentation" width="100%">
                            <tr>

                                <td align="left">

                                    <table role="presentation">
                                    <tr>

                                        <td
                                            valign="middle"
                                            style="
                                                width:42px;
                                                height:42px;
                                            "
                                        >
                                            <img
                                                src="{{ asset('images/creator kecil.png') }}"
                                                alt="CAI"
                                                width="42"
                                                height="42"
                                                style="
                                                    width:42px;
                                                    height:42px;
                                                    display:block;
                                                    border:0;
                                                    border-radius:12px;
                                                    object-fit:contain;
                                                "
                                            >
                                        </td>

                                        <td
                                            valign="middle"
                                            style="padding-left:12px;"
                                        >
                                            <div
                                                style="
                                                    font-size:16px;
                                                    font-weight:700;
                                                    color:#111827;
                                                    line-height:20px;
                                                "
                                            >
                                                Creator Affiliate
                                            </div>

                                            <div
                                                style="
                                                    font-size:12px;
                                                    color:#6b7280;
                                                    line-height:18px;
                                                "
                                            >
                                                Intelligence
                                            </div>
                                        </td>

                                    </tr>
                                </table>

                                </td>

                            </tr>
                        </table>

                    </td>
                </tr>


                <!-- =========================================
                     CONTENT
                ========================================== -->

                <tr>
                    <td
                        class="content"
                        style="padding:42px 40px 36px;"
                    >

                        <!-- Greeting -->

                        <div
                            style="
                                font-size:14px;
                                color:#6b7280;
                                margin-bottom:8px;
                            "
                        >
                            Halo,
                        </div>

                        <div
                            class="dark-text"
                            style="
                                font-size:26px;
                                line-height:34px;
                                font-weight:700;
                                letter-spacing:-0.6px;
                                color:#111827;
                                margin-bottom:14px;
                            "
                        >
                            {{ $user->name }}
                              <span
                                style="
                                    font-size:24px;
                                    font-weight:400;
                                    letter-spacing:0;
                                    white-space:nowrap;
                                "
                            >👋</span>
                        </div>

                        <div
                            class="dark-muted"
                            style="
                                font-size:15px;
                                line-height:25px;
                                color:#6b7280;
                                margin-bottom:28px;
                            "
                        >
                            Kami menerima permintaan untuk mengatur ulang
                            password akun
                            <strong style="color:#374151;">
                                {{ $appName }}
                            </strong>.
                        </div>


                        <!-- =====================================
                             RESET CARD
                        ====================================== -->

                        <table
                            role="presentation"
                            width="100%"
                            class="security-card"
                            style="
                                background:#f8fafc;
                                border:1px solid #e5e7eb;
                                border-radius:16px;
                            "
                        >

                            <tr>
                                <td style="padding:24px;">

                                    <!-- Icon -->

                                    <div
                                        style="
                                            width:44px;
                                            height:44px;
                                            border-radius:12px;
                                            background:#eef2ff;
                                            color:#4f46e5;
                                            font-size:20px;
                                            line-height:44px;
                                            text-align:center;
                                            margin-bottom:18px;
                                        "
                                    >
                                        🔐
                                    </div>

                                    <div
                                        class="dark-text"
                                        style="
                                            font-size:17px;
                                            font-weight:700;
                                            color:#111827;
                                            margin-bottom:8px;
                                        "
                                    >
                                        Reset password
                                    </div>

                                    <div
                                        class="dark-muted"
                                        style="
                                            font-size:14px;
                                            line-height:22px;
                                            color:#6b7280;
                                            margin-bottom:22px;
                                        "
                                    >
                                        Gunakan tombol di bawah untuk membuat
                                        password baru untuk akun kamu.
                                    </div>


                                    <!-- CTA -->

                                    <table role="presentation">
                                        <tr>
                                            <td
                                                class="button"
                                                style="
                                                    border-radius:10px;
                                                    background:#111827;
                                                "
                                            >
                                                <a
                                                    href="{{ $url }}"
                                                    target="_blank"
                                                    style="
                                                        display:inline-block;
                                                        padding:13px 22px;
                                                        border-radius:10px;
                                                        background:#111827;
                                                        color:#ffffff;
                                                        font-size:14px;
                                                        font-weight:600;
                                                        line-height:20px;
                                                    "
                                                >
                                                    Reset Password
                                                    &nbsp;→
                                                </a>
                                            </td>
                                        </tr>
                                    </table>


                                    <!-- Expiry -->

                                    <div
                                        style="
                                            margin-top:18px;
                                            font-size:12px;
                                            line-height:20px;
                                            color:#6b7280;
                                        "
                                    >
                                        ⏱ Link ini akan kedaluwarsa dalam
                                        <strong style="color:#374151;">
                                            60 menit
                                        </strong>.
                                    </div>

                                </td>
                            </tr>

                        </table>


                        <!-- =====================================
                             SECURITY NOTICE
                        ====================================== -->

                        <table
                            role="presentation"
                            width="100%"
                            style="margin-top:20px;"
                        >

                            <tr>

                                <td
                                    valign="top"
                                    style="
                                        width:28px;
                                        font-size:16px;
                                    "
                                >
                                    🛡️
                                </td>

                                <td
                                    class="dark-muted"
                                    style="
                                        font-size:13px;
                                        line-height:21px;
                                        color:#6b7280;
                                    "
                                >
                                    <strong
                                        class="dark-text"
                                        style="color:#374151;"
                                    >
                                        Tidak merasa melakukan permintaan ini?
                                    </strong>
                                    <br>

                                    Kamu dapat mengabaikan email ini.
                                    Password akun kamu tidak akan berubah
                                    sampai kamu menggunakan link di atas.
                                </td>

                            </tr>

                        </table>


                        <!-- =====================================
                             DIVIDER
                        ====================================== -->

                        <div
                            style="
                                height:1px;
                                background:#eef0f3;
                                margin:32px 0;
                            "
                        ></div>


                        <!-- =====================================
                             FALLBACK URL
                        ====================================== -->

                        <div
                            class="dark-muted"
                            style="
                                font-size:12px;
                                line-height:20px;
                                color:#6b7280;
                                margin-bottom:8px;
                            "
                        >
                            Jika tombol di atas tidak dapat digunakan,
                            salin dan buka URL berikut di browser:
                        </div>

                        <div
                            class="fallback-url"
                            style="
                                padding:12px 14px;
                                background:#f8fafc;
                                border:1px solid #e5e7eb;
                                border-radius:8px;
                                font-size:11px;
                                line-height:18px;
                                color:#4f46e5;
                                word-break:break-all;
                            "
                        >
                            {{ $url }}
                        </div>

                    </td>
                </tr>


                <!-- =========================================
                     FOOTER
                ========================================== -->

                <tr>
                    <td
                        style="
                            padding:24px 40px 30px;
                            border-top:1px solid #eef0f3;
                        "
                    >

                        <table
                            role="presentation"
                            width="100%"
                        >

                            <tr>

                                <td>

                                    <div
                                        class="dark-text"
                                        style="
                                            font-size:13px;
                                            font-weight:700;
                                            color:#111827;
                                            margin-bottom:4px;
                                        "
                                    >
                                        {{ $appName }}
                                    </div>

                                    <div
                                        class="dark-muted"
                                        style="
                                            font-size:12px;
                                            line-height:18px;
                                            color:#9ca3af;
                                        "
                                    >
                                        Creator Affiliate Intelligence
                                    </div>

                                </td>

                                <td
                                    align="right"
                                    valign="top"
                                >

                                    <div
                                        style="
                                            font-size:11px;
                                            color:#9ca3af;
                                        "
                                    >
                                        Password Security
                                    </div>

                                </td>

                            </tr>

                        </table>


                        <div
                            style="
                                margin-top:22px;
                                font-size:11px;
                                line-height:18px;
                                color:#9ca3af;
                            "
                        >
                            Email ini dikirim secara otomatis.
                            Mohon tidak membalas email ini.
                        </div>

                        <div
                            style="
                                margin-top:6px;
                                font-size:11px;
                                color:#9ca3af;
                            "
                        >
                            © {{ date('Y') }} {{ $appName }}.
                            All rights reserved.
                        </div>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>