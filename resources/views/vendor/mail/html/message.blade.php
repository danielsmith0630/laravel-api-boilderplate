@php

  if (isset($theme)) {
    switch ($theme) {
      case ('light'):
        $headerBg = null;
        $headerAlign = null;
        $headerLogo = asset('assets/img/email/logo-white.png');
        $bodyBg = asset('assets/img/email/bg-circles-light.png');
        $iconFacebook = asset('assets/img/email/facebook-light.png');
        $iconTwitter = asset('assets/img/email/twitter-light.png');
        $iconInstagram = asset('assets/img/email/instagram-light.png');
        $iconLinkedin = asset('assets/img/email/linkedin-light.png');
        break;
      case ('purple'):
        $headerBg = asset('assets/img/email/bg-people-purple.jpg');
        $headerAlign = 'center';
        $headerLogo = asset('assets/img/email/logo-yellow.png');
        $bodyBg = null;
        $iconFacebook = asset('assets/img/email/facebook-white.png');
        $iconTwitter = asset('assets/img/email/twitter-white.png');
        $iconInstagram = asset('assets/img/email/instagram-white.png');
        $iconLinkedin = asset('assets/img/email/linkedin-white.png');
        break;
      default:
        $headerBg = null;
        $headerAlign = null;
        $headerLogo = asset('assets/img/email/logo-white.png');
        $bodyBg = null;
        $iconFacebook = asset('assets/img/email/facebook-light.png');
        $iconTwitter = asset('assets/img/email/twitter-light.png');
        $iconInstagram = asset('assets/img/email/instagram-light.png');
        $iconLinkedin = asset('assets/img/email/linkedin-light.png');
    }
  }
@endphp
@component('mail::layout', ['bodyBg' => $bodyBg])
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url'), 'headerBg' => $headerBg, 'align' => $headerAlign])
<img src="{{ $headerLogo }}" class="logo" alt="{{ config('app.name') }} Logo">
@endcomponent
@endslot

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset

{{-- Footer --}}
@slot('footer')
@component('mail::footer', ['iconFacebook' => $iconFacebook, 'iconTwitter' => $iconTwitter, 'iconInstagram' => $iconInstagram, 'iconLinkedin' => $iconLinkedin])
@lang('email.common.footer.copy', ['year' => date('Y'), 'appName' => config('app.name')])
<br/>
@lang('email.common.footer.source', ['appName' => config('app.name')])
<br/>
@lang('email.common.footer.address')

@endcomponent
@endslot
@endcomponent