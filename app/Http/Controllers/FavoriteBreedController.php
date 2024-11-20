<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserDogSelection;
use App\Models\User;
use Inertia\Inertia;

class FavoriteBreedController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Fetch the authenticated user's liked dogs
        $myLikedDogs = $user->dogSelections->pluck('dog_breed');

        // Fetch all other users and their liked dogs
        $otherUsers = User::with('dogSelections:dog_breed,user_id')
            ->where('id', '!=', $user->id)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'likedDogs' => $user->dogSelections->pluck('dog_breed'),
                ];
            });

        return Inertia::render('FavoriteBreeds', [
            'myLikedDogs' => $myLikedDogs,
            'otherUsers' => $otherUsers,
        ]);
    }
}
