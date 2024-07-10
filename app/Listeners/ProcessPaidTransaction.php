<?php

namespace App\Listeners;

use App\Events\SaleCreated;
use App\Events\TransactionPaid;
use App\Jobs\SendPaymentConfirmationNotification;
use App\Models\AuthorTax;
use App\Models\Sale;

class ProcessPaidTransaction
{
    public function handle(TransactionPaid $event)
    {
        $trx = $event->transaction;

        if ($trx->isPaid()) {
            dispatch(new SendPaymentConfirmationNotification($trx));

            $trxItems = $trx->trxItems;
            $user = $trx->user;
            $user_country = @$user->address->country;

            foreach ($trxItems as $trxItem) {
                $item = $trxItem->item;
                $author = $item->author;

                $buyerFee = $trxItem->isLicenseTypeRegular() ? $item->category->regular_buyer_fee : $item->category->extended_buyer_fee;

                $amountWithoutBuyerFee = $buyerFee > 0 ? ($trxItem->price - $buyerFee) : $trxItem->price;
                $authorFeesPercentage = $author->level->fees;
                $authorFeesAmount = $authorFeesPercentage > 0 ? ($amountWithoutBuyerFee * $authorFeesPercentage) / 100 : 0;
                $authorEarningAmount = $authorFeesAmount > 0 ? ($amountWithoutBuyerFee - $authorFeesAmount) : $amountWithoutBuyerFee;

                $author_tax = null;
                $authorTax = AuthorTax::whereJsonContains('countries', $user_country)->first();
                if ($authorTax) {
                    $authorTaxAmount = ($authorEarningAmount * $authorTax->rate) / 100;
                    $authorEarningAmount = ($authorEarningAmount - $authorTaxAmount);
                    $author_tax = [
                        'name' => $authorTax->name,
                        'rate' => $authorTax->rate,
                        'amount' => round($authorTaxAmount, 2),
                    ];
                }

                for ($i = 0; $i < $trxItem->quantity; $i++) {
                    $sale = new Sale();
                    $sale->author_id = $author->id;
                    $sale->user_id = $user->id;
                    $sale->item_id = $item->id;
                    $sale->license_type = $trxItem->license_type;
                    $sale->price = $trxItem->price;
                    $sale->buyer_fee = $buyerFee;
                    if ($trx->hasTax()) {
                        $sale->buyer_tax = [
                            'name' => $trx->tax->name,
                            'rate' => $trx->tax->rate,
                            'amount' => round($trx->tax->amount, 2),
                        ];
                    }
                    $sale->author_fee = $authorFeesAmount;
                    $sale->author_tax = $author_tax;
                    $sale->author_earning = $authorEarningAmount;
                    $sale->country = $user_country ?? null;
                    $sale->save();
                    event(new SaleCreated($sale));
                }
            }
        }
    }
}