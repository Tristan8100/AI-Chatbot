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
            'message'         => 'required|string|max:1000',
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

        $weatherTool = Tool::as('weather') //mock tools from documentation
        ->for('Get current weather conditions')
        ->withStringParameter('city', 'The city to get weather for')
        ->using(function (string $city): string {
            return "The weather in {$city} is sunny and 72°F.";
        });

        $userInfoTool = Tool::as('userInfo')
        ->for('Get authenticated user information')
        ->withStringParameter('user', 'The user to get information for')
        ->using(function (string $user): string {
            $user = Auth::user();
            if (!$user) {
                return 'No user is logged in.';
            }
            return "The user's name is {$user->name} and email is {$user->email}.";
        });

        try {
            $responseAI = Prism::text()
                ->using(Provider::Groq, 'qwen-qwq-32b')
                ->withSystemPrompt('You are his friend, dont be mean and you should bre friendly and speak humanly, dont speak like an AI, use $userInfoTool to get user information, always address the user by their name.')
                ->withMessages([
                    ...$structuredMessages,
                    new UserMessage($request->message),
                ])
                ->withTools([$weatherTool, $userInfoTool])
                ->withMaxSteps(5)
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
                'tools' => $userInfoTool,
                'structuredMessages' => $structuredMessages
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
