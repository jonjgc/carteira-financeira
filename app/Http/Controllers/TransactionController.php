<?php

namespace App\Http\Controllers;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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
    public function transfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'receiver_email' => 'required|email|exists:users,email',
        ]);

        $senderWallet = $request->user()->wallet;
        $receiver = User::where('email', $request->receiver_email)->first();
        $receiverWallet = $receiver->wallet;

        if ($senderWallet->balance < $request->amount) {
            return response()->json(['error' => 'Saldo insuficiente'], 400);
        }

        DB::transaction(function () use ($senderWallet, $receiverWallet, $request) {
            $senderWallet->decrement('balance', $request->amount);
            $receiverWallet->increment('balance', $request->amount);

            $senderTransaction = Transaction::create([
                'wallet_id' => $senderWallet->id,
                'amount' => $request->amount,
                'type' => 'transfer_sent',
            ]);

            Transaction::create([
                'wallet_id' => $receiverWallet->id,
                'amount' => $request->amount,
                'type' => 'transfer_received',
                'related_transaction_id' => $senderTransaction->id,
            ]);
        });

        return response()->json(['message' => 'Transferência realizada com sucesso', 'balance' => $senderWallet->balance], 200);
    }
}