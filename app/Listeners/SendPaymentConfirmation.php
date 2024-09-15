<?php

namespace App\Listeners;

use App\Events\PaymentProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentConfirmation;

class SendPaymentConfirmation implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentProcessed $event)
    {
        $order = $event->order;

        // Aquí puedes enviar el correo electrónico
        Mail::to($order->user->email)->send(new PaymentConfirmation($order));
    }
}
