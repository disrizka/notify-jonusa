<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index() {
    // Ambil chat + info pengirim, urutkan dari yang lama ke baru
    $chats = \App\Models\Chat::with('user:id,name')->orderBy('created_at', 'asc')->get();
    return response()->json($chats);
}

public function store(Request $request) {
    $request->validate(['message' => 'required']);

    $chat = \App\Models\Chat::create([
        'user_id' => $request->user()->id,
        'message' => $request->message
    ]);

    return response()->json($chat->load('user:id,name'), 201);
}
}
