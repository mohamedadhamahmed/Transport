<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ترجمة نص قصير (زي اسم منتج) بالمجان عن طريق نفس نقطة الـ API الداخلية
 * اللي بيستخدمها موقع translate.google.com نفسه (من غير مفتاح API ومن
 * غير أي تكلفة) - مفيش عندنا حاليًا مفتاح Google Cloud Translation رسمي
 * في المشروع، فده أبسط حل شغال دلوقتي لميزة "تفعيل الترجمة" في مودالات
 * "منتج جديد".
 *
 * ملحوظة مهمة: ده endpoint غير رسمي ومحدوش استخدامه بشكل تجاري ضخم -
 * ممكن يتأخر أو يفشل لو جوجل حسّت بطلبات كتير من نفس السيرفر، فالكود هنا
 * بيرجع نص فاضي بهدوء لو حصل أي خطأ عشان حقل الاسم الإنجليزي يفضل قابل
 * للتعديل يدويًا بدل ما توقف الشاشة.
 */
class TranslationController extends Controller
{
    public function translate(Request $request)
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'max:5'],
            'target' => ['nullable', 'string', 'max:5'],
        ]);

        $text = trim($validated['text']);
        $source = $validated['source'] ?? 'ar';
        $target = $validated['target'] ?? 'en';

        if ($text === '') {
            return response()->json(['translated' => '']);
        }

        try {
            $response = Http::timeout(6)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => $source,
                'tl' => $target,
                'dt' => 't',
                'q' => $text,
            ]);

            if (! $response->successful()) {
                return response()->json(['translated' => '', 'error' => 'upstream_error']);
            }

            // شكل الاستجابة الغريب: [[["Translated text","Original",null,null,3]],null,"ar"]
            $body = $response->json();
            $translated = '';
            if (is_array($body) && isset($body[0]) && is_array($body[0])) {
                foreach ($body[0] as $segment) {
                    if (isset($segment[0])) {
                        $translated .= $segment[0];
                    }
                }
            }

            return response()->json(['translated' => trim($translated)]);
        } catch (\Throwable $e) {
            Log::warning('Translation request failed: ' . $e->getMessage());

            return response()->json(['translated' => '', 'error' => 'exception']);
        }
    }
}
