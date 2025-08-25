<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TrackingService
{
    protected $ship24ApiKey;
    protected $porthApiKey;

    public function __construct()
    {
        $this->ship24ApiKey = config('services.ship24.api_key');
        $this->porthApiKey = config('services.porth.api_key');
    }

    public function getShip24Tracking($trackingNumber)
    {
        try {
            // Using the correct API endpoint for Ship24
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->ship24ApiKey,
                'Accept' => 'application/json'
            ])->post("https://api.ship24.com/public/v1/tracking/search", [
                'trackingNumber' => $trackingNumber
            ]);

            if ($response->failed()) {
                \Log::error('Ship24 API request failed:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();

            if (!$data || !isset($data['data']['trackings'][0])) {
                \Log::warning('Invalid or empty response from Ship24 API');
                return null;
            }

            // Simplemente devuelve los datos tal como vienen de la API
            $tracking = $data['data']['trackings'][0];

            return [
                'raw_data' => $tracking,
                'current_phase' => $tracking['shipment']['statusMilestone'] ?? null,
                'estimated_delivery' => $tracking['shipment']['delivery']['estimatedDeliveryDate'] ?? null,
            ];

        } catch (\Exception $e) {
            \Log::error('Error in getShip24Tracking:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function determinePhaseStatusForShip24($phaseKey, $currentMilestone, $date)
    {
        if ($date) {
            return 'completed';
        }

        if ($phaseKey === $currentMilestone) {
            return 'active';
        }

        // Get the position of the phases for comparison
        $phaseOrder = [
            'info_received' => 1,
            'in_transit' => 2,
            'out_for_delivery' => 3,
            'failed_attempt' => 4,
            'available_for_pickup' => 5,
            'delivered' => 6
        ];

        $phaseNumber = $phaseOrder[$phaseKey] ?? 999;
        $currentNumber = $phaseOrder[$currentMilestone] ?? 0;

        return $phaseNumber < $currentNumber ? 'completed' : 'pending';
    }

    public function getPorthTracking($trackingNumber)
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->porthApiKey,
                'Accept' => 'application/json'
            ])->get("https://porth-api.fly.dev/api/shipment/byId/{$trackingNumber}");

            if ($response->failed()) {
                \Log::error('API request failed:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();

            if (!$data || !isset($data['phases'])) {
                \Log::warning('Invalid or empty response from Porth API');
                return null;
            }

            return $this->processPorthTrackingData($data);

        } catch (\Exception $e) {
            \Log::error('Error in getPorthTracking:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function getPorthTrackingByMasterBl($masterBl)
    {
        try {
            \Log::info('Fetching tracking data by MasterBl', ['masterBl' => $masterBl]);

            $response = Http::withHeaders([
                'apikey' => $this->porthApiKey,
                'Accept' => 'application/json'
            ])->get("https://porth-api.fly.dev/api/shipment/byMasterBl/{$masterBl}");

            if ($response->failed()) {
                \Log::error('MasterBl API request failed:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();

            if (!$data || !isset($data['phases'])) {
                \Log::warning('Invalid or empty response from Porth API for MasterBl', [
                    'masterBl' => $masterBl
                ]);
                return null;
            }

            return $this->processPorthTrackingData($data);

        } catch (\Exception $e) {
            \Log::error('Error in getPorthTrackingByMasterBl:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'masterBl' => $masterBl
            ]);
            return null;
        }
    }

    private function processPorthTrackingData($data)
    {
        // Definir el orden y nombres de las fases
        $phaseOrder = [
            '10_ready' => [
                'name' => 'Ready for pickup',
                'icon' => 'warehouse'
            ],
            '20_to_origin_port' => [
                'name' => 'In transit origin port',
                'icon' => 'truck'
            ],
            '30_at_origin_port' => [
                'name' => 'At origin port',
                'icon' => 'port'
            ],
            '40_in_transit' => [
                'name' => 'In transit to dest. port',
                'icon' => 'ship'
            ],
            '50_at_destination_port' => [
                'name' => 'At destination port',
                'icon' => 'port'
            ],
            '60_to_final_destination' => [
                'name' => 'In transit to final dest.',
                'icon' => 'truck'
            ],
            '70_delivered' => [
                'name' => 'Delivered',
                'icon' => 'check'
            ]
        ];

        // Procesar las fases
        $timeline = [];
        $currentPhase = $data['phase'];

        foreach ($phaseOrder as $phaseKey => $phaseInfo) {
            $phase = collect($data['phases'])->firstWhere('name', $phaseKey);

            $status = $this->determinePhaseStatus($phaseKey, $currentPhase, $phase);

            $timeline[] = [
                'id' => $phase['id'] ?? null,
                'name' => $phaseInfo['name'],
                'icon' => $phaseInfo['icon'],
                'status' => $status,
                'date' => $phase['actualDate'] ?? ($phase['estimatedDates'][0] ?? null),
                'is_current' => $phaseKey === $currentPhase,
                'is_completed' => $this->isPhaseCompleted($phaseKey, $currentPhase, $phase)
            ];
        }

        return [
            'timeline' => $timeline,
            'current_phase' => $currentPhase,
            'estimated_delivery' => $data['eta'] ?? null,
            'cargo' => $data['cargo'] ?? [],
            'pol' => $data['polName'] ?? '',
            'pod' => $data['podName'] ?? '',
            'carrier' => $data['carrierCode'] ?? ''
        ];
    }

    private function determinePhaseStatus($phaseKey, $currentPhase, $phase)
    {
        if ($phase['actualDate']) {
            return 'completed';
        }

        if ($phaseKey === $currentPhase) {
            return 'active';
        }

        $phaseNumber = (int) substr($phaseKey, 0, 2);
        $currentNumber = (int) substr($currentPhase, 0, 2);

        return $phaseNumber < $currentNumber ? 'completed' : 'pending';
    }

    private function isPhaseCompleted($phaseKey, $currentPhase, $phase)
    {
        if ($phase['actualDate']) {
            return true;
        }

        $phaseNumber = (int) substr($phaseKey, 0, 2);
        $currentNumber = (int) substr($currentPhase, 0, 2);

        return $phaseNumber < $currentNumber;
    }

    public function getMockTrackingData()
    {
        return [
            'timeline' => [
                [
                    'name' => 'Ready for pickup',
                    'icon' => 'warehouse',
                    'status' => 'completed',
                    'date' => '2025-01-22T10:00:00.000Z',
                    'is_current' => false,
                    'is_completed' => true
                ],
                [
                    'name' => 'In transit origin port',
                    'icon' => 'truck',
                    'status' => 'completed',
                    'date' => '2025-01-22T11:00:00.000Z',
                    'is_current' => false,
                    'is_completed' => true
                ],
                [
                    'name' => 'At origin port',
                    'icon' => 'port',
                    'status' => 'completed',
                    'date' => '2025-01-23T20:32:00.000Z',
                    'is_current' => false,
                    'is_completed' => true
                ],
                [
                    'name' => 'In transit to dest. port',
                    'icon' => 'ship',
                    'status' => 'active',
                    'date' => null,
                    'is_current' => true,
                    'is_completed' => false
                ],
                [
                    'name' => 'At destination port',
                    'icon' => 'port',
                    'status' => 'pending',
                    'date' => '2025-04-01T23:00:00.000Z',
                    'is_current' => false,
                    'is_completed' => false
                ],
                [
                    'name' => 'In transit to final dest.',
                    'icon' => 'truck',
                    'status' => 'pending',
                    'date' => null,
                    'is_current' => false,
                    'is_completed' => false
                ],
                [
                    'name' => 'Delivered',
                    'icon' => 'check',
                    'status' => 'pending',
                    'date' => null,
                    'is_current' => false,
                    'is_completed' => false
                ]
            ],
            'current_phase' => '40_in_transit',
            'estimated_delivery' => '2025-04-01T23:00:00.000Z',
            'cargo' => [
                [
                    'type' => '40\' Dry',
                    'number' => 'HASU4258561'
                ]
            ],
            'pol' => 'Sanshui',
            'pod' => 'Puerto Caldera',
            'carrier' => 'MAEU'
        ];
    }

    public function getPorthTrackingByContainerNumber($containerNumber)
    {
        try {
            \Log::info('Fetching tracking data by container number', ['containerNumber' => $containerNumber]);

            $response = Http::withHeaders([
                'apikey' => $this->porthApiKey,
                'Accept' => 'application/json'
            ])->get("https://porth-api.fly.dev/api/shipment/byContainer/{$containerNumber}");

            if ($response->failed()) {
                \Log::error('Container number API request failed:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();

            if (!$data || !isset($data['phases'])) {
                \Log::warning('Invalid or empty response from Porth API for container number', [
                    'containerNumber' => $containerNumber
                ]);
                return null;
            }

            return $this->processPorthTrackingData($data);

        } catch (\Exception $e) {
            \Log::error('Error in getPorthTrackingByContainerNumber:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'containerNumber' => $containerNumber
            ]);
            return null;
        }
    }

    public function getTracking($trackingId = null, $mblNumber = null)
    {
        \Log::info('TrackingService::getTracking called:', [
            'tracking_id' => $trackingId,
            'mbl_number' => $mblNumber
        ]);

        // Si no hay tracking_id ni mbl_number, devolver datos de prueba
        if (!$trackingId && !$mblNumber) {
            \Log::info('No tracking ID or MBL provided, returning mock data');
            return $this->getMockTrackingData();
        }

        $trackingData = null;

        // Primero intentamos con el tracking ID si está disponible
        if ($trackingId) {
            \Log::info('Attempting to get Porth tracking data using ID', ['id' => $trackingId]);
            $trackingData = $this->getPorthTracking($trackingId);
        }

        // Si no tenemos datos por tracking ID o no se proporcionó, intentamos con MBL
        if (!$trackingData && $mblNumber) {
            \Log::info('Attempting to get Porth tracking data using Master BL', ['mbl' => $mblNumber]);
            $trackingData = $this->getPorthTrackingByMasterBl($mblNumber);
        }

        // Si ambos métodos fallan, devolver datos de prueba como fallback
        if (!$trackingData) {
            \Log::info('All API calls failed, returning mock data');
            return $this->getMockTrackingData();
        }

        // Asegurarnos de que siempre exista la clave 'timeline'
        if (!isset($trackingData['timeline'])) {
            $trackingData['timeline'] = [];
        }


        \Log::info('Successfully retrieved Porth tracking data');
        return $trackingData;
    }
}
