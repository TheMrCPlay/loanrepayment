<?php

namespace App\Controller;

use App\Application\Dto\IncomingPaymentDto;
use App\Application\Service\PaymentIngestionService;
use App\Domain\Exception\DuplicatePaymentException;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'api/payment')]
final class PaymentController extends AbstractController
{
    /**
     * # 1
     *
     * {
     * "firstname": "Lorem",
     * "lastname": "Ipsum",
     * "paymentDate": "2022-12-12T15:19:21+00:00",
     * "amount": "99.99",
     * "description": "Lorem ipsum dolorLN20221212 sit amet...",
     * "refId": "dda8b637-b2e8-4f79-a4af-d1d68e266bf5"
     * }
     *
     * # 2
     *
     * {
     * "firstname": "Lorem",
     * "lastname": "Ipsum",
     * "paymentDate": "2022-12-12T15:19:21+00:00",
     * "amount": "99.99",
     * "description": "LN20221212",
     * "refId": "130f8a89-51c9-47d0-a6ef-1aea54924d3b"
     * }
     */
    #[Route('/', name: 'single_payment', methods: ['POST'])]
    public function index(
        Request $request,
        PaymentIngestionService $paymentIngestionService,
        LoggerInterface $logger,
    ): JsonResponse {
        $body = new Collection(json_decode($request->getContent(), true));

        try {
            $paymentIngestionService->process(new IncomingPaymentDto(
                $body->get('firstname'),
                $body->get('lastname'),
                $body->get('paymentDate'),
                $body->get('amount'),
                $body->get('description'),
                $body->get('refId')
            ));
        } catch (DuplicatePaymentException $exception) {
            return $this->json([
                'message' => $exception->getMessage()
            ], 409);
        } catch (\DomainException $exception) {
            return $this->json([
                'message' => $exception->getMessage()
            ], 400);
        } catch (\Exception $exception) {
            $logger->error('Payment processing failed', [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return $this->json([
                'message' => 'An unexpected error occurred.'
            ], 500);
        }

        return $this->json([
            'message' => 'Payment received successfully.'
        ], 201);
    }
}
