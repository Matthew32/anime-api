<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)($request->query('per_page', 25));
        $episodes = Episode::orderBy('number')->paginate($perPage);
        return response()->json($episodes);
    }

    public function show(int $id)
    {
        $episode = Episode::findOrFail($id);
        return response()->json($episode);
    }
}
