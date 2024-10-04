<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Card;
use App\Models\Transaction;

class TransactionController extends BaseController
{
    public function postingTransaction(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'from_card_id' => 'required',
            'to_card_id' => 'nullable',
            'amount' => 'required|max_digits:15',
            'type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 500);
        }

        $from_card = Card::find($request->from_card_id);

        if (!$from_card) {
            return $this->sendError('Source card not found.', [], 404);
        }

        if ($request->to_card_id) {
            $to_card = Card::find($request->to_card_id);

            if (!$to_card) {
                return $this->sendError('Target card not found.', [], 404);
            }
        }

        if ($request->type == 'transfer' && $from_card->user_id != auth()->user()->id) {
            return $this->sendError('Unauthorized transaction.', [], 401);
        }

        if ($request->type == 'transfer') {
            if ($from_card->saldo < $request->amount) {
                return $this->sendError('Insufficient balance.', [], 402);
            }

            // Update saldo of both cards
            $from_card->saldo -= $request->amount;
            $from_card->save();

            if ($request->to_card_id) {
                $to_card->saldo += $request->amount;
                $to_card->save();
            }
        } else if ($request->type == 'deposit') {
            $from_card->saldo += $request->amount;
            $from_card->save();
        } else if ($request->type == 'withdraw') {
            if ($from_card->saldo < $request->amount) {
                return $this->sendError('Insufficient balance.', [], 402);
            }

            $from_card->saldo -= $request->amount;
            $from_card->save();
        }

        // Create a new card record in the database
        $trans = new Transaction();
        $trans->from_card_id = $request->from_card_id;
        $trans->to_card_id = $request->to_card_id;
        $trans->amount = $request->amount;
        $trans->user_id = auth()->user()->id;
        $trans->type = $request->type;
        $trans->save();

        return $this->sendResponse($trans, 'Transaction added successfully.', 201);
    }

    public function getUserTransactionList($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $transactions = Transaction::where('user_id', $id)->get();

        return $this->sendResponse($transactions, 'User Transaction list retrieved successfully.');
    }

    public function getCardTransactionList($id)
    {
        $card = Card::find($id);

        if (!$card) {
            return $this->sendError('Card not found.', [], 404);
        }

        $transactions = Transaction::where('from_card_id', $id)->orWhere('to_card_id', $id)->get();

        return $this->sendResponse($transactions, 'Card Transaction list retrieved successfully.');
    }

    public function getTransactionDetail($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return $this->sendError('Transaction not found.', [], 404);
        }

        return $this->sendResponse($transaction, 'Transaction detail retrieved successfully.');
    }
}
