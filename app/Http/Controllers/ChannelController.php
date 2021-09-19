<?php

namespace App\Http\Controllers;

use App\Models\{
    Channel,
    ChannelMember,
    Space
};
use App\Http\Requests\Channel\{
    ChannelStoreRequest,
    ChannelUpdateRequest
};
use Spatie\QueryBuilder\{
  QueryBuilder,
  AllowedFilter
};
use Illuminate\Http\Request;

/**
 * @group Channel Management
 *
 * API endpoints for managing channels
 */
class ChannelController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Channel::class, 'channel');
    }

    /**
     * Get Channels
     *
     * Get all channels that belongs to a space
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @responseFile responses/Channel/channels.json
     */
    public function index(Space $space)
    {
        return $this->jsonCollection(
            QueryBuilder::for($space->channels()->getQuery())
                ->allowedIncludes([
                    'user',
                    'owner',
                    'space',
                    'members'
                ])
                ->allowedFilters(array_merge(
                    Channel::getFields(),
                    [AllowedFilter::trashed()]
                ))
                ->jsonPaginate()
        );
    }

    /**
     * Create Channel
     *
     * Create a channel that belongs to a space
     *
     * @urlParam space int required The ID of the space. Example: 4
     * @responseFile responses/Channel/channel.json
     */
    public function store(ChannelStoreRequest $request, Space $space)
    {
        $channel = new Channel;

        $channel->fill($request->all());

        $space->channels()->save($channel);

        $owner = new ChannelMember;
        $owner->role = 'owner';
        $owner->user_id = $request->user()->id;

        $channel->members()->save($owner);

        return $this->jsonItem(
            $channel->fresh()
        );
    }

    /**
     * Get Channel
     *
     * Get a channel with the specific id
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam id int required The ID of the channel. Example: 1
     * @responseFile responses/Channel/channel.json
     */
    public function show(Space $space, Channel $channel)
    {
      return $this->jsonItem(
        QueryBuilder::for(Channel::class)
            ->allowedIncludes([
                'user',
                'owner',
                'space',
                'members'
            ])
            ->allowedFilters(array_merge(
                Channel::getFields(),
                [AllowedFilter::trashed()]
            ))
            ->findOrFail($channel->id)
      );
    }

    /**
     * Update Channel
     *
     * Update the specified channel fields.
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam id int required The ID of the channel. Example: 1
     * @responseFile responses/Channel/channel.json
     */
    public function update(ChannelUpdateRequest $request, Space $space, Channel $channel)
    {
        $channel->fill($request->all());
        $channel->save();

        return $this->jsonItem($channel);
    }

    /**
     * Delete Channel
     *
     * Delete a specified channel (mark as deleted).
     *
     * @urlParam space_id int required The ID of the space. Example: 4
     * @urlParam id int required The ID of the channel. Example: 1
     * @response 204
     */
    public function destroy(Space $space, Channel $channel)
    {
        $channel->delete();
        return response(null, 204);
    }
}
