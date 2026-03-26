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
        $request->validate(['type' => 'required']);

        $filePath = null;
        if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('uploads', 'public');
        }

        $chat = \App\Models\Chat::create([
                'user_id' => $request->user()->id,
                'message'   => $request->message ??'', 
                'file_path' => $filePath,
                'type' => $request->type,
                'parent_id' => $request->parent_id
        ]);

        return response()->json($chat->load(['user:id,name', 'parent.user:id,name']), 201);
        }
    
}
