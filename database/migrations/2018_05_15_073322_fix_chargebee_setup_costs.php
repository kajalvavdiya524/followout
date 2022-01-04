<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixChargebeeSetupCosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $setupFeeProduct = \App\Product::subscriptionSetupFee()->first();

        if (is_null($setupFeeProduct)) {
            $setupFeeProduct = new \App\Product([
                'name' => 'Followouts Pro Setup Fee',
                'action_name' => null,
                'description' => 'Pro account subscription setup fee.',
                'price' => 49.98,
                'type' => 'subscription_setup_fee',
            ]);
            $setupFeeProduct->save();
        }

        // We'll add missing setup fees for subscriptions to our Payment models
        $setupFeeProduct = \App\Product::subscriptionSetupFee()->first();
        $payments = \App\Payment::viaChargebee()->orderBy('created_at')->get();

        foreach ($payments as $payment) {
            $user = $payment->user;

            $invoices = \PaymentHelper::getChargebeeInvoicesForCustomer($user->id);

            foreach ($invoices as $invoice) {
                foreach ($invoice['line_items'] as $lineItem) {
                    if ($lineItem['entity_type'] === 'plan_setup') {
                        $products = $payment->products;

                        $item = [
                            'name' => $setupFeeProduct->name,
                            'description' => $setupFeeProduct->description,
                            'type' => $setupFeeProduct->type,
                            'price' => (float) number_format($lineItem['amount'] / 100, 2),
                        ];

                        // Add setup fee product
                        $products[] = $item;

                        $payment->products = $products;

                        // Update total amount
                        $payment->amount = number_format((float) $payment->amount + (float) $item['price'], 2);

                        $payment->save();
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
