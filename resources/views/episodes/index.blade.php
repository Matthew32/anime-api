@extends('layouts.app')

@section('title', 'One Piece Episodes')

@section('content')
    <div class="banner">
        <div style="font-size:28px">🗺️</div>
        <div>
            <div class="title">Grand Line Logbook</div>
            <div class="muted">Browse One Piece episodes and mark your current voyage.</div>
        </div>
    </div>

    <div id="current" class="card" style="margin-bottom:16px">
        <strong>Current episode:</strong>
        @if($current)
            <a id="currentLink" href="/episodes/{{ $current->id }}">
                <span id="currentText">#{{ $current->number }} — {{ $current->title }}</span>
            </a>
        @else
            <a id="currentLink" href="#" class="muted">
                <span id="currentText">Loading…</span>
            </a>
        @endif
    </div>

    <div class="grid">
        @foreach($episodes as $ep)
            <div class="card">
                <div class="muted">#{{ $ep->number }}</div>
                <h3 style="margin:6px 0">{{ $ep->title }}</h3>
                @if($ep->aired_at)
                    <div class="muted">Aired: {{ $ep->aired_at }}</div>
                @endif
                <p style="min-height:48px">{{ $ep->synopsis }}</p>
                <a class="btn" href="{{ route('episodes.show', ['id' => $ep->id]) }}">Open</a>
                @if($ep->video_url)
                    <a class="btn secondary" href="{{ route('episodes.show', ['id' => $ep->id]) }}#player">Watch</a>
                @endif
            </div>
        @endforeach
    </div>

    <div class="pagination" style="margin-top:16px">
        <div>
            @if (!$episodes->onFirstPage())
                <a class="pager-btn" href="{{ $episodes->url(1) }}">« First</a>
                <a class="pager-btn" href="{{ $episodes->previousPageUrl() }}">← Previous</a>
            @else
                <span class="pager-btn disabled">« First</span>
                <span class="pager-btn disabled">← Previous</span>
            @endif
        </div>
        <div class="muted">Page {{ $episodes->currentPage() }} of {{ $episodes->lastPage() }} • {{ $episodes->perPage() }} per page</div>
        <div>
            @if ($episodes->hasMorePages())
                <a class="pager-btn" href="{{ $episodes->nextPageUrl() }}">Next →</a>
                <a class="pager-btn" href="{{ $episodes->url($episodes->lastPage()) }}">Last »</a>
            @else
                <span class="pager-btn disabled">Next →</span>
                <span class="pager-btn disabled">Last »</span>
            @endif
        </div>
    </div>

    <script>
    async function loadProgress() {
        try {
            const res = await fetch('/api/progress', { credentials: 'same-origin' });
            const json = await res.json();
            const el = document.getElementById('currentText');
            const link = document.getElementById('currentLink');
            if (json && json.episode) {
                el.innerText = `#${json.episode.number} — ${json.episode.title}`;
                if (link) {
                    link.href = `/episodes/${json.episode.id}`;
                    link.classList.remove('muted');
                }
            } else {
                el.innerText = 'None yet';
                if (link) {
                    link.removeAttribute('href');
                    link.classList.add('muted');
                }
            }
        } catch (e) {
            document.getElementById('currentText').innerText = 'Unavailable';
        }
    }
    loadProgress();
    </script>
@endsection