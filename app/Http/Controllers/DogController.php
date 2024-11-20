<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DogController extends Controller
{
    public function index()
    {
        $breedsWithImages = Cache::remember('dog_breeds_with_images', now()->addHours(1), function () {
            // Fetch all breeds
            $response = Http::get('https://dog.ceo/api/breeds/list/all');
            $breeds = collect($response->json('message'))->keys();

            // Fetch random images for each breed
            return $breeds->map(function ($breed) {
                $imageResponse = Http::get("https://dog.ceo/api/breed/{$breed}/images/random");
                return [
                    'breed' => $breed,
                    'image' => $imageResponse->json('message'),
                ];
            });
        });

        $likedDogs = auth()->user()->dogSelections()->pluck('dog_breed')->toArray();

        return Inertia::render('Dashboard', [
            'dogs' => $breedsWithImages,
            'likedDogs' => $likedDogs
        ]);
    }

}
