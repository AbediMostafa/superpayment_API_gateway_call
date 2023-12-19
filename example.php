<?php
require 'Payment.php';

$payment = new Payment( 1000, 101,    'PWH_vZA1rFM6qJxxgqTP3mpfho0N3EXOszn16sZyKLEG');

// add items to the cart
$payment->addCartItems('book01', 'www.book01_details.com', 4)
    ->addCartItems('book02', 'www.book02_details.com', 13);

// Manual redirection
$result = $payment->send(false);
