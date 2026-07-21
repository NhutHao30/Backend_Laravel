<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CuocTroChuyen;
use App\Models\TinNhan;
use App\Models\KhachHang;
use App\Models\NhanVien;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Lấy danh sách cuộc trò chuyện
     */
    public function getConversations(Request $request)
    {
        $userLogin = Auth::guard('api')->user();
        if (!$userLogin) {
            return response()->json(['error' => 'Chưa đăng nhập'], 401);
        }

        if ($userLogin->MAROLE == 3) {
            // Khách hàng -> Lấy danh sách đoạn chat của họ
            $khachHang = KhachHang::where('USERNAME', $userLogin->USERNAME)->first();
            if (!$khachHang) {
                return response()->json(['error' => 'Không tìm thấy hồ sơ khách hàng'], 404);
            }

            $conversations = CuocTroChuyen::with(['tinnhans' => function($q) {
                $q->orderBy('created_at', 'desc')->take(1);
            }])
            ->where('MAKH', $khachHang->MAKH)
            ->orderBy('updated_at', 'desc')
            ->get();

            return response()->json($conversations);

        } else if (in_array($userLogin->MAROLE, [0, 1, 2, 4])) {
            // Nhân viên / Admin / CSKH -> Lấy đoạn chat
            // Tạm thời lấy tất cả, thực tế có thể lọc theo MACUAHANG
            $conversations = CuocTroChuyen::with(['tinnhans' => function($q) {
                $q->orderBy('created_at', 'desc')->take(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();

            return response()->json($conversations);
        }

        return response()->json([]);
    }

    /**
     * Khách hàng tạo hoặc lấy đoạn chat
     */
    public function startConversation(Request $request)
    {
        $userLogin = Auth::guard('api')->user();
        if (!$userLogin || $userLogin->MAROLE != 3) {
            return response()->json(['error' => 'Chỉ khách hàng mới có thể bắt đầu chat'], 403);
        }

        $khachHang = KhachHang::where('USERNAME', $userLogin->USERNAME)->first();
        if (!$khachHang) {
            return response()->json(['error' => 'Không tìm thấy khách hàng'], 404);
        }

        // Tìm đoạn chat đang hoạt động
        $conversation = CuocTroChuyen::where('MAKH', $khachHang->MAKH)
                                     ->where('TRANGTHAI', 'DANG_HOAT_DONG')
                                     ->first();

        if (!$conversation) {
            // Tạo đoạn chat mới
            $conversation = CuocTroChuyen::create([
                'MAKH' => $khachHang->MAKH,
                'TRANGTHAI' => 'DANG_HOAT_DONG'
            ]);
        }

        return response()->json($conversation);
    }

    /**
     * Lấy lịch sử tin nhắn
     */
    public function getMessages($conversationId)
    {
        $messages = TinNhan::where('MACUOCTROCHUYEN', $conversationId)
                           ->orderBy('created_at', 'asc')
                           ->get();
        return response()->json($messages);
    }

    /**
     * Gửi tin nhắn mới
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'NOIDUNG' => 'required|string'
        ]);

        $userLogin = Auth::guard('api')->user();
        if (!$userLogin) {
            return response()->json(['error' => 'Chưa đăng nhập'], 401);
        }

        $conversation = CuocTroChuyen::find($conversationId);
        if (!$conversation) {
            return response()->json(['error' => 'Đoạn chat không tồn tại'], 404);
        }

        $loaiNguoiGui = ($userLogin->MAROLE == 3) ? 'khachhang' : 'nhanvien';

        $tinNhan = TinNhan::create([
            'MACUOCTROCHUYEN' => $conversationId,
            'NGUOIGUI_ID' => $userLogin->USERNAME,
            'LOAINGUOIGUI' => $loaiNguoiGui,
            'NOIDUNG' => $request->NOIDUNG,
            'DADOC' => 0
        ]);

        // Cập nhật thời gian update của đoạn chat
        $conversation->touch();

        // Broadcast sự kiện qua Pusher
        broadcast(new MessageSent($tinNhan, $conversationId))->toOthers();

        return response()->json($tinNhan);
    }

    /**
     * Thu hồi tin nhắn
     */
    public function recallMessage($conversationId, $messageId)
    {
        $userLogin = Auth::guard('api')->user();
        if (!$userLogin) return response()->json(['error' => 'Chưa đăng nhập'], 401);

        $message = TinNhan::where('MACUOCTROCHUYEN', $conversationId)->where('MATINNHAN', $messageId)->first();
        if (!$message) return response()->json(['error' => 'Tin nhắn không tồn tại'], 404);

        if ($message->NGUOIGUI_ID !== $userLogin->USERNAME) {
            return response()->json(['error' => 'Bạn không thể thu hồi tin nhắn của người khác'], 403);
        }

        $message->NOIDUNG = '🚫 Tin nhắn đã bị thu hồi';
        $message->save();

        broadcast(new MessageSent($message, $conversationId))->toOthers();

        return response()->json($message);
    }
}
