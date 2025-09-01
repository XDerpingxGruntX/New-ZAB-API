<?php

namespace App\Services;

use App\Data\ControllerData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class VATUSA
{
    protected Factory|PendingRequest $client;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->client = Http::baseUrl($baseUrl)->withQueryParameters(['apikey' => $apiKey]);
    }

    /**
     * Get a list of all facilities.
     *
     * @throws ConnectionException
     */
    public function listFacilities()
    {
        return $this->client->get('facility')->json();
    }

    /**
     * Get a facility by its IATA code.
     *
     * @throws ConnectionException
     */
    public function getFacility(string $id)
    {
        return $this->client->get("facility/{$id}")->json();
    }

    /**
     * Get the roster for a facility.
     *
     * @param  string  $id  The facility ID
     * @param  'home'|'visit'|'both'  $type  The type of roster to retrieve
     * @return Collection<int, ControllerData>
     *
     * @throws ConnectionException
     */
    public function getFacilityRoster(string $id, string $type = 'home'): Collection
    {
        $json = $this->client->get("facility/{$id}/roster/{$type}")->json();

        return collect($json['data'] ?? [])
            ->map(fn (array $member): ControllerData => ControllerData::from($member));
    }

    /**
     * Add a visitor to a facility.
     *
     * @param  string  $facilityId  The facility ID
     * @param  string  $cid  The controller's CID
     *
     * @throws ConnectionException
     */
    public function addVisitor(string $facilityId, string $cid): array
    {
        return $this->client->post("facility/{$facilityId}/roster/manageVisitor/{$cid}")->json();
    }

    /**
     * Remove a visitor from a facility.
     *
     * @param  string  $facilityId  The facility ID
     * @param  string  $cid  The controller's CID
     * @param  string  $reason  The reason for removal
     *
     * @throws ConnectionException
     */
    public function removeVisitor(string $facilityId, string $cid, string $reason): array
    {
        return $this->client->delete("facility/{$facilityId}/roster/manageVisitor/{$cid}", [
            'reason' => $reason,
        ])->json();
    }

    /**
     * Remove a controller from a facility roster.
     *
     * @param  string  $facilityId  The facility ID
     * @param  string  $cid  The controller's CID
     * @param  string  $reason  The reason for removal
     *
     * @throws ConnectionException
     */
    public function removeController(string $facilityId, string $cid, string $reason): array
    {
        return $this->client->delete("facility/{$facilityId}/roster/{$cid}", [
            'reason' => $reason,
        ])->json();
    }
}
