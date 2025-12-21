@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="banner">
        <div style="font-size:28px">📝</div>
        <div>
            <div class="title">Set your captain name</div>
            <div class="muted">Enter a name to personalize your session.</div>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required minlength="2" maxlength="50" style="display:block;width:100%;padding:8px;margin-top:6px;border:1px solid #d1b073;border-radius:8px" />
            <button class="btn" type="submit" style="margin-top:10px">Enter</button>
        </form>
    </div>
@endsection