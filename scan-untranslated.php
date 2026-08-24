<?php
/**
 * سكريبت بسيط بيلف على كل ملفات Blade في resources/views ويطلعلك
 * أي سطر فيه نص عربي مكتوب "مباشر" (Hardcoded) مش لافّ جوه __() أو @lang().
 *
 * النص ده لو فضل مكتوب مباشر، هيفضل زي ما هو حتى لو بدّلتي اللغة لإنجليزي،
 * يعني مش متربط فعليًا بنظام الـ localization.
 *
 * طريقة الاستخدام:
 *   1) حطي الملف ده في جذر المشروع بجوار artisan:
 *          C:\xampp\htdocs\my-erp\scan-untranslated.php
 *   2) افتحي cmd/terminal جوه مجلد المشروع ونفذي:
 *          php scan-untranslated.php
 *   3) هيطلعلك قائمة بكل الملفات والسطور المشتبه فيها عشان تراجعيها وتلفيها
 *      بـ __('...') أو __('messages.key').
 */

$viewsPath = __DIR__ . '/resources/views';

if (!is_dir($viewsPath)) {
    fwrite(STDERR, "مجلد resources/views مش موجود هنا. شغّلي السكريبت من جذر المشروع.\n");
    exit(1);
}

function findBladeFiles(string $dir): array
{
    $result = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            $result = array_merge($result, findBladeFiles($path));
        } elseif (str_ends_with($item, '.blade.php')) {
            $result[] = $path;
        }
    }
    return $result;
}

$files = findBladeFiles($viewsPath);
sort($files);

$totalIssues = 0;
$arabicRange = '\x{0600}-\x{06FF}';

foreach ($files as $file) {
    $lines = file($file);
    $fileIssues = [];

    foreach ($lines as $lineNumber => $line) {
        // في نص عربي في السطر؟
        if (!preg_match('/[' . $arabicRange . ']/u', $line)) {
            continue;
        }

        // متجاهلين تعليقات Blade {{-- --}} وتعليقات HTML <!-- -->
        $trimmed = trim($line);
        if (str_starts_with($trimmed, '{{--') || str_starts_with($trimmed, '<!--')) {
            continue;
        }

        // لو السطر فيه __( أو @lang( يبقى غالبًا متعمول له localization صح
        if (str_contains($line, '__(') || str_contains($line, '@lang(')) {
            continue;
        }

        $fileIssues[] = [
            'line' => $lineNumber + 1,
            'text' => trim($line),
        ];
    }

    if (!empty($fileIssues)) {
        $relative = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file);
        echo "\n== $relative ==\n";
        foreach ($fileIssues as $issue) {
            echo "  سطر {$issue['line']}: {$issue['text']}\n";
            $totalIssues++;
        }
    }
}

echo "\n----------------------------------------\n";
if ($totalIssues === 0) {
    echo "تمام، مفيش أي نص عربي مكتوب مباشر برّه __() في كل ملفات resources/views.\n";
} else {
    echo "لقيت {$totalIssues} سطر فيهم نص عربي مش لافّ جوه __()، راجعيهم وحوّليهم بالشكل ده:\n";
    echo "  قبل: <p>مرحبا بك</p>\n";
    echo "  بعد: <p>{{ __('messages.welcome') }}</p>\n";
    echo "  وضيفي المفتاح 'welcome' في lang/ar/messages.php و lang/en/messages.php\n";
}