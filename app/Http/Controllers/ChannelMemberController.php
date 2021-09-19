<?php

namespace App\Http\Controllers;

use App\Models\{
    Space,
    Channel,
    ChannelMember
};
use App\Http\Requests\ChannelMember\{
    ChannelMemberStoreRequest,
    ChannelMemberUpdateRequest
};
use Spatie\QueryBuilder\{
  QueryBuilder,
  AllowedFilter
};

use Illuminate\Http\Request;

/**
 * @group Channel Member Management
 *
 * APIs for managing channel members
 */
class ChannelMemberController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(ChannelMember::class, 'member');
    }

    /**
     * Get Channel Members
     *
     * Get a listing of the channel members.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam channel_id int required The ID of the channel. Example: 1
     * @responseFile responses/ChannelMember/members.json
     */
    public function index(Space $space, Channel $channel)
    {
        return $this->jsonCollection(
            QueryBuilder::for($channel->members()->getQuery())
                ->allowedIncludes([
                    'user',
                    'user.profile',
                    'channel'
                ])
                ->allowedFilters(array_merge(
                    ChannelMember::getFields(),
                    [AllowedFilter::trashed()]
                ))
                ->jsonPaginate()
        );
    }

    /**
     * Create Channel Member
     *
     * Create a channel member.
     *
     * @urlParam space int required The ID of the space. Example: 4
     * @urlParam channel int required The ID of the channel. Example: 1
     * @responseFile responses/ChannelMember/member.json
     */
    public function store(ChannelMemberStoreRequest $request, Space $space, Channel $channel)
    {
        $member = $channel->members()->where('user_id', $request->user_id)->first();
        if ($member) {
            $profile = $request->user()->profile;
            return response()->json([
                'errors' => [
                    'user_id' => [
                        __('channel.member_already_exist', [
                            'userName'=> $profile->first_name . ' ' . $profile->last_name,
                            'channelName' => $channel->name,
                        ])
                    ]
                ]
            ], 422);
        }

        $member = new ChannelMember;

        $member->fill($request->all());
        $member->user_id = $request->user_id;

        $channel->members()->save($member);

        return $this->jsonItem(
            $member->fresh()
        );
    }

    /**
     * Get Channel Member
     *
     * Get a channel member with the specified id.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam channel_id int required The ID of the channel. Example: 1
     * @urlParam id int required The ID of the channel member. Example: 3
     * @responseFile responses/ChannelMember/member.json
     */
    public function show(Space $space, Channel $channel, ChannelMember $member)
    {
      return $this->jsonItem(
        QueryBuilder::for(ChannelMember::class)
            ->allowedIncludes([
                'user',
                'user.profile',
                'channel'
            ])
            ->allowedFilters(array_merge(
                ChannelMember::getFields(),
                [AllowedFilter::trashed()]
            ))
            ->findOrFail($member->id)
      );
    }

    /**
     * Update Channel Member
     *
     * Update the specified channel member in storage.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam channel_id int required The ID of the channel. Example: 1
     * @urlParam id int required The ID of the channel member. Example: 3
     * @responseFile responses/ChannelMember/member.json
     */
    public function update(ChannelMemberUpdateRequest $request, Space $space, Channel $channel, ChannelMember $member)
    {
        $member->fill($request->all());
        $member->save();

        return $this->jsonItem(
            $member
        );
    }

    /**
     * Delete Channel Member
     *
     * Delete the specified channel member from storage (mark as deleted).
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam channel_id int required The ID of the channel. Example: 1
     * @urlParam id int required The ID of the channel member. Example: 3
     * @response 204
     */
    public function destroy(Space $space, Channel $channel, ChannelMember $member)
    {
        $member->delete();
        return response(null, 204);
    }
}
