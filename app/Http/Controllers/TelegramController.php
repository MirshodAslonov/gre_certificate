<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    public function start(Request $request)
{
    return $this->sendMessage(7112096011, "⚠️ Fayl juda katta (50MB+). Boshqa sifatni tanlang.");
    $data = $request->all();
    $token = config('services.telegram.you_tube_bot_token');

    // Tugmani bosganda callback qaytadi
    if (isset($data['callback_query'])) {

    $chatId = $data['callback_query']['message']['chat']['id'];

    // callback_data: "720|https://youtube.com/..."
    $parts = explode('|', $data['callback_query']['data']);
    $format = $parts[0];
    $url    = $parts[1];

    return $this->downloadAndSend($chatId, $url, $format);
}
    if (!isset($data['message']['text'])) {
        return response()->json(['error' => 'Text not found'], 200);
    }

    $chatId = $data['message']['chat']['id'];
    $text   = $data['message']['text'];

    // YouTube link bo'lsa
    if (str_contains($text, 'youtube.com') || str_contains($text, 'youtu.be')) {
        return $this->askQuality($chatId, $text);
    }

    return $this->sendMessage($chatId, "YouTube videoni linkini yuboring 👇");
}
public function askQuality($chatId, $url)
{
    $token = config('services.telegram.you_tube_bot_token');

    return Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
        'chat_id' => $chatId,
        'text' => "🎬 Formanni tanlang:",

        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '720p 🎞',  'callback_data' => '720|' . $url],
                    ['text' => '1080p 🎬', 'callback_data' => '1080|' . $url],
                ],
                [
                    ['text' => 'MP3 🎧', 'callback_data' => 'mp3|' . $url],
                ]
            ]
        ])
    ]);
}

    public function sendMessage($chatId, $text)
    {
        $token = config('services.telegram.you_tube_bot_token');

        return Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }

    private function downloadAndSend($chatId, $url, $format)
{
    $token = config('services.telegram.you_tube_bot_token');

    $this->sendMessage($chatId, "⏳ Yuklab olayapman... ($format)");

    $ext = $format == 'mp3' ? 'mp3' : 'mp4';
    $file = storage_path("app/video.$ext");

    if ($format == 'mp3') {
        $cmd = "yt-dlp -x --audio-format mp3 -o " . escapeshellarg($file) . " " . escapeshellarg($url) . " 2>&1";
    } elseif ($format == '1080') {
        $cmd = "yt-dlp -f 'bestvideo[height<=1080]+bestaudio' --merge-output-format mp4 -o " . escapeshellarg($file) . " " . escapeshellarg($url) . " 2>&1";
    } else { // 720
        $cmd = "yt-dlp -f 'bestvideo[height<=720]+bestaudio' --merge-output-format mp4 -o " . escapeshellarg($file) . " " . escapeshellarg($url) . " 2>&1";
    }

    $output = shell_exec($cmd);

    if (!file_exists($file)) {
        $this->sendMessage($chatId, "❌ Xatolik: yuklab bo'lmadi!\n$output");
        return response()->json(['status' => 'error', 'output' => $output]);
    }

    if (filesize($file) > 52428800) {
        unlink($file);
        return $this->sendMessage($chatId, "⚠️ Fayl juda katta (50MB+). Boshqa sifatni tanlang.");
    }

    $type = $format == 'mp3' ? 'audio' : 'video';
    $fileName = $format == 'mp3' ? 'audio.mp3' : 'video.mp4';

    Http::attach($type, file_get_contents($file), $fileName)
        ->post("https://api.telegram.org/bot{$token}/send" . ucfirst($type), [
            'chat_id' => $chatId,
        ]);

    unlink($file);
    return response()->json(['status' => 'sent']);
}


}
