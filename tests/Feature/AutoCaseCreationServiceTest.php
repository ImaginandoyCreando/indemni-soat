<?php

namespace Tests\Feature;

use App\Models\Caso;
use App\Models\EmailLog;
use App\Services\AutoCaseCreationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class AutoCaseCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AutoCaseCreationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AutoCaseCreationService();
    }

    /**
     * Helper para invocar métodos privados desde tests sin exponerlos.
     */
    private function invoke(string $metodo, array $args = [])
    {
        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod($metodo);
        $m->setAccessible(true);
        return $m->invokeArgs($this->service, $args);
    }

    public function test_extrae_fecha_en_formato_largo_espanol(): void
    {
        $fecha = $this->invoke('extractFecha', ['Accidente ocurrido el 13 de abril de 2026 en Bogotá']);
        $this->assertInstanceOf(Carbon::class, $fecha);
        $this->assertEquals('2026-04-13', $fecha->toDateString());
    }

    public function test_extrae_fecha_en_formato_corto(): void
    {
        $fecha = $this->invoke('extractFecha', ['Fecha: 15/06/2024']);
        $this->assertInstanceOf(Carbon::class, $fecha);
        $this->assertEquals('2024-06-15', $fecha->toDateString());
    }

    public function test_extrae_aseguradora_normalizada(): void
    {
        $this->assertSame('SURA',        $this->invoke('extractAseguradora', ['Notificación de SEGUROS SURA SA']));
        $this->assertSame('MAPFRE',      $this->invoke('extractAseguradora', ['Respuesta de Mapfre Colombia']));
        $this->assertSame('LA PREVISORA',$this->invoke('extractAseguradora', ['La Previsora S.A.']));
        $this->assertNull($this->invoke('extractAseguradora', ['Hola, buenos días']));
    }

    public function test_extrae_radicado_judicial(): void
    {
        $this->assertSame('2024.001-23', $this->invoke('extractRadicado', ['RAD: 2024.001-23']));
        $this->assertNull($this->invoke('extractRadicado', ['Sin radicado mencionado']));
    }

    public function test_detecta_tipo_evento_tutela(): void
    {
        $this->assertSame('tutela',
            $this->invoke('detectTipoEvento', ['Acción de Tutela RAD 123', 'cuerpo']));

        $this->assertSame('fallo_tutela',
            $this->invoke('detectTipoEvento', ['Notificación', 'Adjunto el fallo de tutela del juzgado']));

        $this->assertSame('incidente_desacato',
            $this->invoke('detectTipoEvento', ['Asunto', 'Se inicia incidente de desacato por incumplimiento']));
    }

    public function test_separa_nombre_completo_segun_convencion_colombiana(): void
    {
        $this->assertSame(['Juan', 'Pérez García'],
            $this->invoke('splitNombreCompleto', ['Juan Pérez García']));

        $this->assertSame(['Juan Carlos', 'Pérez García'],
            $this->invoke('splitNombreCompleto', ['Juan Carlos Pérez García']));

        $this->assertSame(['Juan Carlos María', 'Pérez García'],
            $this->invoke('splitNombreCompleto', ['Juan Carlos María Pérez García']));

        $this->assertSame(['Juan', 'Pérez'],
            $this->invoke('splitNombreCompleto', ['Juan Pérez']));

        $this->assertSame(['Por identificar', ''],
            $this->invoke('splitNombreCompleto', ['']));
    }

    public function test_correo_irrelevante_no_crea_caso(): void
    {
        $resultado = $this->service->processEmailForNewCase([
            'subject' => 'Reunión de equipo viernes',
            'body'    => 'No olvides traer tu portátil.',
            'message_id' => 'msg-1',
        ], 'gestion');

        $this->assertNull($resultado);
        $this->assertSame(0, Caso::count());
    }

    public function test_dedup_no_crea_caso_si_el_correo_ya_fue_procesado(): void
    {
        EmailLog::create([
            'email_id' => 'msg-dup-1',
            'subject'  => 'cualquiera',
            'processed' => true,
        ]);

        $resultado = $this->service->processEmailForNewCase([
            'subject' => 'Acción de tutela RAD 2024.001 contra SURA por SOAT',
            'body'    => 'Paciente Juan Pérez García sufrió accidente de tránsito',
            'message_id' => 'msg-dup-1',
        ], 'gestion');

        $this->assertNull($resultado);
        $this->assertSame(0, Caso::count());
    }

    public function test_correo_jurídico_relevante_crea_caso_con_datos_extraidos(): void
    {
        $resultado = $this->service->processEmailForNewCase([
            'subject'    => 'Acción de tutela seguida por JUAN PEREZ GARCIA RAD: 2024.001-23',
            'body'       => 'Notificación de SURA. Accidente del 13 de abril de 2026.',
            'from_email' => 'notificaciones@juzgado.gov.co',
            'message_id' => 'msg-real-1',
        ], 'gestion');

        $this->assertNotNull($resultado);
        $this->assertTrue($resultado['success']);

        $caso = Caso::first();
        $this->assertNotNull($caso);
        $this->assertTrue((bool) $caso->auto_created);
        $this->assertSame('gestion', $caso->auto_created_from);
        $this->assertSame('SURA', $caso->aseguradora);
        $this->assertSame('RAD-2024.001-23', $caso->numero_caso);

        // Log del correo asociado
        $this->assertSame(1, EmailLog::where('email_id', 'msg-real-1')->count());
        $this->assertSame($caso->id, EmailLog::where('email_id', 'msg-real-1')->first()->caso_id);
    }
}
