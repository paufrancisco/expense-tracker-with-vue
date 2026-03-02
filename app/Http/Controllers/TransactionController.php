<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of transactions with optional filters.
     */
    public function index(Request $request)
    {
        $query = $request->user()->transactions()
            ->orderBy('transaction_date', 'desc');

        // Filter by type (income/expense)
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->category) {
            $query->where('category', $request->category);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        // Search by title
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->paginate(15);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters'      => $request->only([
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
     */
    public function create(Request $request)
    {
        $categories = $request->user()->categories()->get();

        return Inertia::render('Transactions/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created transaction in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'             => 'required|in:income,expense',
            'title'            => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'category'         => 'required|string',
            'description'      => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $request->user()->transactions()->create($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction added successfully!');
    }

    /**
     * Show the form for editing an existing transaction.
     */
    public function edit(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'categories'  => $request->user()->categories()->get(),
        ]);
    }

    /**
     * Update the specified transaction in the database.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'type'             => 'required|in:income,expense',
            'title'            => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'category'         => 'required|string',
            'description'      => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully!');
    }

    /**
     * Remove the specified transaction from the database.
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully!');
    }

    /**
     * Export all transactions as a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $transactions = $request->user()->transactions()
            ->orderBy('transaction_date', 'desc')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=transactions.csv',
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // CSV header row
            fputcsv($handle, [
                'Date',
                'Type',
                'Title',
                'Category',
                'Amount',
                'Description',
            ]);

            // CSV data rows
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