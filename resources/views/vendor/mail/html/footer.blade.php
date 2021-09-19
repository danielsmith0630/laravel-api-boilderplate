<tr>
<td>
<table class="app-markets" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr align="center">
<td class="content-cell" align="center">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
<tr align="center">
<td colspan="2"><p>@lang('email.common.app_store_title', ['appName' => config('app.name')])</p></td>
</tr>
<tr>
<td align="right" class="app-markets__col">
<a href="#" style="display: inline-block;" target="_blank" rel="noopener">
<img src="{{ asset('assets/img/email/appstore.png') }}" class="app-markets__app-btn" alt="App Store">
</a>
</td>
<td align="left" class="app-markets__col">
<a href="#" style="display: inline-block;" target="_blank" rel="noopener">
<img src="{{ asset('assets/img/email/googleplay.png') }}" class="app-markets__app-btn" alt="Google Play">
</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>

<tr align="middle">
<td>
<table border="0" cellpadding="0" cellspacing="0" role="presentation" class="social-btns">
<tr>
<td class="social-btn__col">
<a href="https://www.facebook.com/larabel-boilderplate" style="display: inline-block;" target="_blank" rel="noopener">
<img src="{{ $iconFacebook }}" class="social-btn" alt="Facebook">
</a>
</td>
<td class="social-btn__col">
<a href="https://twitter.com/larabel-boilderplate" style="display: inline-block;" target="_blank" rel="noopener">
<img src="{{ $iconTwitter }}" class="social-btn" alt="Twitter">
</a>
</td>
<td class="social-btn__col">
<a href="https://www.linkedin.com/company/larabel-boilderplate" style="display: inline-block;" target="_blank" rel="noopener">
<img src="{{ $iconLinkedin }}" class="social-btn" alt="Linkedin">
</a>
</td>
{{-- <td class="social-btn__col">
<a href="#" style="display: inline-block;" target="_blank" rel="noopener">
<img src="{{ $iconInstagram }}" class="social-btn" alt="Instagram">
</a>
</td> --}}
</tr>
</table>
</td>
</tr>

<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
<tr align="middle">
<td>
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr class="footer-links">
<td class="footer-link"><a href="https://www.larabel-boilderplate.com/privacy-policy" target="_blank" rel="noopener" style="display: inline-block;">@lang('email.common.footer.links.policy')</a></td>
<td class="footer-link__pipe">|</td>
<td class="footer-link"><a href="https://www.larabel-boilderplate.com/terms-of-service" target="_blank" rel="noopener" style="display: inline-block;">@lang('email.common.footer.links.tos')</a></td>
{{--<td class="footer-link__pipe">|</td>
<td class="footer-link"><a href="#" target="_blank" rel="noopener" style="display: inline-block;">@lang('email.common.footer.links.support')</a></td>
<td class="footer-link__pipe">|</td>
<td class="footer-link"><a href="#" target="_blank" rel="noopener" style="display: inline-block;">@lang('email.common.footer.links.account')</a></td>
<td class="footer-link__pipe">|</td>
<td class="footer-link"><a href="#" target="_blank" rel="noopener" style="display: inline-block;">@lang('email.common.footer.links.subscribe')</a></td>--}}
</tr>
</table>
</td>
</tr>

</table>
</td>
</tr>
