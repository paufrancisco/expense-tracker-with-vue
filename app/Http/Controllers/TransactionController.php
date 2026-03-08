<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of transactions with optional filters.
     *
     * @param Request $request the incoming request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $query = $request->user()->transactions()
            ->orderBy('transaction_date', 'desc');

        if ($request->input('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->input('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->input('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->input('date_from'));
        }

        if ($request->input('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->input('date_to'));
        }

        if ($request->input('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        $transactions = $query->paginate(9);

        return Inertia::render('Transactions/Index', array_merge(
            ['transactions' => $transactions],
            ['filters' => $request->only(['type', 'category', 'date_from', 'date_to', 'search'])],
            $this->getCategoriesGroupedByType($request)
        ));
    }

    /**
     * Retrieve the authenticated user's categories grouped by type.
     *
     * @param Request $request the incoming request
     * @return array
     */
    private function getCategoriesGroupedByType(Request $request): array
    {
        $grouped = $request->user()
            ->categories()
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return [
            'income'  => $grouped->get('income', collect()),
            'expense' => $grouped->get('expense', collect()),
        ];
    }

    /**
     * Show the form for creating a new transaction.
     *
     * @param Request $request the incoming request
     * @return Response
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Transactions/Create', $this->getCategoriesGroupedByType($request));
    }

    /**
     * Store a newly created transaction in the database.
     *
     * @param Request $request the incoming request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
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
     *
     * @param Request $request the incoming request
     * @param Transaction $transaction the transaction to edit
     * @return Response
     */
    public function edit(Request $request, Transaction $transaction): Response
    {
        $this->authorize('update', $transaction);

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'categories'  => $request->user()->categories()->orderBy('name')->get(),  
        ]);
    }

    /**
     * Update the specified transaction in the database.
     *
     * @param Request $request the incoming request
     * @param Transaction $transaction the transaction to update
     * @return RedirectResponse
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
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
     *
     * @param Transaction $transaction the transaction to delete
     * @return RedirectResponse
     */
    public function destroy(Transaction $transaction): RedirectResponse
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
            return $this->exportAsPdf($transactions);
        }

        if ($format === 'excel') {
            return $this->exportAsExcel($transactions);
        }

        return $this->exportAsCsv($transactions);
    }

    /**
     * Export transactions as a PDF file.
     *
     * @param \Illuminate\Database\Eloquent\Collection $transactions the transactions to export
     * @return mixed
     */
    private function exportAsPdf(\Illuminate\Database\Eloquent\Collection $transactions): mixed
    {
        $pdf = Pdf::loadView('exports.transactions-pdf', [
            'transactions' => $transactions,
        ]);

        return $pdf->download('transactions.pdf');
    }

    /**
     * Export transactions as an Excel file.
     *
     * @param \Illuminate\Database\Eloquent\Collection $transactions the transactions to export
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportAsExcel(\Illuminate\Database\Eloquent\Collection $transactions): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
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

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'transactions.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export transactions as a CSV file.
     *
     * @param \Illuminate\Database\Eloquent\Collection $transactions the transactions to export
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportAsCsv(\Illuminate\Database\Eloquent\Collection $transactions): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
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