<?php

namespace App\Models;

use App\Models\BuyerTax;
use App\Models\PaymentGateway;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    const STATUS_UNPAID = 0;
    const STATUS_PENDING = 1;
    const STATUS_PAID = 2;
    const STATUS_CANCELLED = 3;

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($transaction) {
            if ($transaction->payment_proof) {
                removeFileFromStorage($transaction->payment_proof, 'local');
            }
        });
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', self::STATUS_UNPAID);
    }

    public function isUnpaid()
    {
        return $this->status == self::STATUS_UNPAID;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isPending()
    {
        return $this->status == self::STATUS_PENDING;
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function isPaid()
    {
        return $this->status == self::STATUS_PAID;
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function isCancelled()
    {
        return $this->status == self::STATUS_CANCELLED;
    }

    public function hasFees()
    {
        return $this->fees > 0;
    }

    public function hasTax()
    {
        return $this->tax != null;
    }

    protected $fillable = [
        'user_id',
        'amount',
        'tax',
        'fees',
        'total',
        'payment_id',
        'payer_id',
        'payer_email',
        'payment_proof',
        'status',
        'cancellation_reason',
    ];

    protected $with = [
        'trxItems',
    ];

    protected $casts = [
        'tax' => 'object',
    ];

    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING => translate('Pending'),
            self::STATUS_PAID => translate('Paid'),
            self::STATUS_CANCELLED => translate('Cancelled'),
        ];
    }

    public function getStatusName()
    {
        return self::getStatusOptions()[$this->status];
    }

    public static function prepareForCheckout($amount, array $items)
    {
        $user = authUser();

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $amount;
        $transaction->total = $amount;
        $transaction->save();

        foreach ($items as $item) {
            $transactionItem = new TransactionItem();
            $transactionItem->transaction_id = $transaction->id;
            $transactionItem->item_id = $item['id'];
            $transactionItem->license_type = $item['license_type'];
            $transactionItem->price = $item['price'];
            $transactionItem->quantity = isset($item['quantity']) ? $item['quantity'] : 1;
            $transactionItem->total = $item['total'];
            $transactionItem->save();
        }

        return $transaction;
    }

    public function calculate()
    {
        $subTotal = $this->amount;

        $tax = null;

        $user = $this->user;

        $buyerTax = BuyerTax::whereJsonContains('countries', @$user->address->country)->first();
        if ($buyerTax) {
            $taxRate = $buyerTax->rate;
            $taxAmount = ($subTotal * $taxRate) / 100;

            $tax = [
                'name' => $buyerTax->name,
                'rate' => $taxRate,
                'amount' => round($taxAmount, 2),
            ];

            $subTotal = ($subTotal + $taxAmount);
        }

        $paymentGateway = $this->paymentGateway;

        $fees = 0;
        if ($paymentGateway->fees > 0) {
            $fees = ($subTotal * $paymentGateway->fees) / 100;
        }

        $total = ($subTotal + $fees);

        $this->tax = $tax;
        $this->fees = $fees;
        $this->total = $total;
        $this->update();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function trxItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
