@extends('layouts.app')

@section('title', 'Episode Details')

@section('content')
    <a href="{{ route('episodes.index') }}" class="muted">← Back to list</a>
    <h1 style="margin-top:8px">#{{ $episode->number }} — {{ $episode->title }}</h1>
    @if($episode->aired_at)
        <div class="muted">Aired: {{ $episode->aired_at }}</div>
    @endif
    <p>{{ $episode->synopsis }}</p>

    @if($episode->video_url)
    <div id="player" class="card" style="margin-top:12px">
        <iframe
            id="iframePlayer"
            src="{{ $episode->video_url }}"
            style="width:100%; aspect-ratio: 16 / 9; display:block;"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
        ></iframe>
    </div>
    @elseif($episode->embed_url)
    <div id="player" class="card" style="margin-top:12px">
        <iframe
            id="iframePlayer"
            src="{{ $episode->embed_url }}"
            style="width:100%; aspect-ratio: 16 / 9; display:block;"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
        ></iframe>
    </div>
    @endif

    <div style="margin-top:12px">
        <button class="btn" id="markBtn" data-episode-id="{{ $episode->id }}">Mark as current</button>
        <span id="status" class="muted" style="margin-left:8px"></span>
    </div>

    <script>
    async function setProgress(id) {
        try {
            const res = await fetch('/api/progress', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ episode_id: id })
            });
            const json = await res.json();
            document.getElementById('status').innerText = json.status === 'ok' ? 'Saved!' : 'Failed';
        } catch (e) {
            document.getElementById('status').innerText = 'Error saving';
        }
    }
    document.getElementById('markBtn').addEventListener('click', () => {
        const id = Number(document.getElementById('markBtn').dataset.episodeId);
        setProgress(id);
    });
    const video = document.getElementById('video');
    if (video) {
        video.addEventListener('play', () => {
            const id = Number(video.dataset.episodeId);
            setProgress(id);
        });
    }
    </script>
@endsection