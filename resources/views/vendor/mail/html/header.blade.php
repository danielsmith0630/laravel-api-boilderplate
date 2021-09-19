<tr>
<td>
<table class="header" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="{{ $headerBg ? 'background-image: url(' . $headerBg . ')' : '' }}">
<tr align="{{ $align ?? '' }}">
<td>
<a href="{{ $url }}" style="display: inline-block;">
{{ $slot }}
</a>
</td>
</tr>
</table>
</td>
</tr>