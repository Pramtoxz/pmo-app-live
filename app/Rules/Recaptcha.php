<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = config('services.recaptcha.secret_key', env('RECAPTCHA_SECRET_KEY', ''));

        if (empty($secret)) {
            return;
        }

        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        $response  = $recaptcha->verify($value, request()->ip());

        if (!$response->isSuccess()) {
            $fail('Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
        }
    }
}
