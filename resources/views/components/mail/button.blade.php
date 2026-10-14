@props(['url'])
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
    <tr><td bgcolor="#FD5523" style="background-color:#FD5523;border-radius:6px;text-align:center;">
        <a href="{{ $url }}" style="display:inline-block;padding:13px 24px;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;">{{ $slot }}</a>
    </td></tr>
</table>
