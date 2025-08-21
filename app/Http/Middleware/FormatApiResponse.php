<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FormatApiResponse
{
    public function handle($request, Closure $next)
    {
        try {
            $response = $next($request);

            // Kalau bukan error, lanjutkan normal
            return $response;
        } catch (ValidationException $e) {
            // Tangkap validation error
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422));
        } catch (HttpExceptionInterface $e) {
            // Tangkap HTTP Exception biasa
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'HTTP error',
                'errors' => [],
            ], $e->getStatusCode()));
        } catch (\Exception $e) {
            // Tangkap exception lain
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Server error',
                'errors' => [],
            ], 500));
        }
    }
}
