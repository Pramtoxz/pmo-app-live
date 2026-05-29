<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admin can send notifications
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required_without:kd_toko|exists:users,id',
            'kd_toko' => 'required_without:user_id|string',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'type' => 'nullable|string|in:general,order,collection,campaign,system',
            'data' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required_without' => 'User ID atau Kode Toko harus diisi',
            'user_id.exists' => 'User tidak ditemukan',
            'kd_toko.required_without' => 'Kode Toko atau User ID harus diisi',
            'title.required' => 'Judul notifikasi harus diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'body.required' => 'Isi notifikasi harus diisi',
            'body.max' => 'Isi notifikasi maksimal 1000 karakter',
            'type.in' => 'Tipe notifikasi tidak valid',
        ];
    }
}
