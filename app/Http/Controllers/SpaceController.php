<?php

namespace App\Http\Controllers;

use App\Models\{
    Space,
    SpaceMember,
    SpaceMemberRole,
    Avatar,
    Banner
};
use App\Http\Requests\Space\{
    SpaceStoreRequest,
    SpaceUpdateRequest,
    SpaceUploadImageRequest,
};
use Spatie\QueryBuilder\{
  QueryBuilder,
  AllowedFilter
};
use App\Services\FileService;
use Illuminate\Http\Request;

/**
 * @group Space Management
 *
 * API endpoints for space management
 */
class SpaceController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Space::class, 'space');
    }

    /**
     * Get Spaces
     *
     * Get all spaces
     *
     * @responseFile responses/Space/spaces.json
     */
    public function index()
    {
        return $this->jsonCollection(
            QueryBuilder::for(Space::class)
                ->allowedIncludes([
                    'creator',
                    'owner',
                    'avatars',
                    'banners',
                    'members',
                    'channels'
                ])
                ->allowedFilters(array_merge(
                    Space::getFields(),
                    [AllowedFilter::trashed()]
                ))
                ->jsonPaginate()
        );
    }

    /**
     * Create Space
     *
     * Create a space and register the auth user as an owner.
     *
     * @responseFile responses/Space/space-create.json
     */
    public function store(SpaceStoreRequest $request)
    {
        $space = new Space;

        $space->fill($request->all());

        $request->user()->createdSpaces()->save($space);

        $member = new SpaceMember;
        $member->user_id = $request->user()->id;

        $space->members()->save($member);

        $role = new SpaceMemberRole;
        $role->space_id = $space->id;
        $role->user_id = $member->user_id;
        $role->role = 'owner';
        $member->role()->save($role);

        return $this->jsonItem(
            $space->fresh()
        );
    }

    /**
     * Upload Space Avatar and Banner
     * 
     * Upload avatar and banner of the space
     *
     * @urlParam space int required The ID of the space. Example: 4
     * @responseFile responses/Space/space.json
     */
    public function uploadImages(SpaceUploadImageRequest $request, Space $space)
    {
        $this->authorize('update', $space);

        if ($request->avatar) {
            $displayState = $request->input('avatar_display_state', 'normal') ?? 'normal';
            $avatar = $space
                ->avatars()
                ->where('display_state', $displayState)
                ->firstOrCreate([ 'display_state' => $displayState ]);

            if ($avatar->file) {
                $avatar->file->delete();
            }

            $fileService = new FileService(
                $request,
                $avatar,
                '/avatars',
                FileService::$TYPE_AVATAR,
                $space->id
            );
            $fileService->upload();
        }
        
        if ($request->banner) {
            $displayState = $request->input('banner_display_state', 'normal') ?? 'normal';
            $banner = $space
                ->banners()
                ->where('display_state', $displayState)
                ->firstOrCreate([ 'display_state' => $displayState ]);

            if ($banner->file) {
                $banner->file->delete();
            }

            $fileService = new FileService(
                $request,
                $banner,
                '/banners',
                FileService::$TYPE_BANNER,
                $space->id
            );
            $fileService->upload();
        }

        return $this->jsonItem(
            $space->fresh()
        );
    }

    /**
     * Get Space
     * 
     * Get a space with specific id
     *
     * @urlParam id int required The ID of the space. Example: 4
     * @responseFile responses/Space/space.json
     */
    public function show(Space $space)
    {
        return $this->jsonItem(
            QueryBuilder::for(Space::class)
              ->allowedIncludes([
                'creator',
                'owner',
                'avatars',
                'banners',
                'members',
                'channels'
              ])
              ->allowedFilters(array_merge(
                Space::getFields(),
                [AllowedFilter::trashed()]
              ))
              ->findOrFail($space->id)
        );
    }

    /**
     * Update Space
     * 
     * Update the fields of a space
     *
     * @urlParam id int required The ID of the space. Example: 4
     * @responseFile responses/Space/space.json
     */
    public function update(SpaceUpdateRequest $request, Space $space)
    {
        $space->update($request->all());

        return $this->jsonItem(
            $space->fresh()
        );
    }

    /**
     * Delete Space
     *
     * Delete the space with a specific id. (mark as deleted)
     *
     * @urlParam id int required The ID of the space. Example: 4
     * @response 204
     */
    public function destroy(Space $space)
    {
        $space->delete();
        return response(null, 204);
    }
}
