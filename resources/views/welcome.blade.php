@extends('layouts.app')

@section('title', 'One Piece Explorer')

@section('content')
    <section class="banner" style="align-items:flex-start">
        <div style="font-size:34px">☠️</div>
        <div>
            <div class="title" style="font-size:28px">Welcome to the Grand Line</div>
            <div class="muted">Chart your voyage through One Piece — browse, watch, and track progress.</div>
            <div style="margin-top:12px">
                <a class="btn" href="{{ route('episodes.index') }}">Enter the Grand Line</a>
                <a class="btn secondary" href="{{ route('episodes.index') }}?per_page=50" style="margin-left:8px">Browse Episodes</a>
            </div>
        </div>
    </section>

    <div class="grid">
        <div class="card">
            <h3 style="margin-top:0">Captain's Log</h3>
            <p class="muted" id="homeCurrent">Loading current episode…</p>
            <a class="btn" id="homeContinue" href="{{ route('episodes.index') }}">Continue Watching</a>
        </div>
        <div class="card">
            <h3 style="margin-top:0">About</h3>
            <p class="muted">This explorer lists all 1,554 episodes. Each episode links to a watch page and renders in an embedded player (where allowed).</p>
            <p class="muted">Use the Episodes page to navigate by number and mark your progress.</p>
        </div>
        <div class="card">
            <h3 style="margin-top:0">Tips</h3>
            <ul style="margin:0 0 8px 18px">
                <li>Pagination shows 50 episodes per page.</li>
                <li>Embedded players may be blocked by providers.</li>
                <li>Use the Watch button to jump to the player.</li>
            </ul>
        </div>
    </div>

    <script>
    async function loadHomeProgress() {
        try {
            const res = await fetch('/api/progress', { credentials: 'same-origin' });
            const json = await res.json();
            const el = document.getElementById('homeCurrent');
            const btn = document.getElementById('homeContinue');
            if (json && json.episode) {
                el.innerText = `Currently at #${json.episode.number} — ${json.episode.title}`;
                if (btn) btn.href = `/episodes/${json.episode.id}`;
            } else {
                el.innerText = 'No current episode yet — start at Episode 1!';
                if (btn) btn.href = `{{ route('episodes.index') }}`;
            }
        } catch (e) {
            document.getElementById('homeCurrent').innerText = 'Progress unavailable';
            const btn = document.getElementById('homeContinue');
            if (btn) btn.href = `{{ route('episodes.index') }}`;
        }
    }
    loadHomeProgress();
    </script>
@endsection
