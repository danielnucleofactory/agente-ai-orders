<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\PorthTranslationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PorthTranslationServiceTest extends TestCase
{
    public function test_translate_port_falls_back_to_maestros_with_port_suffix_variation(): void
    {
        Cache::flush();

        Http::fake([
            '*' => Http::response([
                'data' => [
                    ['name' => 'Shenzen, China'],
                ],
            ], 200),
        ]);

        $service = app(PorthTranslationService::class);
        $service->clearCache();

        $translated = $service->translatePort(null, 'Shenzen port, China');

        $this->assertSame('Shenzen, China', $translated);
    }
}
