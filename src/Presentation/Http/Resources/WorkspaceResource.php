<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Stockr\Domain\Auth\Entities\Workspace;

/**
 * @property-read Workspace $resource
 *
 * @mixin Workspace
 */
final class WorkspaceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $workspace = $this->resource;

        return [
            'id' => $workspace->id,
            'name' => $workspace->name(),
            'slug' => (string) $workspace->slug(),
            'owner_id' => $workspace->ownerId,
        ];
    }
}
