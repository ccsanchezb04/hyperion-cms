<?php

namespace App\Http\Resources\Content;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\User\UserResource;
use App\Http\Resources\Media\MediaResource;
use App\Http\Resources\Shared\CategoryResource;

class ContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->cont_idcont,

            'title' => $this->cont_nmtitl,

            'slug' => $this->cont_cdslug,

            'type' => $this->cont_cdtype,

            'status' => $this->cont_cdstat,

            'published_at' => $this->cont_dtpubl,

            'created_at' => $this->cont_dtcrea,

            'updated_at' => $this->cont_dtupda,

            /*
            |--------------------------------------------------------------------------
            | RELACIONES CONDICIONALES
            |--------------------------------------------------------------------------
            */

            'author' => UserResource::make(
                $this->whenLoaded('author')
            ),

            'categories' => CategoryResource::collection(
                $this->whenLoaded('categories')
            ),

            'media' => MediaResource::collection(
                $this->whenLoaded('media')
            ),

            'versions' => ContentVersionResource::collection(
                $this->whenLoaded('versions')
            ),

            /*
            |--------------------------------------------------------------------------
            | CAMPOS COMPUTADOS
            |--------------------------------------------------------------------------
            */

            'url' => url("/{$this->cont_cdslug}"),

            'is_published' => (
                $this->cont_cdstat === 'published'
            ),

            'excerpt' => str($this->latestVersion?->cove_dsbody)
                ->limit(200)
                ->toString(),
        ];
    }
}
