<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Auto-create default income categories for the new user
        $incomeCategories = [
            'Salary',
            'Freelance',
            'Investment',
            'Gift',
        ];

        // Auto-create default expense categories for the new user
        $expenseCategories = [
            'Food',
            'Transport',
            'Housing',
            'Utilities',
            'Healthcare',
            'Entertainment',
            'Shopping',
        ];

        foreach ($incomeCategories as $category) {
            $user->categories()->create([
                'name'  => $category,
                'type'  => 'income',
                'color' => '#10B981',
            ]);
        }

        foreach ($expenseCategories as $category) {
            $user->categories()->create([
                'name'  => $category,
                'type'  => 'expense',
                'color' => '#EF4444',
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}