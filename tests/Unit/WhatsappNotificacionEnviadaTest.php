<?php

namespace Tests\Unit;

use App\Models\WhatsappNotificacionEnviada;
use Tests\TestCase;

class WhatsappNotificacionEnviadaTest extends TestCase
{
    public function test_it_uses_the_database_timestamp_column_enviado_en(): void
    {
        $notificacion = new WhatsappNotificacionEnviada();

        $this->assertTrue($notificacion->isFillable('enviado_en'));
        $this->assertFalse($notificacion->isFillable('enviada_at'));
        $this->assertSame('datetime', $notificacion->getCasts()['enviado_en']);
    }
}
