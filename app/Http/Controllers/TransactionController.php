<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of transactions with optional filters.
     *
     * @param Request $request the incoming request
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
    {
        $query = $request->user()->transactions()
            ->orderBy('transaction_date', 'desc');

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->date_from) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->paginate(15);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only([
                'type',
                'category',
                'date_from',
                'date_to',
                'search',
            ]),
        ]);
    }

    /**
     * Show the form for creating a new transaction.
     *
     * @param Request $request the incoming request
     * @return \Inertia\Response
     */
    public function create(Request $request): \Inertia\Response
    {
        $categories = $request->user()->categories()->get();

        return Inertia::render('Transactions/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created transaction in the database.
     *
     * @param Request $request the incoming request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $request->user()->transactions()->create($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction added successfully!');
    }

    /**
     * Show the form for editing an existing transaction.
     *
     * @param Request $request the incoming request
     * @param Transaction $transaction the transaction to edit
     * @return \Inertia\Response
     */
    public function edit(Request $request, Transaction $transaction): \Inertia\Response
    {
        $this->authorize('update', $transaction);

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'categories' => $request->user()->categories()->get(),
        ]);
    }

    /**
     * Update the specified transaction in the database.
     *
     * @param Request $request the incoming request
     * @param Transaction $transaction the transaction to update
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Transaction $transaction): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully!');
    }

    /**
     * Remove the specified transaction from the database.
     *
     * @param Transaction $transaction the transaction to delete
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Transaction $transaction): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully!');
    }

    /**
     * Export transactions in the specified format.
     *
     * @param Request $request the incoming request
     * @return mixed
     */
    public function export(Request $request): mixed
    {
        $format = $request->input('format', 'csv');
        $transactions = $request->user()->transactions()
            ->orderBy('transaction_date', 'desc')
            ->get();

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.transactions-pdf', [
                'transactions' => $transactions,
            ]);

            return $pdf->download('transactions.pdf');
        }

        if ($format === 'excel') {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->fromArray(
                ['Date', 'Type', 'Title', 'Category', 'Amount', 'Description'],
                null,
                'A1'
            );

            $row = 2;

            foreach ($transactions as $transaction) {
                $sheet->fromArray([
                    $transaction->transaction_date->format('Y-m-d'),
                    $transaction->type,
                    $transaction->title,
                    $transaction->category,
                    $transaction->amount,
                    $transaction->description,
                ], null, 'A' . $row);

                $row++;
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, 'transactions.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=transactions.csv',
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Type', 'Title', 'Category', 'Amount', 'Description']);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->transaction_date->format('Y-m-d'),
                    $transaction->type,
                    $transaction->title,
                    $transaction->category,
                    $transaction->amount,
                    $transaction->description,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}