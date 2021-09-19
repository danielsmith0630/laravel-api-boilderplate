<?php

namespace App\Http\Controllers;

use App\Models\{
    Space,
    SpaceMember,
    SpaceMemberRole
};
use App\Http\Requests\SpaceMemberRole\{
    SpaceMemberRoleStoreRequest,
    SpaceMemberRoleUpdateRequest
};
use Illuminate\Http\Request;

/**
 * @group Space Member Role Management
 * 
 * APIs for managing space member roles
 */
class SpaceMemberRoleController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(SpaceMemberRole::class, 'role');
    }

    /**
     * Get Space Member Roles
     * 
     * Get a listing of the space member roles.
     * 
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam member_id int required The ID of the space member. Example: 5
     * @responseFile responses/SpaceMemberRole/role.json
     */
    public function index(Space $space, SpaceMember $member)
    {
        return $this->jsonItem(
            $member->role
        );
    }

    /**
     * Create Space Member Role
     * 
     * Create a space member role.
     *
     * @urlParam space int required The ID of the space. Example: 4
     * @urlParam member int required The ID of the space member. Example: 5
     * @responseFile responses/SpaceMemberRole/role.json
     */
    public function store(SpaceMemberRoleStoreRequest $request, Space $space, SpaceMember $member)
    {
        $role = new SpaceMemberRole;

        $role->fill($request->all());
        $role->space_id = $space->id;
        $role->user_id = $member->user_id;

        $member->role()->save($role);

        return $this->jsonItem(
            $role->fresh()
        );
    }

    /**
     * Get Space Member Role
     * 
     * Get a space member role with the specified id.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam member_id int required The ID of the space member. Example: 5
     * @urlParam id int required The ID of the role. Example: 4
     * @responseFile responses/SpaceMemberRole/role.json
     */
    public function show(Space $space, SpaceMember $member, SpaceMemberRole $role)
    {
        return $this->jsonItem(
            $role
        );
    }

    /**
     * Update Space Member Role
     * 
     * Update the specified space member role in storage.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam member_id int required The ID of the space member. Example: 5
     * @urlParam id int required The ID of the space member role. Example: 4
     * @responseFile responses/SpaceMemberRole/role.json
     */
    public function update(SpaceMemberRoleUpdateRequest $request, Space $space, SpaceMember $member, SpaceMemberRole $role)
    {
        $role->fill($request->all());
        $role->save();

        return $this->jsonItem(
            $role
        );
    }

    /**
     * Transfer Space Ownership
     * 
     * Transfer the space ownership to the other member.
     *
     * @urlParam space int required The ID of the space. Example: 4
     * @urlParam member int required The ID of the space member. Example: 5
     * @urlParam role int required The ID of the space member role. Example: 4
     * @responseFile responses/SpaceMemberRole/role.transfer-owner.json
     */
    public function transferOwnership(Request $request, Space $space, SpaceMember $member, SpaceMemberRole $role)
    {
        $this->authorize('transferOwnership', $role);

        $authMember = $space->members()->where('user_id', $request->user()->id)->first();
        $authMemberRole = $authMember->role;
        $authMemberRole->role = 'admin';
        $authMemberRole->save();

        $role->role = 'owner';
        $role->save();

        $space->owner_id = $member->user_id;
        $space->save();

        return $this->jsonItem(
            $role
        );
    }

    /**
     * Delete Space Member Role
     * 
     * Delete the specified space member role from storage (mark as deleted).
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam member_id int required The ID of the space member. Example: 5
     * @urlParam id int required The ID of the space member role. Example: 4
     * @response 204
     */
    public function destroy(Space $space, SpaceMember $member, SpaceMemberRole $role)
    {
        $role->delete();
        return response(null, 204);
    }
}
