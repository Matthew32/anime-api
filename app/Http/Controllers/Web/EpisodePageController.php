<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EpisodePageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)($request->query('per_page', 50));
        $episodes = Episode::orderBy('number')->paginate($perPage);

        $sessionId = $request->session()->getId();
        $row = DB::table('session_progress')->where('session_id', $sessionId)->first();
        $current = $row ? Episode::find($row->episode_id) : null;

        // If a current episode exists and page is not specified, redirect
        // to the pagination page that contains the current episode.
        if ($current && !$request->has('page')) {
            $targetPage = (int) ceil($current->number / max(1, $perPage));
            return redirect()->route('episodes.index', ['page' => $targetPage, 'per_page' => $perPage]);
        }

        return view('episodes.index', compact('episodes', 'current'));
    }

    public function show(Request $request, int $id)
    {
        $episode = Episode::findOrFail($id);
        return view('episodes.show', compact('episode'));
    }
}
