<?php

namespace App\Http\Controllers;

use App\Models\{
    Space,
    SpacePrivacySetting
};
use App\Http\Requests\SpacePrivacySetting\{
    SpacePrivacySettingStoreRequest,
    SpacePrivacySettingUpdateRequest
};
use Illuminate\Http\Request;

/**
 * @group Space Privacy Setting Management
 * 
 * APIs for managing space privacy settings
 */
class SpacePrivacySettingController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(SpacePrivacySetting::class, 'privacy_setting');
    }

    /**
     * Get Space Privacy Settings
     * 
     * Get the privacy setting for the specified space.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @responseFile responses/SpacePrivacySetting/setting.json
     */
    public function index(Space $space)
    {
        return $this->jsonItem(
            $space->privacySetting
        );
    }

    /**
     * Create Space Privacy Setting
     * 
     * Create the privacy setting of a space.
     *
     * @urlParam space int required The ID of the space. Example: 4
     * @responseFile responses/SpacePrivacySetting/setting.json
     */
    public function store(SpacePrivacySettingStoreRequest $request, Space $space)
    {
        $space->privacySetting
            ->fill($request->all())
            ->save();

        return $this->jsonItem(
            $space->privacySetting
        );
    }

    /**
     * Get Space Privacy Setting
     * 
     * Get the privacy setting with a space.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam privacy_setting int required The ID of the space privacy setting. Example: 1
     * @responseFile responses/SpacePrivacySetting/setting.json
     */
    public function show(Space $space, SpacePrivacySetting $privacy_setting)
    {
        return $this->jsonItem(
            $space->privacySetting
        );
    }

    /**
     * Update Space Privacy Setting
     * 
     * Update the specified space privacy setting in storage.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam privacy_setting int required The ID of the spacePrivacySetting. Example: 1
     * @responseFile responses/SpacePrivacySetting/setting.json
     */
    public function update(SpacePrivacySettingUpdateRequest $request, Space $space, SpacePrivacySetting $privacy_setting)
    {
        $space->privacySetting
            ->fill($request->all())
            ->save();
        
        return $this->jsonItem(
            $space->privacySetting
        );
    }

    /**
     * Delete Space Privacy Setting
     * 
     * Delete the specified space privacy setting from storage (mark as deleted).
     *
     * @urlParam space_id int required The ID of the user. Example: 4
     * @urlParam privacy_setting int required The ID of the space privacy setting. Example: 1
     * @response 204
     */
    public function destroy(Space $space, SpacePrivacySetting $privacy_setting)
    {
        $space->privacySetting()->delete();
        return response(null, 204);
    }
}
