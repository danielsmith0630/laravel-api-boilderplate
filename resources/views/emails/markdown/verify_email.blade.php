@component('mail::message', ['theme' => $theme])
# @lang('email.verify_email.title')


@lang('email.verify_email.greetings')


@lang('email.verify_email.line_1', ['appName' => config('app.name')])


@lang('email.verify_email.line_2', [
  'appName' => config('app.name'),
  'link' => 'mailto:hello@larabel-boilderplate.com',
  'expiresInHours' => trans_choice(
    'email.common.hours',
    config('mail.verification.expires_in_hours'),
    ['hours' => config('mail.verification.expires_in_hours')]
  )
])


@component('mail::button', ['url' => $url, 'color' => 'primary'])
@lang('email.verify_email.cta')
@endcomponent
@endcomponent
