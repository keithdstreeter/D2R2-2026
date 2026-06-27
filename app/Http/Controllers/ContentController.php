<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ContentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // $since = $request->query('since');
        // $sinceDate = $since ? Carbon::parse(str_replace(' ', '+', $since)) : null;

        // $query = Movie::query()
        //     ->active()
        //     ->with(['ageGroup', 'questions' => function ($q) use ($sinceDate) {
        //         $q->active();

        //         if ($sinceDate) {
        //             $q->where('created_at', '>', $sinceDate);
        //         }

        //         $q->with('choices');
        //     }]);

        // if ($sinceDate) {
        //     $query->whereHas('questions', function ($q) use ($sinceDate) {
        //         $q->active()->where('created_at', '>', $sinceDate);
        //     });
        // }

        // $movies = $query->orderBy('sort_order')->get();

        // return response()->json([
        //     'movies' => $movies->map(fn (Movie $movie) => [
        //         'title' => $movie->title,
        //         'slug' => $movie->slug,
        //         'age_group_code' => $movie->ageGroup->code,
        //         'release_year' => $movie->release_year,
        //         'poster_path' => $movie->poster_path,
        //         'description' => $movie->description,
        //         'sort_order' => $movie->sort_order,
        //         'questions' => $movie->questions->map(fn ($question) => [
        //             'prompt' => $question->prompt,
        //             'difficulty' => $question->difficulty,
        //             'kind' => $question->kind,
        //             'explanation' => $question->explanation,
        //             'choices' => $question->choices->map(fn ($choice) => [
        //                 'label' => $choice->label,
        //                 'text' => $choice->text,
        //                 'is_correct' => $choice->is_correct,
        //             ])->values()->all(),
        //         ])->values()->all(),
        //     ])->values()->all(),
        //     'timestamp' => now()->toIso8601String(),
        // ]);

        $cuesheets = $query->orderBy('id')->get();

        return response()->json([
            'cuesheets' => $cuesheets->map(fn (Movie $cuesheet) => [
                'turn' => $cuesheet->turn,
                'notes' => $cuesheet->slug,
                'distance' => $cuesheet->distance,
            ])->values()->all(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function displayImage(Request $request): string
    {
        $imagePath = public_path('icon.png');

        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        return response()->file($imagePath);

        // $imageData = base64_encode(file_get_contents($imagePath));
        // $mimeType = mime_content_type($imagePath);

    //     return response()->json([
    //         'image' => 'data:' . $mimeType . ';base64,' . $imageData,
    //     ]);
    // }
    }
}
