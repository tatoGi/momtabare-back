<?php

namespace App\Http\Controllers\Website;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatMessages;
use App\Models\WebUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(WebUser $user)
    {
        $messages = ChatMessages::with(['sender', 'receiver'])
            ->whereIn('sender_id', [Auth::id(), $user->id])
            ->whereIn('receiver_id', [Auth::id(), $user->id])
            ->get();

        return response()->json($messages);
    }

    public function store(WebUser $user, Request $request)
    {
        $message = ChatMessages::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $user->id,
            'text' => $request->message,
        ]);
        broadcast(new MessageSent($user, $message))->toOthers();

        return response()->json($message);
    }
}
