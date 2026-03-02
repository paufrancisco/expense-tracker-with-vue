<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with financial summary and charts.
     *
     * @param Request $request the incoming request
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
    {
        $user = $request->user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $transactions = $user->transactions()
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        $expenseByCategory = $transactions
            ->where('type', 'expense')
            ->groupBy('category')
            ->map(fn ($items) => $items->sum('amount'));

        $incomeByCategory = $transactions
            ->where('type', 'income')
            ->groupBy('category')
            ->map(fn ($items) => $items->sum('amount'));

        $monthlyData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);

            $monthTransactions = $user->transactions()
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->get();

            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'income' => $monthTransactions->where('type', 'income')->sum('amount'),
                'expense' => $monthTransactions->where('type', 'expense')->sum('amount'),
            ];
        }

        $recentTransactions = $user->transactions()
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($transaction) => [
                'id' => $transaction->id,
                'title' => $transaction->title,
                'category' => $transaction->category,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            ]);

        return Inertia::render('Dashboard', [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'expenseByCategory' => $expenseByCategory,
            'incomeByCategory' => $incomeByCategory,
            'monthlyData' => $monthlyData,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}