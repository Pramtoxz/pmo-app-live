<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestBookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->IDGuestBook,
            'tanggal' => $this->Tanggal?->format('Y-m-d'),
            'nama_customer' => $this->NamaCustomer,
            'no_hp' => $this->NoHp,
            'kode_type' => $this->KodeType,
            'kode_warna' => $this->KodeWarna,
            'deskripsi_warna' => $this->DeskripsiWarnaMotor,
            'rencana_pembayaran' => $this->RencanaPembayaran,
            'tipe_customer' => $this->TipeCustomer,
            'alamat_prospect' => $this->AlamatProspect,
            'alamat_kantor' => $this->AlamatKantorProspect,
            'source' => $this->Source,
            'keterangan' => $this->Keterangan,
            'status' => $this->Status_guestbook,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
