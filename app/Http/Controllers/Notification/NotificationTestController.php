<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\BroadcastMessageRequest;
use App\Http\Requests\PrivateMessageRequest;
use App\Services\MessageService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class NotificationTestController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected MessageService $messageService
    ) {}

    public function broadcast(BroadcastMessageRequest $request): ?JsonResponse
    {
        $result = $this->messageService->broadcastMessage($request->get('message'));

        return $this->successResponse(data: $result, message: 'Broadcast message sent successfully');
    }

    public function private(PrivateMessageRequest $request): ?JsonResponse
    {
        $result = $this->messageService->privateMessage($request->get('user_id'), $request->get('message'));

        return $this->successResponse(data: $result, message: 'Private message sent successfully');
    }
}
