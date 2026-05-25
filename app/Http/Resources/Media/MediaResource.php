<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\User\UserResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->medi_idmedi,

            'path' => $this->medi_dspath,

            'url' => asset(
                'storage/' . $this->medi_dspath
            ),

            'mime_type' => $this->medi_cdtype,

            'created_at' => $this->medi_dtcrea,

            /*
            |--------------------------------------------------------------------------
            | POLIMÓRFICO
            |--------------------------------------------------------------------------
            */

            'model_id' => $this->medi_idmdbl,

            'model_type' => $this->medi_nmmdbl,

            /*
            |--------------------------------------------------------------------------
            | RELACIONES
            |--------------------------------------------------------------------------
            */

            'uploaded_by' => UserResource::make(
                $this->whenLoaded('uploadedBy')
            ),

            /*
            |--------------------------------------------------------------------------
            | METADATA
            |--------------------------------------------------------------------------
            */

            'extension' => pathinfo(
                $this->medi_dspath,
                PATHINFO_EXTENSION
            ),

            'is_image' => str($this->medi_cdtype)
                ->startsWith('image/'),

            'is_video' => str($this->medi_cdtype)
                ->startsWith('video/'),
        ];
    }
}
