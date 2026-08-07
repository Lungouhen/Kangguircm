<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/images/favicon.png">
    @if(isset($seo))
        {!! \App\Helpers\SeoHelper::metaTags($seo) !!}
    @endif
    <title>{{ $pageTitle ?? 'Blog' }} | Multi-Module Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900">
    {{-- Navigation --}}
    <nav class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <img src="/images/logo.png" alt="Logo" class="h-8 w-8 rounded">
                    <a href="/" class="text-xl font-bold">Platform</a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="/blog" class="text-gray-300 hover:text-white text-sm">Blog</a>
                    <a href="/login" class="text-gray-300 hover:text-white text-sm">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm">&copy; {{ date('Y') }} Multi-Module Platform. Built with PHP 8.3 + Blade.</p>
        </div>
    </footer>

    @if(isset($seo))
        {!! \App\Helpers\SeoHelper::articleJsonLd($seo) !!}
    @endif
</body>
</html>
