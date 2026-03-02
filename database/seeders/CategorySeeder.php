<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $income = [
            'Salary',
            'Freelance', 
            'Investment', 
            'Gift', 
            'Other Income'
        ];

        $expense = [
            'Food', 
            'Transport', 
            'Housing', 
            'Utilities', 
            'Healthcare',
            'Entertainment', 
            'Shopping', 
            'Education', 
            'Other Expense'
        ];

        foreach (User::all() as $user) {
            foreach ($income as $name) {
                Category::create([
                    'user_id' => $user->id,
                    'name'    => $name,
                    'type'    => 'income',
                    'color'   => '#10B981',
                ]);
            }

            foreach ($expense as $name) {
                Category::create([
                    'user_id' => $user->id,
                    'name'    => $name,
                    'type'    => 'expense',
                    'color'   => '#EF4444',
                ]);
            }
        }
    }
}