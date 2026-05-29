<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Helpers\ApiResponse;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;


class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with('shop')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Cek apakah toko masih aktif
        $shop = $user->shop;
        if ($shop && !$shop->toko_active) {
            throw ValidationException::withMessages([
                'email' => ['Toko Anda sudah tidak aktif. Silakan hubungi admin.'],
            ]);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'dealerCode' => $shop ? $shop->kode : null,
                'dealerName' => $shop ? $shop->nama : null,
                'phone' => $shop ? $shop->no_hp : null,
                'address' => $shop ? $shop->alamat : null,
                'city' => $shop ? $shop->kota : null,
                'province' => $shop ? $shop->provinsi : null,
            ]
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('shop');
        $shop = $user->shop;

        $salesName = null;
        $salesPhone = null;

        if ($shop) {
            if ($shop->fk_sales) {
                $sales = DB::connection('pgsql')
                    ->table('pmov2.sales_supervisor')
                    ->where('kode_npk', $shop->fk_sales)
                    ->first();
                if ($sales) {
                    $salesName = $sales->nama;
                    $salesPhone = $sales->no_hp;
                }
            }
        }

        return ApiResponse::success([
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'dealerCode' => $shop ? $shop->kode : null,
            'dealerName' => $shop ? $shop->nama : null,
            'salesName' => $salesName,
            'salesPhone' => $salesPhone,
            'salesWhatsapp' => $salesPhone ? 'https://wa.me/' . preg_replace('/\D/', '', $salesPhone) : null,
            'phone' => $shop ? $shop->no_hp : null,
            'address' => $shop ? $shop->alamat : null,
            'npwp' => $shop ? $shop->npwp : null,
            'city' => $shop ? $shop->kota : null,
            'province' => $shop ? $shop->provinsi : null,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $shop = $user->shop;

        if (!$shop) {
            return ApiResponse::error('Toko tidak ditemukan', 404);
        }

        $request->validate([
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'npwp' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        try {
            if ($request->filled('email')) {
                $user->email = $request->email;
            }

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            $shopUpdated = false;
            if ($request->filled('phone')) {
                $shop->no_telp = $request->phone;
                $shopUpdated = true;
            }

            if ($request->filled('address')) {
                $shop->alamat = $request->address;
                $shopUpdated = true;
            }

            if ($request->filled('npwp')) {
                $shop->npwp = $request->npwp;
                $shopUpdated = true;
            }

            if ($shopUpdated) {
                $shop->save();
            }

            $user->refresh();
            $shop->refresh();

            $salesName = null;
            $salesPhone = null;

            if ($shop->fk_sales) {
                $sales = DB::connection('pgsql')
                    ->table('pmov2.sales_supervisor')
                    ->where('kode_npk', $shop->fk_sales)
                    ->first();
                if ($sales) {
                    $salesName = $sales->nama;
                    $salesPhone = $sales->no_hp;
                }
            }

            return ApiResponse::success([
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'dealerCode' => $shop->kode,
                'dealerName' => $shop->nama,
                'salesName' => $salesName,
                'salesPhone' => $salesPhone,
                'salesWhatsapp' => $salesPhone ? 'https://wa.me/' . preg_replace('/\D/', '', $salesPhone) : null,
                'phone' => $shop->no_hp,
                'address' => $shop->alamat,
                'npwp' => $shop->npwp,
                'city' => $shop->kota,
                'province' => $shop->provinsi,
            ], 'Profil berhasil diupdate');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal update profil: ' . $e->getMessage(), 500);
        }
    }

  public function logout(Request $request)
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        
        if ($token) {
            $token->delete();
        }

        return ApiResponse::success(null, 'Logged out successfully');
    }

    /**
     * Request OTP via WhatsApp
     */
    public function requestOTP(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        try {
            // Bersihkan nomor telepon
            $cleanPhone = preg_replace('/\D+/', '', $request->phone);
            
            // Cari toko berdasarkan nomor HP (kolom di DMS: no_telp)
            $shop = Shop::where('toko_active', true)
                ->where(function($query) use ($cleanPhone, $request) {
                    $query->where('no_telp', 'LIKE', '%' . $cleanPhone . '%')
                          ->orWhere('no_telp', 'LIKE', '%' . $request->phone . '%');
                })
                ->first();

            if (!$shop) {
                return ApiResponse::error('Nomor telepon tidak terdaftar', 404);
            }

            // Generate dan kirim OTP
            $otpCode = $this->otpService->generateOTP($cleanPhone, 'login');
            $this->otpService->sendOTP($cleanPhone, $otpCode);

            return ApiResponse::success([
                'phone' => $cleanPhone,
                'message' => 'Kode OTP telah dikirim ke WhatsApp Anda'
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengirim OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify OTP and login
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp_code' => 'required|string|size:6',
        ]);

        try {
            $cleanPhone = preg_replace('/\D+/', '', $request->phone);

            // Verifikasi OTP
            if (!$this->otpService->verifyOTP($cleanPhone, $request->otp_code)) {
                return ApiResponse::error('Kode OTP tidak valid atau sudah kadaluarsa', 400);
            }

            // Cari toko berdasarkan nomor HP (kolom di DMS: no_telp)
            $shop = Shop::where('toko_active', true)
                ->where('no_telp', 'LIKE', '%' . $cleanPhone . '%')
                ->first();

            if (!$shop) {
                return ApiResponse::error('Toko tidak ditemukan', 404);
            }

            // Cari atau buat user untuk toko ini
            $user = User::where('fk_toko', $shop->kd_toko)->first();

            if (!$user) {
                // Buat user baru jika belum ada
                $user = User::create([
                    'name' => $shop->toko,
                    'email' => $shop->kd_toko . '@menara-agung.com', // Email dummy
                    'password' => Hash::make(uniqid()), // Password random
                    'fk_toko' => $shop->kd_toko,
                    'role' => 'dealer',
                ]);
            }

            // Generate token
            $token = $user->createToken('mobile-app')->plainTextToken;

            return ApiResponse::success([
                'token' => $token,
                'user' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'dealerCode' => $shop->kd_toko,
                    'dealerName' => $shop->toko,
                    'phone' => $shop->no_telp,
                    'address' => $shop->alamat,
                    'city' => $shop->kota,
                    'province' => $shop->provinsi,
                ]
            ], 'Login berhasil');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal verifikasi OTP: ' . $e->getMessage(), 500);
        }
    }
}
