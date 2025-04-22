<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class IoTWaterlevelResources extends JsonResource
{
    public $status;
    public $message;
    public $resource;
    public $statusCode;

    /**
     * __construct
     *
     * @param  mixed $status
     * @param  mixed $message
     * @param  mixed $resource
     * @param  int|null $statusCode
     * @return void
     */
    public function __construct($status, $message, $resource, $statusCode = Response::HTTP_OK)
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => $this->status,
            'message' => $this->message,
            'data' => $this->resource,
        ];
    }

    /**
     * Customize the outgoing response.
     *
     * @param  Request $request
     * @param  \Illuminate\Http\JsonResponse $response
     * @return void
     */
    public function withResponse(Request $request, $response): void
    {
        $response->setStatusCode($this->statusCode);
    }
}
