<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Server Error | InvoiceKit</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; font-family: Figtree, ui-sans-serif, system-ui, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border-radius: 1rem; padding: 3rem 2.5rem; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,.07); }
        .code { font-size: 6rem; font-weight: 800; color: #ef4444; line-height: 1; margin-bottom: .5rem; }
        h1 { font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0 0 .75rem; }
        p { color: #6b7280; margin: 0 0 2rem; line-height: 1.6; }
        a { display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: .625rem 1.5rem; border-radius: .5rem; font-weight: 600; font-size: .9rem; transition: background .15s; }
        a:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">500</div>
        <h1>Server Error</h1>
        <p>Something went wrong on our end. We've been notified and are working to fix it.</p>
        <a href="{{ url('/') }}">Back to Home</a>
    </div>
</body>
</html>
