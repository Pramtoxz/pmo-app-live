<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Convert Indonesian field names to English for frontend consistency.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_code' => $this->kd_toko,
            'title' => $this->judul,
            'message' => $this->pesan,
            'type' => $this->tipe,
            'is_read' => (bool) $this->sudah_dibaca,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
