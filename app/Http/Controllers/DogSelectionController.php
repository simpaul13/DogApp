<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserDogSelection;
use Inertia\Inertia;

class DogSelectionController extends Controller
{
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'dogs' => 'required|array|max:3',
            'dogs.*' => 'string',
        ]);

        // Save user's dog selections
        $user = Auth::user();

        // Clear previous selections
        $user->dogSelections()->delete();

        // Save new selections
        foreach ($request->dogs as $breed) {
            $user->dogSelections()->create(['dog_breed' => $breed]);
        }

    }

    public function index()
    {
        // Retrieve all users and their selected dogs
        $users = User::with('dogSelections:dog_breed,user_id')->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'dogs' => $user->dogSelections->pluck('dog_breed'),
                ];
            });

        return Inertia::render('UserDogSelections', [
            'users' => $users,
        ]);
    }
}
