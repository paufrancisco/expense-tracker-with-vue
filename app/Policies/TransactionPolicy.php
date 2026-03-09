<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine if the user can view the transaction.
     *
     * @param  User        $user        the authenticated user
     * @param  Transaction $transaction the transaction to view
     * @return bool
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $this->isOwner($user, $transaction);
    }

    /**
     * Determine if the user can update the transaction.
     *
     * @param  User        $user        the authenticated user
     * @param  Transaction $transaction the transaction to update
     * @return bool
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $this->isOwner($user, $transaction);
    }

    /**
     * Determine if the user can delete the transaction.
     *
     * @param  User        $user        the authenticated user
     * @param  Transaction $transaction the transaction to delete
     * @return bool
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->isOwner($user, $transaction);
    }

    /**
     * Determine if the user is the owner of the transaction.
     *
     * @param  User        $user        the authenticated user
     * @param  Transaction $transaction the transaction to check ownership of
     * @return bool
     */
    private function isOwner(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}