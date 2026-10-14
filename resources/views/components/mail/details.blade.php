@props(['heading', 'rows'])
@if (count($rows))
    <h2 style="margin:24px 0 10px;color:#062f3a;font-size:15px;line-height:1.4;">{{ $heading }}</h2>
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;table-layout:fixed;border-collapse:collapse;border-top:1px solid #e6e6e6;">
        <tbody>
        @foreach ($rows as $label => $value)
            @if (in_array($label, ['Address', 'Description', 'Special instructions'], true))
                <tr><td colspan="2" style="padding:12px;border-bottom:1px solid #e6e6e6;color:#062f3a;font-size:13px;line-height:1.7;vertical-align:top;overflow-wrap:anywhere;word-break:break-word;">
                    <strong style="display:block;margin-bottom:6px;color:#585b6b;font-weight:600;">{{ $label }}</strong>
                    <div style="white-space:pre-line;">{{ $value }}</div>
                </td></tr>
            @else
            <tr>
                <th scope="row" align="left" width="38%" style="width:38%;padding:10px 12px;background-color:#ffffff;border-bottom:1px solid #e6e6e6;color:#585b6b;font-size:13px;font-weight:600;line-height:1.6;vertical-align:top;">{{ $label }}</th>
                <td style="padding:10px 12px;border-bottom:1px solid #e6e6e6;color:#062f3a;font-size:13px;line-height:1.6;vertical-align:top;white-space:pre-line;overflow-wrap:anywhere;word-break:break-word;">{{ $value }}</td>
            </tr>
            @endif
        @endforeach
        </tbody>
    </table>
@endif
