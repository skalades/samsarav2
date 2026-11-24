<?php

namespace App\Http\Middleware;

use App\Support\Money;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeCurrencyInputs
{
    /**
     * Field yang TIDAK boleh diubah walau bentuknya mirip nominal ("12.345.678"):
     * kata sandi, token, NIK, nomor HP/rekening, dan kode.
     */
    protected const EXCEPT_PATTERN = '/(password|token|secret|nik|phone|whatsapp|hotline|account|nmid|code|email)/i';

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        if (! empty($input)) {
            $request->merge($this->cleanArray($input));
        }

        return $next($request);
    }

    /**
     * Recursively clean array inputs.
     */
    protected function cleanArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && preg_match(self::EXCEPT_PATTERN, $key)) {
                continue;
            }

            $data[$key] = is_array($value) ? $this->cleanArray($value) : Money::sanitize($value);
        }

        return $data;
    }
}
