<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Process payment for a booking
     */
    public function processPayment(Booking $booking, string $paymentMethod, array $paymentDetails = []): array
    {
        try {
            // Simulate payment gateway processing
            $paymentResult = $this->simulatePaymentGateway($booking, $paymentMethod, $paymentDetails);

            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'payment_method' => $paymentMethod,
                'status' => $paymentResult['status'],
                'payment_details' => array_merge($paymentDetails, $paymentResult['details']),
            ]);

            // If payment successful, confirm booking
            if ($paymentResult['status'] === 'completed') {
                $booking->update(['status' => 'confirmed']);

                // Decrease ticket quantity
                $booking->ticket->decreaseAvailableQuantity($booking->quantity);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'payment' => $payment,
                    'booking' => $booking->fresh(['ticket', 'event']),
                ];
            }

            // Payment failed
            $booking->update(['status' => 'cancelled']);
            DB::commit();

            return [
                'success' => false,
                'message' => 'Payment failed',
                'payment' => $payment,
                'booking' => $booking,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Simulate payment gateway
     */
    private function simulatePaymentGateway(Booking $booking, string $method, array $details): array
    {
        // Simulate processing delay
        usleep(500000); // 0.5 seconds

        // Simulate random success/failure (90% success rate)
        $isSuccessful = rand(1, 100) <= 90;

        if ($isSuccessful) {
            return [
                'status' => 'completed',
                'details' => [
                    'transaction_id' => 'TXN_' . uniqid(),
                    'authorization_code' => 'AUTH_' . rand(100000, 999999),
                    'processed_at' => now()->toDateTimeString(),
                    'simulated' => true,
                ],
            ];
        }

        return [
            'status' => 'failed',
            'details' => [
                'error_code' => 'DECLINED_' . rand(100, 999),
                'error_message' => 'Payment declined by bank',
                'simulated' => true,
            ],
        ];
    }

    /**
     * Refund payment
     */
    public function refundPayment(Payment $payment): array
    {
        try {
            if ($payment->status !== 'completed') {
                return [
                    'success' => false,
                    'message' => 'Only completed payments can be refunded',
                ];
            }

            DB::beginTransaction();

            // Simulate refund processing
            $refundResult = $this->simulateRefund($payment);

            if ($refundResult['success']) {
                $payment->update([
                    'status' => 'refunded',
                    'payment_details' => array_merge($payment->payment_details ?? [], [
                        'refunded_at' => now()->toDateTimeString(),
                        'refund_reference' => 'REF_' . uniqid(),
                    ])
                ]);

                // Cancel the associated booking
                $payment->booking->update(['status' => 'cancelled']);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Payment refunded successfully',
                    'payment' => $payment->fresh(),
                ];
            }

            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Refund failed',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Refund processing error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Simulate refund
     */
    private function simulateRefund(Payment $payment): array
    {
        usleep(300000); // 0.3 seconds

        return [
            'success' => true,
            'details' => [
                'refund_transaction_id' => 'REF_' . uniqid(),
            ],
        ];
    }
}
