<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Kênh bảo mật cho đoạn chat (chỉ nhân viên hoặc người dùng sở hữu đoạn chat mới được nghe)
Broadcast::channel('chat.{conversationId}', function ($userLogin, $conversationId) {
    if (!$userLogin) return false;

    // Nhân viên / Admin luôn được phép nghe
    if (in_array($userLogin->MAROLE, [0, 1, 2])) {
        return true;
    }

    // Khách hàng thì phải là chủ của cuộc trò chuyện
    $conversation = \App\Models\CuocTroChuyen::find($conversationId);
    if ($conversation && $userLogin->MAROLE == 3) {
        $khachHang = \App\Models\KhachHang::where('USERNAME', $userLogin->USERNAME)->first();
        return $khachHang && $conversation->MAKH == $khachHang->MAKH;
    }

    return false;
}, ['guards' => ['api']]);
