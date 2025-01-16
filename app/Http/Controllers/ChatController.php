<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
class ChatController extends Controller
{
    public function getSupportChats()
    {
        $chats = Chat::with('messages')
            ->get()
            ->map(function ($chat) {
                $chat->user = User::find($chat->user_id);
                $chat->lastMessage = $chat->messages()->orderBy('created_at', 'desc')->first();
                return $chat;
            })
            ->sortBy(function($chat) {
                return $chat->lastMessage 
                    ? -$chat->lastMessage->created_at->timestamp
                    : PHP_INT_MIN;
            })
            ->values();

        return response()->json(['success' => true, 'chats' => $chats]);
    }

    public function getUserMessages()
    {
        $user = auth('api')->user();
        $chat = Chat::where('user_id', $user->id)->first();
        if (!$chat) {
            return response()->json(['success' => false, 'message' => 'Chat not found']);
        }
        $messages = Message::where('chat_id', $chat->id)->get();
        return response()->json(['success' => true, 'messages' => $messages]);
    }
    public function getChatMessages($chatId)
    {
        $chat = Chat::find($chatId);
        if(!$chat){
            return response()->json(['success' => false, 'message' => 'Chat not found']);
        }
        $messages = Message::where('chat_id', $chatId)->get();
        $messages = $messages->map(function ($message) {
            $message->user = User::find($message->user_id);
            return $message;
        });
        return response()->json(['success' => true, 'messages' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        $user = auth('api')->user();
        if($request->chat_id == null){
            $chat = Chat::where('user_id', $user->id)->first();
            if(!$chat){
                $chat = Chat::create([
                    'user_id' => $user->id,
                ]);
            }
        }else{
            $chat = Chat::find($request->chat_id);
        }
        if($request->message == ''){
            return response()->json(['success' => false, 'message' => 'Message is required']);
        }
       
        $message = Message::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);
        $message->user = User::find($message->user_id);
        return response()->json(['success' => true, 'message' => $message]);
    }
}
