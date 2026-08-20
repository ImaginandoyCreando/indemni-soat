<?php

namespace Tests\Unit;

use App\Services\WhatsappService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsappServiceTest extends TestCase
{
    public function test_it_sends_a_colombian_local_number_in_international_format(): void
    {
        config([
            'whatsapp.instance_id' => 'instance-test',
            'whatsapp.token' => 'token-test',
            'whatsapp.base_url' => 'https://api.ultramsg.test',
        ]);

        Http::fake([
            'https://api.ultramsg.test/*' => Http::response(['sent' => 'true'], 200),
        ]);

        $enviado = (new WhatsappService())->enviar('300 123 4567', 'Mensaje de prueba');

        $this->assertTrue($enviado);
        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.ultramsg.test/instance-test/messages/chat'
                && $request['token'] === 'token-test'
                && $request['to'] === '+573001234567'
                && $request['body'] === 'Mensaje de prueba';
        });
    }

    public function test_it_accepts_a_boolean_success_response_from_ultramsg(): void
    {
        config([
            'whatsapp.instance_id' => 'instance-test',
            'whatsapp.token' => 'token-test',
            'whatsapp.base_url' => 'https://api.ultramsg.test',
        ]);

        Http::fake([
            'https://api.ultramsg.test/*' => Http::response(['sent' => true], 200),
        ]);

        $this->assertTrue((new WhatsappService())->enviar('+57 300 123 4567', 'Mensaje de prueba'));
    }

    public function test_it_rejects_an_invalid_destination_without_calling_ultramsg(): void
    {
        config([
            'whatsapp.instance_id' => 'instance-test',
            'whatsapp.token' => 'token-test',
            'whatsapp.base_url' => 'https://api.ultramsg.test',
        ]);

        Http::fake();

        $this->assertFalse((new WhatsappService())->enviar('123', 'Mensaje de prueba'));
        Http::assertNothingSent();
    }
}
