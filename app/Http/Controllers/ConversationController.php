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
         $userId = Auth::id();

        $conversations = Conversation::where('user_id', $userId)
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1); // only get the latest message per conversation
            }])->latest()->get();

        return response()->json([
            'response_code' => 200,
            'status'        => 'success',
            'message'       => 'Conversations retrieved successfully',
            'content'       => $conversations,
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

    public function updateConversation(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255'
        ]);

        $conversation = Conversation::where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        $conversation->update([
            'title' => $request->title
        ]);

        return response()->json([
            'response_code' => 200,
            'status'        => 'success',
            'message'       => 'Conversation updated successfully',
            'content'       => $conversation,
        ]);
    }

    public function deleteConversation($id)
    {
        $conversation = Conversation::where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        // Delete associated messages first if needed
        $conversation->messages()->delete();
        
        $conversation->delete();

        return response()->json([
            'response_code' => 200,
            'status'        => 'success',
            'message'       => 'Conversation deleted successfully',
            'content'       => null,
        ]);
    }
}
