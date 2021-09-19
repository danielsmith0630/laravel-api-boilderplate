<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Http\Requests\UserProfile\{
    UserProfileStoreRequest,
    UserProfileUpdateRequest,
    UserProfileImageRequest
};
use App\Services\FileService;

/**
 * @group User Profile Management
 *
 * API endpoints for user primary profile management
 */
class UserProfileController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(UserProfile::class, 'profile');
    }

    /**
     * Get User Profiles
     *
     * Get the primary profile for a user. In the future,
     * when users have multiple profiles, it will return an array of profiles but
     * at the moment, it just returns a profile object.
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @responseFile responses/UserProfile/profile.json
     */
    public function index(User $user)
    {
        return $this->jsonItem(
            $user->profile
        );
    }

    /**
     * Create User Profile
     *
     * Create a primary profile for a user.
     *
     * @urlParam user int required The ID of the user. Example: 2
     * @responseFile responses/UserProfile/profile-create.json
     */
    public function store(UserProfileStoreRequest $request, User $user)
    {
        $profile = new UserProfile;

        $profile->fill($request->all());

        $user->profile()->save($profile);

        return $this->jsonItem(
            $profile->fresh()
        );
    }

    /**
     * Upload User Profile Images
     *
     * Upload user profile avatar and banner images.
     * 
     * @urlParam user int required The ID of the user. Example: 2
     * @urlParam profile int required The ID of the user profile. Example: 3
     * @responseFile responses/UserProfile/profile.json
     */
    public function uploadImages(UserProfileImageRequest $request, User $user, UserProfile $profile)
    {
        $this->authorize('update', $profile);

        $profile = $user->profile;

        if ($request->avatar) {
            $diplayState = $request->input('avatar_display_state', 'normal') ?? 'normal';
            $avatar = $profile
                ->avatars()
                ->where('display_state', $diplayState)
                ->firstOrCreate([ 'display_state' => $diplayState ]);

            if ($avatar->file) {
                $avatar->file->delete();
            }

            $fileService = new FileService(
                $request,
                $avatar,
                '/avatars',
                FileService::$TYPE_AVATAR,
                null
            );
            $fileService->upload();
        }
        
        if ($request->banner) {
            $diplayState = $request->input('banner_display_state', 'normal') ?? 'normal';
            $banner = $profile
                ->banners()
                ->where('display_state', $diplayState)
                ->firstOrCreate([ 'display_state' => $diplayState ]);

            if ($banner->file) {
                $banner->file->delete();
            }

            $fileService = new FileService(
                $request,
                $banner,
                '/banners',
                FileService::$TYPE_BANNER,
                null
            );
            $fileService->upload();
        }

        return $this->jsonItem(
            $user->profile->fresh()
        );
    }

    /**
     * Get User Profile
     *
     * Get the primary profile for a user
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @urlParam id int required The ID of the user primary profile. Example: 3
     * @responseFile responses/UserProfile/profile.json
     */
    public function show(User $user, UserProfile $profile)
    {
        return $this->jsonItem(
            $user->profile
        );
    }

    /**
     * Update User Profile
     *
     * Update the user primary profile
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @urlParam id int required The ID of the user primary profile. Example: 3
     * @responseFile responses/UserProfile/profile.json
     */
    public function update(UserProfileUpdateRequest $request, User $user, UserProfile $profile)
    {
        $user->profile->fill($request->all())->save();

        return $this->jsonItem(
            $user->profile->fresh()
        );
    }

    /**
     * Delete User Profile
     *
     * Delete the user primary profile. (mark as deleted)
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @urlParam id int required The ID of the user primary profile. Example: 3
     * @response 204
     */
    public function destroy(User $user, UserProfile $profile)
    {
        $user->profile()->delete();
        return response(null, 204);
    }
}
