<?php

namespace App\Livewire;

use App\Classes\Country;
use App\Models\BuyerTax;
use App\Models\PaymentGateway;
use App\Traits\LivewireToastr;
use Livewire\Component;

class Checkout extends Component
{
    use LivewireToastr;

    public $user;
    public $trx;
    public $summary = [];
    public $payment_method;

    public $address_line_1;
    public $address_line_2;
    public $city;
    public $state;
    public $zip;
    public $country;

    public function mount()
    {
        $this->user = authUser();
        $this->payment_method = old('payment_method') ?? PaymentGateway::active()->first()->alias;

        $user = $this->user;
        $this->address_line_1 = old('address_line_1') ?? @$user->address->line_1;
        $this->address_line_2 = old('address_line_2') ?? @$user->address->line_2;
        $this->city = old('city') ?? @$user->address->city;
        $this->state = old('state') ?? @$user->address->state;
        $this->zip = old('zip') ?? @$user->address->zip;
        $this->country = old('country') ?? @$user->address->country;
    }

    public function updateSummary()
    {
        $total = $this->trx->amount;

        $tax = null;
        if ($this->country) {
            $buyerTax = BuyerTax::whereJsonContains('countries', $this->country)->first();

            if ($buyerTax) {
                $taxRate = $buyerTax->rate;
                $taxAmount = ($total * $taxRate) / 100;

                $tax = [
                    'name' => $buyerTax->name,
                    'rate' => $taxRate,
                    'amount' => getAmount(round($taxAmount, 2)),
                ];

                $total = round($total + $taxAmount, 2);
            }
        }

        $gateway = null;
        if ($this->payment_method) {

            $paymentGateway = PaymentGateway::where('alias', $this->payment_method)
                ->active()->first();

            if ($paymentGateway && !$paymentGateway->isAccountBalance()) {
                $gatewayFees = $paymentGateway->fees;

                if ($gatewayFees > 0) {
                    $feesAmount = ($total * $gatewayFees) / 100;

                    $gateway = [
                        'name' => $paymentGateway->name,
                        'fees' => $gatewayFees,
                        'amount' => getAmount(round($feesAmount, 2)),
                    ];

                    $total = round($total + $feesAmount, 2);
                }
            }
        }

        $this->summary = [
            'tax' => $tax,
            'gateway' => $gateway,
            'total' => $total,
        ];
    }

    public function render()
    {
        $this->updateSummary();

        $paymentGateways = PaymentGateway::active()->get();
        return theme_view('livewire.checkout', [
            'paymentGateways' => $paymentGateways,
        ]);
    }
}
