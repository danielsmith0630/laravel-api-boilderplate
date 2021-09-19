@component('mail::message', ['theme' => $theme])
# @lang('email.welcome.title')


@lang('email.welcome.greetings', ['appName' => config('app.name')])


## @lang('email.welcome.title_1')



@lang('email.welcome.line_1_1', ['appName' => config('app.name')])


@lang('email.welcome.line_1_2')


@lang('email.welcome.line_1_3', ['appName' => config('app.name')])


## @lang('email.welcome.title_2')


@lang('email.welcome.line_2_1')


**@lang('email.welcome.sign_1')**  
*@lang('email.welcome.sign_2', ['appName' => config('app.name')])*

@component('mail::button', ['url' => $url, 'color' => 'primary', 'align' => 'center'])
@lang('email.welcome.cta', ['appName' => config('app.name')])
@endcomponent
@endcomponent