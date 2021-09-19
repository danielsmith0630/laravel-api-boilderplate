<?php

namespace App\Http\Controllers;

use App\Models\{
    Space,
    SpaceMember,
    SpaceMemberRole
};
use App\Http\Requests\SpaceMember\{
    SpaceMemberStoreRequest,
    SpaceMemberUpdateRequest
};
use Spatie\QueryBuilder\{
  QueryBuilder,
  AllowedFilter
};
use Illuminate\Http\Request;

/**
 * @group Space Member Management
 *
 * API endpoints for space member management
 */
class SpaceMemberController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(SpaceMember::class, 'member');
    }

    /**
     * Get Space Members
     *
     * Get all members of a specific space
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @responseFile responses/SpaceMember/members.json
     */
    public function index(Space $space)
    {
        return $this->jsonCollection(
            QueryBuilder::for($space->members()->getQuery())
              ->allowedIncludes([
                'user',
                'user.profile',
                'space',
                'role',
              ])
              ->allowedFilters(array_merge(
                SpaceMember::getFields(),
                [AllowedFilter::trashed()]
              ))
              ->jsonPaginate()
        );
    }

    /**
     * Create Space Member
     *
     * Create a member for a specified space
     *
     * @urlParam space int required The ID of the space. Example: 4
     * @responseFile responses/SpaceMember/member.json
     */
    public function store(SpaceMemberStoreRequest $request, Space $space)
    {
        $member = $space->members()->where('user_id', $request->user_id)->first();
        if ($member) {
            $profile = $request->user()->profile;
            return response()->json([
                'errors' => [
                    'user_id' => [
                        __('space.member_already_exist', [
                            'userName'=> $profile->first_name . ' ' . $profile->last_name,
                            'spaceName' => $space->name,
                        ])
                    ]
                ]
            ], 422);
        }

        $member = new SpaceMember;

        $member->fill($request->all());
        $member->user_id = $request->user_id;

        $space->members()->save($member);

        $role = new SpaceMemberRole;
        $role->space_id = $space->id;
        $role->user_id = $member->user_id;
        $role->role = 'member';
        $member->role()->save($role);

        return $this->jsonItem(
            $member->fresh()
        );
    }

    /**
     * Get Space Member
     *
     * Get a specific member of the space.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam id int required The ID of the space member. Example: 5
     * @responseFile responses/SpaceMember/member.json
     */
    public function show(Space $space, SpaceMember $member)
    {
        return $this->jsonItem(
          QueryBuilder::for(SpaceMember::class)
            ->allowedIncludes([
              'user',
              'user.profile',
              'space',
              'role',
            ])
            ->allowedFilters(array_merge(
              SpaceMember::getFields(),
              [AllowedFilter::trashed()]
            ))
            ->findOrFail($member->id)
        );
    }

    /**
     * Update Space Member
     *
     * Update the space member profile fields.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam id int required The ID of the space member. Example: 5
     * @responseFile responses/SpaceMember/member.json
     */
    public function update(SpaceMemberUpdateRequest $request, Space $space, SpaceMember $member)
    {
        $member->fill($request->all());
        $member->save();

        return $this->jsonItem(
            $member->fresh()
        );
    }

    /**
     * Delete Space Member
     *
     * Delete a space member profile. (mark as deleted)
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam id int required The ID of the space member. Example: 5
     * @response 204
     */
    public function destroy(Space $space, SpaceMember $member)
    {
        $member->role()->delete();
        $member->delete();
        return response(null, 204);
    }
}
