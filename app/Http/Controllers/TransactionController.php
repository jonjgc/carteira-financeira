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

    public function reverse(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);
        $user = $request->user();

        // Verificar se o usuário é o remetente ou destinatário da transação
        $isSender = $transaction->wallet && $transaction->wallet->user_id === $user->id;
        $relatedTransaction = $transaction->related_transaction_id ? Transaction::find($transaction->related_transaction_id) : null;
        $isReceiver = $relatedTransaction && $relatedTransaction->wallet && $relatedTransaction->wallet->user_id === $user->id;

        if (!$isSender && !$isReceiver) {
            return response()->json(['error' => 'Você não tem permissão para reverter esta transação'], 403);
        }
        // Verificar se a transação é elegível para reversão
        if (!in_array($transaction->type, ['transfer_sent', 'transfer_received'])) {
            return response()->json(['error' => 'Apenas transferências podem ser revertidas'], 400);
        }

        // Verificar se a transação já foi revertida
        if (Transaction::where('related_transaction_id', $transaction->id)
                    ->whereIn('type', ['reverse_sent', 'reverse_received'])->exists()) {
            return response()->json(['error' => 'Esta transação já foi revertida'], 400);
        }

        // Identificar transações relacionadas
        $senderTransaction = $transaction->type === 'transfer_sent' ? $transaction : Transaction::find($transaction->related_transaction_id);
        $receiverTransaction = $transaction->type === 'transfer_received' ? $transaction : Transaction::where('related_transaction_id', $senderTransaction->id)->first();

        if (!$senderTransaction || !$receiverTransaction) {
            return response()->json(['error' => 'Transação relacionada não encontrada'], 404);
        }

        $senderWallet = $senderTransaction->wallet;
        $receiverWallet = $receiverTransaction->wallet;

        // Verificar saldo do destinatário
        if ($receiverWallet->balance < $transaction->amount) {
            return response()->json(['error' => 'Saldo insuficiente no destinatário para reversão'], 400);
        }

        DB::transaction(function () use ($senderWallet, $receiverWallet, $transaction) {
            // Reverter saldos
            $senderWallet->increment('balance', $transaction->amount);
            $receiverWallet->decrement('balance', $transaction->amount);

            // Registrar transações de reversão
            $reverseSenderTransaction = Transaction::create([
                'wallet_id' => $senderWallet->id,
                'amount' => $transaction->amount,
                'type' => 'reverse_received',
                'related_transaction_id' => $transaction->id,
            ]);

            Transaction::create([
                'wallet_id' => $receiverWallet->id,
                'amount' => $transaction->amount,
                'type' => 'reverse_sent',
                'related_transaction_id' => $reverseSenderTransaction->id,
            ]);
        });

        return response()->json(['message' => 'Transação revertida com sucesso', 'balance' => $senderWallet->balance], 200);
    }

    
}