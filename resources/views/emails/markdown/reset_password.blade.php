@component('mail::message', ['theme' => $theme])
# @lang('email.reset_password.title')


@lang('email.reset_password.greetings')


@lang('email.reset_password.line_1', ['email' => $user->email])


@lang('email.reset_password.line_2', [
  'link' => 'mailto:hello@larabel-boilderplate.com',
  'expiresInHours' => trans_choice(
    'email.common.hours',
    config('mail.reset_password.expires_in_hours'),
    ['hours' => config('mail.reset_password.expires_in_hours')]
  )
])


@component('mail::button', ['url' => $url, 'color' => 'primary'])
@lang('email.reset_password.cta')
@endcomponent
@endcomponent
