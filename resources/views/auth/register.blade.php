<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Create your account</h2>
        </div>
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            @isset($errors)
                @if(count($errors) > 0)
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach($errors as $fieldErrors)
                                @foreach((array)$fieldErrors as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endisset

            <form class="space-y-6" action="/register" method="POST">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Full Name</label>
                    <input id="name" name="name" type="text" required
                           class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm px-3">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
                    <input id="email" name="email" type="email" required
                           class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm px-3">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                    <input id="password" name="password" type="password" required
                           class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm px-3">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm px-3">
                </div>
                <button type="submit"
                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500">Register</button>
            </form>
            <p class="mt-10 text-center text-sm text-gray-500">
                Already have an account? <a href="/login" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
