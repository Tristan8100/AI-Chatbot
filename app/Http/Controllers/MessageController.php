<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\Facades\Tool;
class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        //this will be created when new or existing conversation is intiated
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message'         => 'required|string|max:500',
        ]);

        $value = Conversation::with('messages')->where('user_id', Auth::user()->id)->where('id', $request->conversation_id)->first();
        
        if(!$value){
            return response()->json([
                'response_code' => 404,
                'status'        => 'error',
                'message'       => 'Conversation not found',
            ]);
        }

        $messagesSorted = $value->messages()
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get()
        ->reverse()
        ->values();

        $structuredMessages = $messagesSorted->map(function ($msg) {
            return $msg->sender === 'user'
                ? new UserMessage($msg->message)
                : new AssistantMessage($msg->message);
        })->toArray();

        //ai response here
        try {
            $responseAI = Prism::text()
                ->using(Provider::Gemini, 'gemini-1.5-flash')
                ->withMaxSteps(2)
                ->withSystemPrompt('you are his bro bestfriend')
                ->withMessages([
                    ...$structuredMessages,
                    new UserMessage($request->message),
                ])
                ->asText();

            Message::create([
                'conversation_id' => $request->conversation_id,
                'message'         => $request->message,
                'sender'          => 'user',
            ]);

            Message::create([
                'conversation_id' => $request->conversation_id,
                'message'         => $responseAI->text,
                'sender'          => 'ai',
            ]);

            return response()->json([
                'status' => 'success',
                'response' => $responseAI->text,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
