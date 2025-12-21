<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function get(Request $request)
    {
        $sessionId = $request->session()->getId();
        $row = DB::table('session_progress')->where('session_id', $sessionId)->first();
        if (!$row) {
            return response()->json(['episode' => null]);
        }
        $episode = Episode::find($row->episode_id);
        return response()->json(['episode' => $episode]);
    }

    public function set(Request $request)
    {
        $validated = $request->validate([
            'episode_id' => ['required', 'integer', 'exists:episodes,id'],
        ]);
        $sessionId = $request->session()->getId();
        DB::table('session_progress')->updateOrInsert(
            ['session_id' => $sessionId],
            ['episode_id' => $validated['episode_id'], 'updated_at' => now(), 'created_at' => now()]
        );
        return response()->json(['status' => 'ok']);
    }
}
