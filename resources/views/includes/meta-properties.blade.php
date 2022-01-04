<meta property="fb:app_id" content="{{ env('FACEBOOK_APP_ID') }}" />
<meta property="og:site_name" content="{{ config('app.name') }}" />
<meta property="og:type" content="website">

@if (Route::currentRouteName() === 'followouts.show')
    @isset($followout)
        <meta property="og:title" content="{{ $followout->title }}" />
        <meta property="og:description" content="{{ $followout->description }}" />
        <meta property="og:image" content="{{ convertToHttpScheme($followout->flyerURL()) }}" />
        <meta property="og:image:width" content="500">
        <meta property="og:image:height" content="750">
        <meta property="og:url" content="{{ $followout->isPublic() ? convertToHttpScheme($followout->url()) : convertToHttpScheme($followout->url(true)) }}" />

        <meta property="twitter:title" content="{{ $followout->title }}" />
        <meta property="twitter:description" content="{{ $followout->description }}" />
        <meta property="twitter:image" content="{{ convertToHttpScheme($followout->flyerURL()) }}" />
        <meta property="twitter:url" content="{{ $followout->isPublic() ? convertToHttpScheme($followout->url()) : convertToHttpScheme($followout->url(true)) }}" />
    @endisset
@elseif (Route::currentRouteName() === 'users.show')
    @isset($user)
        <meta property="og:title" content="{{ $user->name }}" />
        <meta property="og:description" content="{{ $user->about }}" />
        <meta property="og:image" content="{{ convertToHttpScheme($user->avatarURL()) }}">
        <meta property="og:image:width" content="500">
        <meta property="og:image:height" content="500">
        <meta property="og:url" content="{{ convertToHttpScheme($user->url()) }}" />

        <meta property="twitter:title" content="{{ $user->name }}" />
        <meta property="twitter:description" content="{{ $user->about }}" />
        <meta property="twitter:image" content="{{ convertToHttpScheme($user->avatarURL()) }}" />
        <meta property="twitter:url" content="{{ convertToHttpScheme($user->url()) }}" />
    @endisset
@endif
