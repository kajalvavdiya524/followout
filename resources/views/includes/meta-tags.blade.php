{{-- Page title and description --}}
<title>@yield('page-title', (new MetaTagHelper)->getTitle())</title>
<meta name="description" content="{{ (new MetaTagHelper)->getDescription() }}">

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

{{-- CSRF Token --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Smart App Banner --}}
<meta name="apple-itunes-app" content="app-id=1254455001,app-argument={{ Request::fullUrl() }}">
