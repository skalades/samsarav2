<?php

namespace App\Http\Middleware;

use App\Support\Money;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeCurrencyInputs
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        if (!empty($input)) {
            $cleaned = $this->cleanArray($input);
            $request->merge($cleaned);
        }

        return $next($request);
    }

    /**
     * Recursively clean array inputs.
     */
    protected function cleanArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->cleanArray($value);
            } else {
                $data[$key] = Money::sanitize($value);
            }
        }

        return $data;
    }
}
