<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Shared\RoleResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->user_iduser,

            'name' => $this->user_nmname,

            'email' => $this->when(
                $request->user()?->can('viewEmail', $this->resource),
                $this->user_dsemai
            ),

            'status' => $this->user_cdstat,

            'created_at' => $this->user_dtcrea,

            /*
            |--------------------------------------------------------------------------
            | RELACIONES CONDICIONALES
            |--------------------------------------------------------------------------
            */

            'roles' => RoleResource::collection(
                $this->whenLoaded('roles')
            ),

            /*
            |--------------------------------------------------------------------------
            | CAMPOS COMPUTADOS
            |--------------------------------------------------------------------------
            */

            'is_active' => (
                $this->user_cdstat === 'active'
            ),

            'avatar_url' => $this->avatar_url
                ?? null,
        ];
    }
}
