<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\WebUser;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function edit(Request $request)
    {
        $user = $request->user('sanctum');
        if (! $user instanceof WebUser) {
            return redirect()->route('login');
        }

        return view('website.profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user('sanctum');
        if (! $user instanceof WebUser) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        // Define validation rules for each field
        $validationRules = [];
        $fieldsToUpdate = [];

        // Only validate and update fields that are present in the request
        if ($request->has('first_name')) {
            $validationRules['first_name'] = 'required|string|max:255';
            $fieldsToUpdate[] = 'first_name';
        }

        if ($request->has('surname') || $request->has('last_name')) {
            $validationRules['surname'] = 'nullable|string|max:255';
            $validationRules['last_name'] = 'nullable|string|max:255';
            $fieldsToUpdate[] = 'surname';
        }

        if ($request->has('email')) {
            $validationRules['email'] = 'required|string|email|max:255|unique:web_users,email,'.$user->id;
            $fieldsToUpdate[] = 'email';
        }

        if ($request->has('phone')) {
            $validationRules['phone'] = 'nullable|string|max:32';
            $fieldsToUpdate[] = 'phone';
        }

        if ($request->has('personal_id')) {
            $validationRules['personal_id'] = 'nullable|string|max:50';
            $fieldsToUpdate[] = 'personal_id';
        }

        if ($request->has('birth_date')) {
            $validationRules['birth_date'] = 'nullable|date';
            $fieldsToUpdate[] = 'birth_date';
        }

        if ($request->has('gender')) {
            $validationRules['gender'] = 'nullable|string|max:20';
            $fieldsToUpdate[] = 'gender';
        }

        if ($request->has('password')) {
            $validationRules['password'] = 'required|string|min:8|confirmed';
            $validationRules['current_password'] = 'required|string';
            $fieldsToUpdate[] = 'password';
        }

        // Validate only the fields that are present
        $validated = $request->validate($validationRules);

        // Handle password change with current password verification
        if (in_array('password', $fieldsToUpdate)) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect.',
                    'errors' => ['current_password' => ['The current password is incorrect.']],
                ], 422);
            }
            $user->password = Hash::make($validated['password']);
        }

        // Update only the fields that were sent in the request
        foreach ($fieldsToUpdate as $field) {
            if ($field === 'password') {
                continue;
            } // Already handled above

            if ($field === 'surname' && ($request->has('last_name') || $request->has('surname'))) {
                // Handle both surname and last_name mapping to surname field
                $user->surname = $validated['last_name'] ?? $validated['surname'] ?? null;
            } else {
                $user->$field = $validated[$field] ?? null;
            }
        }

        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
                'data' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'surname' => $user->surname,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'personal_id' => $user->personal_id,
                    'birth_date' => $user->birth_date?->toDateString(),
                    'gender' => $user->gender,
                    'is_retailer' => $user->is_retailer,
                    'retailer_status' => $user->retailer_status,
                    'retailer_requested_at' => $user->retailer_requested_at,
                    'avatar' => $user->avatar,
                    'avatar_url' => $user->avatar ? asset('storage/'.$user->avatar) : null,
                ],
            ]);
        }

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    /**
     * POST /profile/retailer-request
     * Marks the user's retailer status as pending and sets requested_at.
     */
    public function requestRetailer(Request $request)
    {
        $user = $request->user('sanctum');
        if (! $user instanceof WebUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user->retailer_status = 'pending';
        $user->retailer_requested_at = now();
        $user->save();

        return response()->json([
            'message' => 'Retailer request submitted.',
            'data' => [
                'retailer_status' => $user->retailer_status,
                'retailer_requested_at' => $user->retailer_requested_at,
            ],
        ]);
    }

    /**
     * POST /profile/avatar
     * Accepts multipart/form-data with 'avatar' image file and stores it to public disk.
     */
    public function uploadAvatar(Request $request)
    {
        $user = $request->user('sanctum');
        if (! $user instanceof WebUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        // Upload new avatar as WebP and delete old one
        $file = $request->file('avatar');
        $quality = $this->imageService->getOptimalQuality($file);
        $path = $this->imageService->updateImage($file, $user->avatar, 'avatars', $quality);

        $user->avatar = $path;
        $user->save();

        return response()->json([
            'message' => 'Avatar updated successfully.',
            'data' => [
                'avatar' => $user->avatar,
                'avatar_url' => asset('storage/'.$user->avatar),
            ],
        ], 200);
    }
}
