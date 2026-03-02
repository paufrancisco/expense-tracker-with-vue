<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with financial summary and charts.
     */
    public function index(Request $request)
    {
        $user         = $request->user();
        $currentMonth = Carbon::now()->month;
        $currentYear  = Carbon::now()->year;

        // Get this month's transactions
        $transactions = $user->transactions()
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->get();

        // Calculate totals
        $totalIncome  = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        // Group expenses by category for pie chart
        $expenseByCategory = $transactions
            ->where('type', 'expense')
            ->groupBy('category')
            ->map(fn ($items) => $items->sum('amount'));

        // Last 6 months summary for bar chart
        $monthlyData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);

            $monthTransactions = $user->transactions()
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->get();

            $monthlyData[] = [
                'month'   => $date->format('M Y'),
                'income'  => $monthTransactions->where('type', 'income')->sum('amount'),
                'expense' => $monthTransactions->where('type', 'expense')->sum('amount'),
            ];
        }

        // Get the 5 most recent transactions
        $recentTransactions = $user->transactions()
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalIncome'        => $totalIncome,
            'totalExpense'       => $totalExpense,
            'balance'            => $balance,
            'expenseByCategory'  => $expenseByCategory,
            'monthlyData'        => $monthlyData,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}