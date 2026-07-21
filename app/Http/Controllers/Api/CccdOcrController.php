<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Storage;

class CccdOcrController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // max 10MB
        ]);

        try {
            $file = $request->file('image');
            $path = $file->store('temp_ocr', 'local');
            $fullPath = Storage::disk('local')->path($path);

            $ocr = new TesseractOCR($fullPath);
            
            // Tìm Tesseract trên Windows
            $tesseractPath = null;
            
            // 1. Thử dùng lệnh where của Windows
            $whereOutput = [];
            exec('where tesseract 2>NUL', $whereOutput);
            if (!empty($whereOutput) && file_exists($whereOutput[0])) {
                $tesseractPath = $whereOutput[0];
            } else {
                // 2. Tìm trong các thư mục cài đặt mặc định
                $commonPaths = [
                    'D:\\Program Files\\OCR\\tesseract.exe', // Đường dẫn người dùng cung cấp
                    'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
                    'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
                    getenv('LOCALAPPDATA') . '\\Tesseract-OCR\\tesseract.exe',
                    getenv('LOCALAPPDATA') . '\\Programs\\Tesseract-OCR\\tesseract.exe',
                    'D:\\Program Files\\Tesseract-OCR\\tesseract.exe'
                ];
                
                foreach ($commonPaths as $p) {
                    if (file_exists($p)) {
                        $tesseractPath = $p;
                        break;
                    }
                }
            }

            if ($tesseractPath) {
                $ocr->executable($tesseractPath);
            }

            $ocr->lang('vie');
            $text = $ocr->run();

            // Xóa file tạm
            Storage::disk('local')->delete($path);

            return response()->json([
                'success' => true,
                'raw_text' => $text,
                'parsed_data' => $this->parseCccdText($text)
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Tesseract OCR Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi OCR: ' . $e->getMessage()
            ], 500);
        }
    }

    private function parseCccdText($text)
    {
        $data = [
            'cccd' => null,
            'hoTen' => null,
            'ngaySinh' => null,
            'gioiTinh' => null,
            'diaChi' => null,
        ];

        // 1. Lấy số CCCD (12 số liên tiếp)
        if (preg_match('/(?:\b|Số|No\.)\s*[:\-\s]?\s*(\d{12})\b/i', $text, $matches) || preg_match('/\b(\d{12})\b/', $text, $matches)) {
            $data['cccd'] = $matches[1];
        }

        // 2. Lấy Ngày sinh (Định dạng dd/mm/yyyy)
        if (preg_match('/\b(\d{2})[\/\-\.](\d{2})[\/\-\.](\d{4})\b/', $text, $matches)) {
            $data['ngaySinh'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1]; // Format YYYY-MM-DD for HTML input
        }

        // 3. Lấy Giới tính (Nam / Nữ)
        if (preg_match('/\b(Nam|Nữ)\b/iu', $text, $matches)) {
            // Chuẩn hóa chữ hoa chữ thường
            $data['gioiTinh'] = mb_strtolower($matches[1]) == 'nam' ? 'Nam' : 'Nữ';
        }

        // 4. Lấy Họ tên (Thường là dòng CHỮ IN HOA sau dòng CCCD hoặc ngay trước Ngày sinh)
        $lines = explode("\n", $text);
        $expectingName = false;
        $expectingAddress = false;

        foreach ($lines as $index => $line) {
            $line = trim($line);
            
            // Xóa các nhãn tiếng Anh thừa thường xuất hiện cùng dòng
            $line = preg_replace('/\/?\s*Full name/iu', '', $line);
            $line = preg_replace('/\/?\s*Place of residence/iu', '', $line);
            $line = preg_replace('/\/?\s*Place of origin/iu', '', $line);
            $line = preg_replace('/\/?\s*Date of birth/iu', '', $line);
            $line = preg_replace('/\/?\s*Sex/iu', '', $line);
            $line = preg_replace('/\/?\s*Nationality/iu', '', $line);
            $line = trim($line, "/: \t\n\r\0\x0B");

            if (empty($line)) continue;

            // Xử lý Họ Tên bị rớt dòng
            if ($expectingName && mb_strtoupper($line) === $line && strlen($line) > 5) {
                if (!preg_match('/\d/', $line)) { // Tên không chứa số
                    $data['hoTen'] = $line;
                    $expectingName = false;
                    continue;
                }
            }

            // Tìm Họ tên
            if (preg_match('/(?:Họ và tên|Họ tên)\s*[:\-]?\s*(.*)/iu', $line, $matches)) {
                $name = trim($matches[1], "/: ");
                if (strlen($name) > 3) {
                    $data['hoTen'] = $name;
                } else {
                    $expectingName = true;
                }
                $expectingAddress = false;
                continue;
            } 
            // Dò dòng toàn chữ IN HOA (đề phòng OCR không đọc được nhãn "Họ và tên")
            else if (!$data['hoTen'] && mb_strtoupper($line) === $line && strlen($line) > 5 && !preg_match('/\d/', $line)) {
                $excludeWords = ['CỘNG HÒA', 'ĐỘC LẬP', 'CĂN CƯỚC', 'SOCIALIST', 'INDEPENDENCE', 'CITIZEN', 'IDENTITY', 'VIỆT NAM', 'NAM', 'NỮ'];
                $isSystemWord = false;
                foreach ($excludeWords as $word) {
                    if (str_contains(mb_strtoupper($line), $word)) {
                        $isSystemWord = true;
                        break;
                    }
                }
                if (!$isSystemWord) {
                    $data['hoTen'] = $line;
                }
            }

            // Xử lý địa chỉ nhiều dòng
            if ($expectingAddress) {
                // Ngưng nếu gặp dòng ngày hết hạn hoặc dấu hiệu kết thúc
                if (preg_match('/(?:Có giá trị đến|Date of expiry|Giám đốc|Cục trưởng)/iu', $line)) {
                    $expectingAddress = false;
                    continue;
                }
                $data['diaChi'] .= ($data['diaChi'] ? ', ' : '') . $line;
                continue;
            }

            // Tìm Quê quán / Nơi thường trú
            if (preg_match('/(?:Nơi thường trú|Quê quán|Nơi ở)\s*[:\-]?\s*(.*)/iu', $line, $matches)) {
                $addr = trim($matches[1], "/: ");
                if (strlen($addr) > 3) {
                    $data['diaChi'] = $addr;
                }
                $expectingAddress = true; // Tiếp tục nối địa chỉ ở các dòng sau
                continue;
            }
        }

        return $data;
    }
}
