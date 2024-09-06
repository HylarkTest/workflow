<table
    style="
        padding: 8px;
        background-color: #f5f5f5;
        width: 100%;
        color: #13066a;
        font-size: 18px;
    "
    width="100%"
>
    @if ($baseImage)
    <tr>
        <td
            style="text-align: center;"
        >
            <img
                src="{{ $baseImage }}"
                alt="{{ $baseName }}"
                height="60"
                width="60"
                style="
                    height: 60px;
                    width: 60px;
                    border-radius: 20px;
                "
            />
        </td>
    </tr>
    @endif
    <tr>
        <td
            style="text-align: center;"
        >
            <strong>{{ $baseName }}</strong>
        </td>
    </tr>
</table>
