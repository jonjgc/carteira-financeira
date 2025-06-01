<?php

namespace App\Http\Controllers;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function deposit(Request $request)
    {
       // dd(Wallet::first()); // Teste temporário
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $wallet = $request->user()->wallet;
        DB::transaction(function () use ($wallet, $request) {
            $wallet->increment('balance', $request->amount);
            Transaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'type' => 'deposit',
            ]);
        });

        return response()->json(['message' => 'Depósito realizado com sucesso', 'balance' => $wallet->balance], 200);
    }
}