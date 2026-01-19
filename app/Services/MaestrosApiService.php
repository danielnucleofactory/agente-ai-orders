<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MaestrosApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.maestros.base_url');
    }

    /**
     * Make a GET request to the maestros API
     *
     * @param string $endpoint
     * @param array $params
     * @return array|null
     */
    protected function get(string $endpoint, array $params = [])
    {
        try {
            $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

            // Log parameters for debugging
            \Log::info('MaestrosApiService: Sending request', [
                'endpoint' => $endpoint,
                'params' => $params,
            ]);

            $response = Http::timeout(30)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('MaestrosApiService: API request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('MaestrosApiService: Exception occurred', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get container types
     *
     * @param array $params
     * @return array|null
     */
    public function getContainerTypes(array $params = [])
    {
        $defaultParams = [
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 20,
            'sort' => $params['sort'] ?? 'created_at',
            'order' => $params['order'] ?? 'desc',
        ];

        // Only add search if it's not empty - send original text (backend handles case-insensitive search)
        if (!empty($params['search'])) {
            $defaultParams['search'] = trim($params['search']);
        }

        // Only add active if it's provided - ensure it's always a string 'true' or 'false'
        if (isset($params['active']) && $params['active'] !== '') {
            // Normalize to string 'true' or 'false'
            if ($params['active'] === true || $params['active'] === 'true' || $params['active'] === 1 || $params['active'] === '1') {
                $defaultParams['active'] = 'true';
            } elseif ($params['active'] === false || $params['active'] === 'false' || $params['active'] === 0 || $params['active'] === '0') {
                $defaultParams['active'] = 'false';
            } else {
                $defaultParams['active'] = (string) $params['active'];
            }
        }

        // Only add company if it's not empty
        if (!empty($params['company'])) {
            $defaultParams['company'] = $params['company'];
        }

        return $this->get('/api/v1/container-types', $defaultParams);
    }

    /**
     * Get ports
     *
     * @param array $params
     * @return array|null
     */
    public function getPorts(array $params = [])
    {
        $defaultParams = [
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 20,
            'sort' => $params['sort'] ?? 'created_at',
            'order' => $params['order'] ?? 'desc',
        ];

        // Only add search if it's not empty - send original text (backend handles case-insensitive search)
        if (!empty($params['search'])) {
            $defaultParams['search'] = trim($params['search']);
        }

        // Only add active if it's provided - ensure it's always a string 'true' or 'false'
        if (isset($params['active']) && $params['active'] !== '') {
            // Normalize to string 'true' or 'false'
            if ($params['active'] === true || $params['active'] === 'true' || $params['active'] === 1 || $params['active'] === '1') {
                $defaultParams['active'] = 'true';
            } elseif ($params['active'] === false || $params['active'] === 'false' || $params['active'] === 0 || $params['active'] === '0') {
                $defaultParams['active'] = 'false';
            } else {
                $defaultParams['active'] = (string) $params['active'];
            }
        }

        // Only add company if it's not empty
        if (!empty($params['company'])) {
            $defaultParams['company'] = $params['company'];
        }

        return $this->get('/api/v1/ports', $defaultParams);
    }

    /**
     * Get transport types
     *
     * @param array $params
     * @return array|null
     */
    public function getTransportTypes(array $params = [])
    {
        $defaultParams = [
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 20,
            'sort' => $params['sort'] ?? 'created_at',
            'order' => $params['order'] ?? 'desc',
        ];

        // Only add search if it's not empty - send original text (backend handles case-insensitive search)
        if (!empty($params['search'])) {
            $defaultParams['search'] = trim($params['search']);
        }

        // Only add active if it's provided - ensure it's always a string 'true' or 'false'
        if (isset($params['active']) && $params['active'] !== '') {
            // Normalize to string 'true' or 'false'
            if ($params['active'] === true || $params['active'] === 'true' || $params['active'] === 1 || $params['active'] === '1') {
                $defaultParams['active'] = 'true';
            } elseif ($params['active'] === false || $params['active'] === 'false' || $params['active'] === 0 || $params['active'] === '0') {
                $defaultParams['active'] = 'false';
            } else {
                $defaultParams['active'] = (string) $params['active'];
            }
        }

        // Only add company if it's not empty
        if (!empty($params['company'])) {
            $defaultParams['company'] = $params['company'];
        }

        return $this->get('/api/v1/transport-types', $defaultParams);
    }

    /**
     * Get shipping lines
     *
     * @param array $params
     * @return array|null
     */
    public function getShippingLines(array $params = [])
    {
        $defaultParams = [
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 20,
            'sort' => $params['sort'] ?? 'created_at',
            'order' => $params['order'] ?? 'desc',
        ];

        // Only add search if it's not empty - send original text (backend handles case-insensitive search)
        if (!empty($params['search'])) {
            $defaultParams['search'] = trim($params['search']);
        }

        // Only add active if it's provided - ensure it's always a string 'true' or 'false'
        if (isset($params['active']) && $params['active'] !== '') {
            // Normalize to string 'true' or 'false'
            if ($params['active'] === true || $params['active'] === 'true' || $params['active'] === 1 || $params['active'] === '1') {
                $defaultParams['active'] = 'true';
            } elseif ($params['active'] === false || $params['active'] === 'false' || $params['active'] === 0 || $params['active'] === '0') {
                $defaultParams['active'] = 'false';
            } else {
                $defaultParams['active'] = (string) $params['active'];
            }
        }

        // Only add company if it's not empty
        if (!empty($params['company'])) {
            $defaultParams['company'] = $params['company'];
        }

        return $this->get('/api/v1/shipping-lines', $defaultParams);
    }

    /**
     * Get service providers
     *
     * @param array $params
     * @return array|null
     */
    public function getServiceProviders(array $params = [])
    {
        $defaultParams = [
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 20,
            'sort' => $params['sort'] ?? 'created_at',
            'order' => $params['order'] ?? 'desc',
        ];

        // Only add search if it's not empty - send original text (backend handles case-insensitive search)
        if (!empty($params['search'])) {
            $defaultParams['search'] = trim($params['search']);
        }

        // Only add active if it's provided - ensure it's always a string 'true' or 'false'
        if (isset($params['active']) && $params['active'] !== '') {
            // Normalize to string 'true' or 'false'
            if ($params['active'] === true || $params['active'] === 'true' || $params['active'] === 1 || $params['active'] === '1') {
                $defaultParams['active'] = 'true';
            } elseif ($params['active'] === false || $params['active'] === 'false' || $params['active'] === 0 || $params['active'] === '0') {
                $defaultParams['active'] = 'false';
            } else {
                $defaultParams['active'] = (string) $params['active'];
            }
        }

        // Only add company if it's not empty
        if (!empty($params['company'])) {
            $defaultParams['company'] = $params['company'];
        }

        return $this->get('/api/v1/service-providers', $defaultParams);
    }

    /**
     * Get rate types
     *
     * @param array $params
     * @return array|null
     */
    public function getRateTypes(array $params = [])
    {
        $defaultParams = [
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 20,
            'sort' => $params['sort'] ?? 'created_at',
            'order' => $params['order'] ?? 'desc',
        ];

        // Only add search if it's not empty - send original text (backend handles case-insensitive search)
        if (!empty($params['search'])) {
            $defaultParams['search'] = trim($params['search']);
        }

        // Only add active if it's provided - ensure it's always a string 'true' or 'false'
        if (isset($params['active']) && $params['active'] !== '') {
            // Normalize to string 'true' or 'false'
            if ($params['active'] === true || $params['active'] === 'true' || $params['active'] === 1 || $params['active'] === '1') {
                $defaultParams['active'] = 'true';
            } elseif ($params['active'] === false || $params['active'] === 'false' || $params['active'] === 0 || $params['active'] === '0') {
                $defaultParams['active'] = 'false';
            } else {
                $defaultParams['active'] = (string) $params['active'];
            }
        }

        // Only add company if it's not empty
        if (!empty($params['company'])) {
            $defaultParams['company'] = $params['company'];
        }

        return $this->get('/api/v1/rate-types', $defaultParams);
    }

    /**
     * Make a POST request to the maestros API
     *
     * @param string $endpoint
     * @param array $data
     * @return array|null
     */
    protected function post(string $endpoint, array $data = [])
    {
        try {
            $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

            \Log::info('MaestrosApiService: Sending POST request', [
                'endpoint' => $endpoint,
                'data' => $data,
            ]);

            $response = Http::timeout(30)->post($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('MaestrosApiService: POST request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('MaestrosApiService: Exception occurred in POST', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a port
     *
     * @param array $data
     * @return array|null
     */
    public function createPort(array $data)
    {
        return $this->post('/api/v1/ports', $data);
    }

    /**
     * Create a shipping line
     *
     * @param array $data
     * @return array|null
     */
    public function createShippingLine(array $data)
    {
        return $this->post('/api/v1/shipping-lines', $data);
    }

    /**
     * Create a container type
     *
     * @param array $data
     * @return array|null
     */
    public function createContainerType(array $data)
    {
        return $this->post('/api/v1/container-types', $data);
    }

    /**
     * Create a service provider
     *
     * @param array $data
     * @return array|null
     */
    public function createServiceProvider(array $data)
    {
        return $this->post('/api/v1/service-providers', $data);
    }

    /**
     * Create a transport type
     *
     * @param array $data
     * @return array|null
     */
    public function createTransportType(array $data)
    {
        return $this->post('/api/v1/transport-types', $data);
    }

    /**
     * Create a rate type
     *
     * @param array $data
     * @return array|null
     */
    public function createRateType(array $data)
    {
        return $this->post('/api/v1/rate-types', $data);
    }
}

