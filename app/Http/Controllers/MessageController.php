<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message'         => 'required|string|max:500',
            'sender'          => 'required|string|max:255',
        ]);

        $value = Conversation::where('user_id', Auth::user()->id, 'conversation_id', $request->conversation_id)->where('conversation_id', $request->conversation_id);
        
        if(!$value){
            return response()->json([
                'response_code' => 404,
                'status'        => 'error',
                'message'       => 'Conversation not found',
            ]);
        }

        //ai response here
    }
}
