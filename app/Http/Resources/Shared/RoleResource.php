<?php

namespace App\Http\Resources\Shared;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->role_idrole,

            'name' => $this->role_nmname,

            'slug' => $this->role_cdslug,
        ];
    }
}
