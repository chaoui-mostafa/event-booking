<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class BaseController extends Controller
{
    /**
     * Success response format
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = Response::HTTP_OK)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Error response format
     */
    protected function errorResponse(string $message = 'Error', int $code = Response::HTTP_BAD_REQUEST, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
