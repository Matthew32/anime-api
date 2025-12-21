<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Anime API')</title>
    <style>
        :root {
            --parchment: #fff2dc;
            --parchment-deep: #f7e2bc;
            --ink: #1a202c;
            --navy: #0c2340;
            --gold: #d4a017;
            --rope: #b7791f;
        }
        body {
            font-family: Georgia, Garamond, "Times New Roman", -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial;
            margin: 0;
            background: radial-gradient(ellipse at top, var(--parchment) 0%, var(--parchment-deep) 100%);
            color: var(--ink);
        }
        header {
            background: linear-gradient(90deg, var(--navy), #122c5a);
            color: #fff;
            padding: 16px 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        header .brand {
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-right: 16px;
        }
        header a { color: #fff; text-decoration: none; margin-right: 12px; opacity: 0.9; }
        header a:hover { opacity: 1; text-decoration: underline; }
        main {
            max-width: 1060px; margin: 24px auto; background: #fffaf0; padding: 28px; border-radius: 12px;
            box-shadow: 0 10px 24px rgba(0,0,0,0.15);
            border: 2px dashed var(--rope);
        }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; }
        .card {
            border: 2px dashed var(--rope);
            border-radius: 10px; padding: 14px; background: #fffef6;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .card:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(0,0,0,0.12); }
        .btn { display: inline-block; padding: 8px 12px; border-radius: 8px; background: linear-gradient(180deg, #e6b84d, var(--gold)); color: #212121; text-decoration: none; border: 1px solid #b0851c; }
        .btn:hover { filter: brightness(1.05); }
        .btn.secondary { background: linear-gradient(180deg, #31425f, #1f2e47); color: #fff; border-color: #1b2a43; }
        .muted { color: #6b7280; }
        .banner { display:flex; gap:12px; align-items:center; padding:12px 14px; background:#fff8e6; border:2px dashed var(--rope); border-radius:10px; margin-bottom:16px; }
        .banner .title { font-size: 22px; font-weight: 700; }
        .pagination { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 14px; background:#fff8e6; border:2px dashed var(--rope); border-radius:10px; }
        .pager-btn { display:inline-block; padding:6px 10px; border-radius:8px; background:#fff; border:1px solid #d1b073; color:#1f2e47; text-decoration:none; }
        .pager-btn:hover { filter:brightness(1.05); }
        .pager-btn.disabled { opacity:0.55; pointer-events:none; }
    </style>
</head>
<body>
    <header>
        <span class="brand">🏴‍☠️ One Piece Explorer</span>
        <a href="/">Home</a>
        <a href="{{ route('episodes.index') }}">Episodes</a>
        <span style="float:right">
            @if(session('viewer_name'))
                <span class="muted" style="margin-right:8px">Sailing as {{ session('viewer_name') }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button class="btn secondary" type="submit">Logout</button>
                </form>
            @else
                <a href="{{ route('login.show') }}" class="btn secondary">Login</a>
            @endif
        </span>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>