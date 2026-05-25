<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProfile;
use App\Models\Hotel;
use Illuminate\Support\Facades\Storage;

class OwnerOnboardingController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $profile = $user->profile;
        return view('owner.onboarding', compact('profile'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'quota_details' => 'required|array',
            'quota_details.hotel_id' => 'nullable|integer|exists:hotels,id',
            'quota_details.hotel_name' => 'nullable|string|max:255',
            'quota_details.number_of_rooms' => 'nullable|integer|min:1',
            'quota_details.size' => 'nullable|string|max:50',
            'quota_details.seasonality' => 'nullable|string|max:100',
            'quota_details.notes' => 'nullable|string|max:1000',
            'quota_photos' => 'nullable|array',
            'quota_photos.*' => 'image|mimes:jpeg,jpg,png',
        ]);

        $user = Auth::user();
        $profile = $user->profile;
        $data = $profile->quota_details ?? [];
        $updated = array_merge($data, $request->quota_details);
        // Upload photos
        if ($request->hasFile('quota_photos')) {
            $paths = $updated['photos'] ?? [];
            foreach ($request->file('quota_photos') as $photo) {
                $paths[] = $photo->store('quota_photos', 'public');
            }
            $updated['photos'] = $paths;
        }

        $profile->update(['quota_details' => $updated]);

        return redirect()->route('dashboard')->with('success', 'Detalhes da cota atualizados com sucesso.');
    }

    /**
     * Remove a photo from quota_details and storage.
     */
    public function deletePhoto(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $user = Auth::user();
        $profile = $user->profile;
        $details = $profile->quota_details ?? [];
        $photos = $details['photos'] ?? [];

        $path = $request->input('path');
        $index = array_search($path, $photos, true);
        if ($index === false) {
            return response()->json(['message' => 'Foto não encontrada.'], 404);
        }

        // Remove from array and storage
        unset($photos[$index]);
        $photos = array_values($photos);
        $details['photos'] = $photos;
        $profile->update(['quota_details' => $details]);

        // Best effort delete
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(['message' => 'Foto removida com sucesso.']);
    }
}

