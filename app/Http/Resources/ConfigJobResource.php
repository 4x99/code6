<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConfigJobResource extends JsonResource
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
            'keyword' => $this->keyword,
            'scan_page' => $this->scan_page,
            'scan_interval_min' => $this->scan_interval_min,
            'description' => $this->description,
            'last_scan_at' => $this->last_scan_at ?: '',
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : '',
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
