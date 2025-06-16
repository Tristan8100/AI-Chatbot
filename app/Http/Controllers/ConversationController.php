<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function newconversation(){
        $value = Conversation::create([
            'user_id' => Auth::user()->id,
        ]);

        return response()->json([
            'response_code' => 200,
            'status'        => 'success',
            'message'       => 'Conversation created successfully',
            'content'       => $value,
        ]);
    }

    public function getConversations(){
        $value = Conversation::where('user_id', Auth::user()->id);

        return response()->json([
            'response_code' => 200,
            'status'        => 'success',
            'message'       => 'Conversations retrieved successfully',
            'content'       => $value->get(),
        ]);
    }

    public function getMessages($id)
    {
        $value = Conversation::where('id', $id)->where('user_id', Auth::user()->id)->first();
        return response()->json([
            'response_code' => 200,
            'status'        => 'success',
            'message'       => 'Messages retrieved successfully',
            'content'       => $value->messages()->get(),
        ]);
    }
}
