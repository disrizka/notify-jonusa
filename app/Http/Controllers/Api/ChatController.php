<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller
{
        public function index() {
        $chats = \App\Models\Chat::with(['user:id,name', 'parent.user:id,name'])
                ->orderBy('created_at', 'asc')
                ->get();
                
        return response()->json($chats);
}

        public function store(Request $request) {
            $chat = \App\Models\Chat::create([
                'user_id'   => $request->user()->id,
                'message'   => $request->message,
                'parent_id' => $request->parent_id, // Tambahkan ini
            ]);
            return response()->json($chat->load(['user:id,name', 'parent']), 201);
    }
    
}
