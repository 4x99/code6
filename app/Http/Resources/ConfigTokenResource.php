<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConfigTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'token' => $this->token,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : '',
            'status' => $this->status,
            'api_limit' => $this->api_limit,
            'api_remaining' => $this->api_remaining,
            'api_reset_at' => $this->api_reset_at,
            'description' => $this->description,
        ];
    }
}
