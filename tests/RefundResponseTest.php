<?php

namespace DigiTickets\StripeTests;

use DigiTickets\Stripe\Messages\RefundRequest;
use DigiTickets\Stripe\Messages\RefundResponse;
use Mockery;
use Omnipay\Tests\TestCase;
use Stripe\Refund;

class RefundResponseTest extends TestCase
{
    const REQUEST_REF = 'refund-request-reference';
    const EXCEPTION_MESSAGE = 'Emulating an exception';
    const REFUND_ID_SUCCESS = 'RefSuccess';
    const REFUND_ID_FAILURE = 'RefFail';
    const STATUS_FAILED = 'failed';

    public function creationProvider()
    {
        $request = Mockery::mock(RefundRequest::class);
        $request->shouldReceive('getTransactionReference')->andReturn(self::REQUEST_REF);

        $successfulRefund = new Refund(self::REFUND_ID_SUCCESS);
        $successfulRefund->status = Refund::STATUS_SUCCEEDED;
        $failedRefund = new Refund(self::REFUND_ID_SUCCESS);
        $failedRefund->status = self::STATUS_FAILED;

        return [
            'nothing' => [
                $request,
                null,
                false,
                'Unexpected refund data received',
                null
            ],
            'exception' => [
                $request,
                ['refund' => new \Exception(self::EXCEPTION_MESSAGE)],
                false,
                self::EXCEPTION_MESSAGE,
                null
            ],
            'successful refund' => [
                $request,
                ['refund' => $successfulRefund],
                true,
                Refund::STATUS_SUCCEEDED,
                'What should this be?'
            ],
            'failed refund' => [
                $request,
                ['refund' => $failedRefund],
                false,
                self::STATUS_FAILED,
                null
            ],
        ];
    }

    /**
     * @param RefundRequest $request
     * @param $data
     *
     * @dataProvider creationProvider
     */
    public function testCreation(
        RefundRequest $request,
        $data,
        bool $expectedSuccess,
        string $expectedMessage/*,
        string $expectedReference*/
    ) {
        $refundResponse = new RefundResponse($request, $data);

        $this->assertEquals($expectedSuccess, $refundResponse->isSuccessful());
        $this->assertEquals($expectedMessage, $refundResponse->getMessage());
        $this->assertEquals('What is $expectedReference?', $refundResponse->getTransactionReference());
    }
}
