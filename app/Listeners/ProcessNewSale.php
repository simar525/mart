<?php

namespace App\Listeners;

use App\Events\SaleCreated;
use App\Jobs\SendPurchaseConfirmationNotification;
use App\Models\Purchase;
use App\Models\ReferralEarning;
use App\Models\Statement;
use Str;

class ProcessNewSale
{
    public function handle(SaleCreated $event)
    {
        $sale = $event->sale;

        $author = $sale->author;
        $user = $sale->user;
        $item = $sale->item;

        $purchase = new Purchase();
        $purchase->user_id = $user->id;
        $purchase->author_id = $author->id;
        $purchase->sale_id = $sale->id;
        $purchase->item_id = $item->id;
        $purchase->license_type = $sale->license_type;
        $purchase->code = $this->generatePurchaseCode();
        $purchase->save();

        $author->increment('balance', $sale->author_earning);
        $author->increment('total_sales');
        $author->increment('total_sales_amount', $sale->price);

        $saleStatement = new Statement();
        $saleStatement->user_id = $user->id;
        $saleStatement->title = translate('[Purchase] #:id (:item_name)', [
            'id' => $purchase->id,
            'item_name' => $item->name,
        ]);
        $saleStatement->amount = $sale->price;
        $saleStatement->total = $sale->price;
        $saleStatement->type = Statement::TYPE_DEBIT;
        $saleStatement->save();

        $buyerTax = $sale->buyer_tax;
        if ($buyerTax) {
            $amount = ($sale->price * $buyerTax->rate) / 100;
            $saleStatement = new Statement();
            $saleStatement->user_id = $user->id;
            $saleStatement->title = translate('[:tax_name (:tax_rate%)] Purchase #:id (:item_name)', [
                'id' => $purchase->id,
                'item_name' => $item->name,
                'tax_name' => $buyerTax->name,
                'tax_rate' => $buyerTax->rate,
            ]);
            $saleStatement->amount = $amount;
            $saleStatement->total = $amount;
            $saleStatement->type = Statement::TYPE_DEBIT;
            $saleStatement->save();
        }

        $authorTax = $sale->author_tax;
        $authorEarning = $authorTax ? ($sale->author_earning + $authorTax->amount) : $sale->author_earning;

        $saleStatement = new Statement();
        $saleStatement->user_id = $author->id;
        $saleStatement->title = translate('[Sale] #:id (:item_name)', [
            'id' => $sale->id,
            'item_name' => $item->name,
        ]);
        $saleStatement->amount = $sale->price;
        $saleStatement->buyer_fee = $sale->buyer_fee;
        $saleStatement->author_fee = $sale->author_fee;
        $saleStatement->total = $authorEarning;
        $saleStatement->type = Statement::TYPE_CREDIT;
        $saleStatement->save();

        if ($authorTax) {
            $saleStatement = new Statement();
            $saleStatement->user_id = $author->id;
            $saleStatement->title = translate('[:tax_name (:tax_rate%)] Sale #:id (:item_name)', [
                'id' => $sale->id,
                'item_name' => $item->name,
                'tax_name' => $authorTax->name,
                'tax_rate' => $authorTax->rate,
            ]);
            $saleStatement->amount = $authorTax->amount;
            $saleStatement->total = $authorTax->amount;
            $saleStatement->type = Statement::TYPE_DEBIT;
            $saleStatement->save();
        }

        if (@settings('referral')->status) {
            $referral = $user->referral;
            if ($referral) {
                $referralAuthor = $referral->author;
                $referralEarningAmount = ($sale->price * @settings('referral')->percentage) / 100;

                $referralEarning = new ReferralEarning();
                $referralEarning->referral_id = $referral->id;
                $referralEarning->author_id = $referralAuthor->id;
                $referralEarning->sale_id = $sale->id;
                $referralEarning->author_earning = $referralEarningAmount;
                $referralEarning->save();

                $referral->increment('earnings', $referralEarningAmount);
                $referralAuthor->increment('balance', $referralEarningAmount);
                $referralAuthor->increment('total_referrals_earnings', $referralEarningAmount);

                $referralStatement = new Statement();
                $referralStatement->user_id = $referralAuthor->id;
                $referralStatement->title = translate('[Referral Earnings] #:id', ['id' => $referralEarning->id]);
                $referralStatement->amount = $referralEarningAmount;
                $referralStatement->total = $referralEarningAmount;
                $referralStatement->type = Statement::TYPE_CREDIT;
                $referralStatement->save();
            }
        }

        $item->increment('total_sales');
        $item->increment('total_sales_amount', $sale->price);
        $item->increment('total_earnings', $authorEarning);

        dispatch(new SendPurchaseConfirmationNotification($purchase));
    }

    private function generatePurchaseCode($length = 37)
    {
        $randomString = Str::random($length);
        $uniqueString = $randomString . microtime();
        $hashedString = hash('sha256', $uniqueString);
        $formattedCode = substr($hashedString, 0, $length);

        return $formattedCode;
    }
}