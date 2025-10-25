<?php

namespace App\Controller;

use App\services\ServicesCSV;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    private ServicesCSV $csvService;

    public function __construct(ServicesCSV $csvService)
    {
        $this->csvService = $csvService;
    }

    #[Route('/free-houses', methods: ['GET'])]
    public function getFreeHouses(): JsonResponse
    {
        try {
            $houses = $this->csvService->readCSV('houses.csv');
            $bookings = $this->csvService->readCSV('bookings.csv');

            $bookedHouseIds = array_column($bookings, 'house_id');
            
            $freeHouses = array_filter($houses, function($house) use ($bookedHouseIds) {
                return !in_array($house['id'], $bookedHouseIds);
            });

            return $this->json([
                'success' => true,
                'data' => array_values($freeHouses)
            ]);
            
        } catch (\RuntimeException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    #[Route('/booking', methods: ['POST'])]
    public function createBooking(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['phone']) || !isset($data['house_id'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Необходимы phone и house_id'
                ], 400);
            }

            $bookingData = [
                uniqid(),
                $data['phone'],
                $data['house_id'],
                $data['comment'] ?? '',
                date('Y-m-d H:i:s'),
                'active'  
            ];

            $this->csvService->writeCSV('bookings.csv', $bookingData);

            return $this->json([
                'success' => true,
                'message' => 'Бронирование создано',
                'id' => $bookingData[0]
            ]);
            
        } catch (\RuntimeException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/booking/{id}', methods: ['PUT'])]
    public function updateBooking(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['comment'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Необходим comment для обновления'
                ], 400);
            }

            $this->csvService->updateCSV('bookings.csv', $id, [
                'comment' => $data['comment']
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Бронирование обновлено'
            ]);
            
        } catch (\RuntimeException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}