<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DogController extends Controller
{
    public function index()
    {
        $breeds = Cache::remember('dog_breeds_list', now()->addHours(1), function () {
            $response = Http::get('https://dog.ceo/api/breeds/list/all');
            return collect($response->json('message'))->keys();
        });

        // Fetch liked dogs for the authenticated user
        $likedDogs = auth()->user()->dogSelections()->pluck('dog_breed')->toArray();

        return Inertia::render('Dashboard', [
            'dogs' => $breeds,
            'likedDogs' => $likedDogs,
        ]);
    }

    public function fetchImage($breed)
    {
        // Cache each breed's image separately
        $image = Cache::remember("dog_breed_image_{$breed}", now()->addHours(1), function () use ($breed) {
            $response = Http::get("https://dog.ceo/api/breed/{$breed}/images/random");
            return $response->json('message');
        });

        return response()->json(['breed' => $breed, 'image' => $image]);
    }

    public function getLikes($breed)
    {
        // Retrieve users who liked the specified breed
        $users = User::whereHas('dogSelections', function ($query) use ($breed) {
            $query->where('dog_breed', $breed);
        })->pluck('name'); // Assuming users have a 'name' field

        return response()->json(['breed' => $breed, 'users' => $users]);
    }
}
