<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'clients' => Client::count(),
            'quotes' => Quote::count(),
            'pending' => Quote::where('status', 'pending')->count(),
            'accepted_total' => Quote::where('status', 'accepted')->sum('total_amount'),
        ];
        $statusCounts = collect(Quote::STATUSES)->mapWithKeys(
            fn (string $label, string $status): array => [$status => Quote::where('status', $status)->count()]
        );
        $monthlyQuotes = collect(range(5, 0))->map(function (int $monthsAgo): array {
            $month = Carbon::now()->startOfMonth()->subMonths($monthsAgo);

            return [
                'label' => $month->translatedFormat('M'),
                'count' => Quote::whereBetween('issue_date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])->count(),
            ];
        });
        $maxMonthly = max(1, (int) $monthlyQuotes->max('count'));
        $recentQuotes = Quote::with('client')->latest('issue_date')->limit(5)->get();
        $recentInquiries = Inquiry::latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'statusCounts', 'monthlyQuotes', 'maxMonthly', 'recentQuotes', 'recentInquiries'));
    }
}
