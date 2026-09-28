<?php

namespace App\Http\Controllers;

use App\Models\FiscalYearClosing;
use App\Services\FiscalYearClosingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * شاشة إقفال السنة المالية وترحيل الأرصدة للسنة الجديدة.
 */
class FiscalYearClosingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:year_closing.manage')];
    }

    public function __construct(private FiscalYearClosingService $service)
    {
    }

    public function index(Request $request)
    {
        $closings = FiscalYearClosing::orderByDesc('closing_date')->get();
        $last = $closings->first();

        $defaultDate = $last
            ? $last->closing_date->copy()->addYear()->endOfYear()
            : now()->subYear()->endOfYear();
        if ($defaultDate->isFuture()) {
            $defaultDate = now()->startOfDay();
        }

        $closingDate = $request->filled('closing_date')
            ? Carbon::parse($request->input('closing_date'))
            : $defaultDate;

        $preview = null;
        $previewError = null;
        try {
            $preview = $this->service->preview($closingDate);
        } catch (ValidationException $e) {
            $previewError = collect($e->errors())->flatten()->first();
        }

        return view('year-closing.index', [
            'closings' => $closings,
            'lastClosing' => $last,
            'closingDate' => $closingDate,
            'preview' => $preview,
            'previewError' => $previewError,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'closing_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'confirm' => ['accepted'],
        ], [
            'confirm.accepted' => 'لازم تأكد إنك راجعت المعاينة قبل الإقفال.',
        ]);

        $closing = $this->service->close(
            Carbon::parse($validated['closing_date']),
            $validated['notes'] ?? null,
            Auth::id()
        );

        return redirect()->route('year-closing.show', $closing)
            ->with('success', 'تم إقفال السنة المالية ' . $closing->fiscal_year . ' وترحيل الأرصدة للسنة الجديدة.');
    }

    public function show(FiscalYearClosing $closing)
    {
        $closing->load(['openingBalances' => fn ($q) => $q->orderBy('category')->orderBy('account_number'), 'retainedAccount']);

        return view('year-closing.show', [
            'closing' => $closing,
            'isLast' => optional($this->service->lastClosing())->id === $closing->id,
        ]);
    }

    public function destroy(FiscalYearClosing $closing)
    {
        $year = $closing->fiscal_year;
        $this->service->reopen($closing);

        return redirect()->route('year-closing.index')
            ->with('success', 'تم إلغاء إقفال سنة ' . $year . ' وعكس قيد الإقفال.');
    }
}
