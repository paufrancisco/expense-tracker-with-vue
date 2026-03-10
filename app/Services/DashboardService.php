<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class DashboardService
{
    
    /** @var int Number of recent transactions to show on the dashboard */
    private const RECENT_LIMIT = 10;
    
    /**
     * Build the complete dashboard payload for the given user.
     *
     * @param  User   $user the authenticated user
     * @param  Carbon $now  the current datetime reference
     * @return array
     */
    public function getDashboardData(User $user, Carbon $now): array
    {
        $currentMonthTransactions = $this->getCurrentMonthTransactions($user, $now);

        return [
            'totalIncome'        => $this->sumByType($currentMonthTransactions, 'income'),
            'totalExpense'       => $this->sumByType($currentMonthTransactions, 'expense'),
            'balance'            => $this->sumByType($currentMonthTransactions, 'income')
                                    - $this->sumByType($currentMonthTransactions, 'expense'),
            'expenseByCategory'  => $this->groupByCategory($currentMonthTransactions, 'expense'),
            'incomeByCategory'   => $this->groupByCategory($currentMonthTransactions, 'income'), 
            'recentTransactions' => $this->getRecentTransactions($user),
        ];
    }

    /**
     * Fetch all transactions for the current month.
     *
     * @param  User   $user the authenticated user
     * @param  Carbon $now  the current datetime reference
     * @return Collection
     */
    private function getCurrentMonthTransactions(User $user, Carbon $now): Collection
    {
        return $user->transactions()
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->get();
    }

    /**
     * Sum transaction amounts filtered by type.
     *
     * @param  Collection $transactions the transaction collection to sum
     * @param  string     $type         the transaction type to filter by
     * @return float
     */
    private function sumByType(Collection $transactions, string $type): float
    {
        return (float) $transactions->where('type', $type)->sum('amount');
    }

    /**
     * Group transaction amounts by category for a given type.
     *
     * @param  Collection $transactions the transaction collection to group
     * @param  string     $type         the transaction type to filter by
     * @return SupportCollection
     */
    private function groupByCategory(Collection $transactions, string $type): SupportCollection
    {
        return $transactions
            ->where('type', $type)
            ->groupBy('category')
            ->map(fn (Collection $items) => (float) $items->sum('amount'));
    }

    /**
     * Fetch and shape the most recent transactions for display.
     *
     * @param  User $user the authenticated user
     * @return SupportCollection
     */
    private function getRecentTransactions(User $user): SupportCollection
    {
        return $user->transactions()
            ->orderBy('transaction_date', 'desc')
            ->limit(self::RECENT_LIMIT)
            ->get()
            ->map(fn ($transaction) => [
                'id'               => $transaction->id,
                'title'            => $transaction->title,
                'category'         => $transaction->category,
                'type'             => $transaction->type,
                'amount'           => (float) $transaction->amount,
                'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            ]);
    }
}