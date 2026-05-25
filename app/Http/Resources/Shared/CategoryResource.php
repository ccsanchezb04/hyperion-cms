<?php

namespace App\Http\Resources\Shared;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->cate_idcate,

            'name' => $this->cate_nmname,

            'slug' => $this->cate_cdslug,

            'parent_id' => $this->cate_idpare,
        ];
    }
}
