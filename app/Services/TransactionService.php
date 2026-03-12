<?php

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as SupportCollection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionService
{
    /** @var int Number of transactions per page */
    private const PER_PAGE = 9;

    /** @var array<string> Columns included in all export formats */
    private const EXPORT_HEADERS = ['Date', 'Type', 'Title', 'Category', 'Amount', 'Description'];

    /** @var array<string> Allowed filter keys from the request */
    private const ALLOWED_FILTERS = ['type', 'category', 'date_from', 'date_to', 'search'];

    /**
     * Fetch a paginated and filtered list of transactions for the given user.
     *
     * @param  User    $user    the authenticated user
     * @param  Request $request the incoming request containing filter parameters
     * @return LengthAwarePaginator
     */
    public function getPaginatedTransactions(User $user, Request $request): LengthAwarePaginator
    {
        $query = $user->transactions()->orderBy('transaction_date', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    /**
     * Retrieve the user's active filter values from the request.
     *
     * @param  Request $request the incoming request
     * @return array
     */
    public function getActiveFilters(Request $request): array
    {
        return $request->only(self::ALLOWED_FILTERS);
    }

    /**
     * Retrieve the authenticated user's categories grouped by type.
     *
     * @param  User $user the authenticated user
     * @return array
     */
    public function getCategoriesGroupedByType(User $user): array
    {
        $grouped = $user->categories()
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return [
            'income'  => $grouped->get('income', collect()),
            'expense' => $grouped->get('expense', collect()),
        ];
    }

    /**
     * Fetch all transactions for the given user ordered by date descending.
     *
     * @param  User $user the authenticated user
     * @return Collection
     */
    public function getAllTransactions(User $user): Collection
    {
        return $user->transactions()
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Export transactions as a PDF download response.
     *
     * @param  Collection $transactions the transactions to export
     * @return mixed
     */
    public function exportAsPdf(Collection $transactions): mixed
    {
        return Pdf::loadView('exports.transactions-pdf', [
            'transactions' => $transactions,
        ])->download('transactions.pdf');
    }

    /**
     * Export transactions as an Excel download response.
     *
     * @param  Collection $transactions the transactions to export
     * @return StreamedResponse
     */
    public function exportAsExcel(Collection $transactions): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->fromArray(self::EXPORT_HEADERS, null, 'A1');

        $transactions->each(function ($transaction, int $index) use ($sheet) {
            $sheet->fromArray(
                $this->formatTransactionRow($transaction),
                null,
                'A' . ($index + 2)
            );
        });

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'transactions.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export transactions as a CSV download response.
     *
     * @param  Collection $transactions the transactions to export
     * @return StreamedResponse
     */
    public function exportAsCsv(Collection $transactions): StreamedResponse
    {
        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, self::EXPORT_HEADERS);

            $transactions->each(function ($transaction) use ($handle) {
                fputcsv($handle, $this->formatTransactionRow($transaction));
            });

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=transactions.csv',
        ]);
    }

    /**
     * Format a single transaction into an exportable row array.
     *
     * @param  \App\Models\Transaction $transaction the transaction to format
     * @return array
     */
    private function formatTransactionRow(\App\Models\Transaction $transaction): array
    {
        return [
            $transaction->transaction_date->format('Y-m-d'),
            $transaction->type,
            $transaction->title,
            $transaction->category,
            $transaction->amount,
            $transaction->description,
        ];
    }

    
    /**
     * Fetch filtered transactions for export (no pagination).
     *
     * @param  User    $user    the authenticated user
     * @param  Request $request the incoming request containing filter parameters
     * @return Collection
     */
    public function getFilteredTransactions(User $user, Request $request): Collection
    {
        $query = $user->transactions()->orderBy('transaction_date', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        return $query->get();
    } 
















}