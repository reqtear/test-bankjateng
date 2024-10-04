<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Card;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use Carbon\Carbon;
use App\Models\Transaction;

class CardController extends BaseController
{
    public function addCard(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'card_number' => 'required|digits:16|unique:card,card_number',
            'cvv' => 'required|digits:3',
            'expiry_date' => 'required|min:7|max:7',
            'pin' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 500);
        }

        // Create a new card record in the database
        $card = new Card();
        $card->card_number = $request->card_number;
        $card->cvv = $request->cvv;
        $card->expiry_date = Carbon::parse('01/' . $request->expiry_date);
        $card->user_id = auth()->user()->id;
        $card->saldo = 0;
        $card->pin = $request->pin;
        $card->save();

        return $this->sendResponse($card, 'Card added successfully.', 201);
    }

    public function getCardList(Request $request): JsonResponse
    {
        // Get all cards associated with the authenticated user
        $cards = Card::where('user_id', auth()->user()->id)->get();

        return $this->sendResponse($cards, 'Card list retrieved successfully.');
    }

    public function getCardDetail($id): JsonResponse
    {
        $card = Card::find($id);

        if (!$card) {
            return $this->sendError('Card not found.', [], 404);
        }

        return $this->sendResponse($card, 'Card detail retrieved successfully.');
    }

    public function setPin($id, Request $request): JsonResponse
    {
        $card = Card::find($id);

        if (!$card) {
            return $this->sendError('Card not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'pin' => 'required|digits:6|numeric'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 500);
        }

        $card->pin = $request->pin;
        $card->save();

        return $this->sendResponse($card, 'Set pin successfully.');
    }

    public function deleteCard(Request $request)
    {
        $card = Card::find($request->id);

        if (!$card) {
            return $this->sendError('Card not found.', [], 404);
        }

        $trans = Transaction::where('from_card_id', $card->id)->orWhere('to_card_id', $card->id)->first();

        if ($trans) {
            return $this->sendError('Cannot delete card with transactions.', [], 500);
        }

        $card->delete();

        return $this->sendResponse([], 'Card deleted successfully.');
    }
}
