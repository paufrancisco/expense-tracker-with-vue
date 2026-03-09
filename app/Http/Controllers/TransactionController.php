<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    /** @var TransactionService */
    private TransactionService $transactionService;

    /**
     * @param  TransactionService $transactionService the service handling transaction logic
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a paginated and filtered listing of transactions.
     *
     * @param  Request $request the incoming request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Transactions/Index', [
            'transactions' => $this->transactionService->getPaginatedTransactions($user, $request),
            'filters'      => $this->transactionService->getActiveFilters($request),
            ...$this->transactionService->getCategoriesGroupedByType($user),
        ]);
    }

    /**
     * Show the form for creating a new transaction.
     *
     * @param  Request $request the incoming request
     * @return Response
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Transactions/Create',
            $this->transactionService->getCategoriesGroupedByType($request->user())
        );
    }

    /**
     * Store a newly created transaction in the database.
     *
     * @param  StoreTransactionRequest $request the incoming HTTP request
     * @return RedirectResponse
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $request->user()->transactions()->create($request->validated());
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction added successfully!');
    }

    /**
     * Show the form for editing an existing transaction.
     *
     * @param  Request     $request     the incoming request
     * @param  Transaction $transaction the transaction to edit
     * @return Response
     */
    public function edit(Request $request, Transaction $transaction): Response
    {
        $this->authorize('update', $transaction);

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            ...$this->transactionService->getCategoriesGroupedByType($request->user()),
        ]);
    }

    /**
     * Update the specified transaction in the database.
     *
     * @param  UpdateTransactionRequest $request     the incoming HTTP request
     * @param  Transaction              $transaction the transaction to update
     * @return RedirectResponse
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($request, $transaction) {
            $transaction->update($request->validated());
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully!');
    }

    /**
     * Remove the specified transaction from the database.
     *
     * @param  Transaction $transaction the transaction to delete
     * @return RedirectResponse
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        DB::transaction(function () use ($transaction) {
            $transaction->delete();
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully!');
    }

    /**
     * Export all transactions in the requested format.
     *
     * @param  Request $request the incoming request
     * @return mixed
     */
    public function export(Request $request): mixed
    {
        $format       = $request->input('format', 'csv');
        $transactions = $this->transactionService->getAllTransactions($request->user());

        return match ($format) {
            'pdf'   => $this->transactionService->exportAsPdf($transactions),
            'excel' => $this->transactionService->exportAsExcel($transactions),
            default => $this->transactionService->exportAsCsv($transactions),
        };
    }
}