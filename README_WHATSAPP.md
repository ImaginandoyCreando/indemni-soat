# Alertas automáticas por WhatsApp

La aplicación usa **UltraMsg** para enviar alertas de los casos jurídicos. El endpoint utilizado es `POST https://api.ultramsg.com/{instance_id}/messages/chat`; el proveedor documenta los campos `token`, `to` en formato internacional y `body` para el texto del mensaje [1].

## Variables en Koyeb

Configura estas variables como **Secrets/Environment variables** del servicio, no dentro del repositorio:

```dotenv
APP_TIMEZONE=America/Bogota
WHATSAPP_BASE_URL=https://api.ultramsg.com
WHATSAPP_INSTANCE_ID=tu_instance_id
WHATSAPP_TOKEN=tu_token
```

`WHATSAPP_INSTANCE_ID` y `WHATSAPP_TOKEN` se obtienen desde la instancia de UltraMsg. La instancia debe estar autorizada y conectada a WhatsApp; si no lo está, UltraMsg puede dejar el mensaje en cola hasta que la instancia quede lista [1].

## Flujo automático

El contenedor ejecuta `php artisan schedule:run` cada 60 segundos mediante Supervisor. La tarea `whatsapp:notificar` se ejecuta diariamente a las **08:00 de Colombia**, con prevención de ejecuciones simultáneas. Antes de enviar, la aplicación revisa los contactos activos, resuelve la alerta actual de cada caso, aplica la ventana de reenvío y registra cada envío en `whatsapp_notificaciones_enviadas.enviado_en`.

Las alertas de recordatorio se vuelven a enviar según los días definidos en `config/whatsapp.php`. Las alertas con valor `0` se envían una sola vez por caso, alerta y número; cuando cambia la alerta del caso, las notificaciones obsoletas se limpian.

## Verificación después del deploy

Desde la pantalla administrativa de WhatsApp, registra un contacto con número colombiano de 10 dígitos o con prefijo `57`, activa el contacto y usa la acción de prueba. Para una comprobación sin envío real, ejecuta en el contenedor:

```bash
php artisan whatsapp:notificar --dry-run --debug
```

Para revisar el scheduler, consulta los logs de Koyeb y el archivo `storage/logs/whatsapp-scheduler.log`. Los errores de autenticación, respuesta inesperada, número inválido o excepción de red se registran sin incluir el token de UltraMsg.

Si el envío manual falla, verifica en este orden: que las dos credenciales estén configuradas en el servicio correcto de Koyeb; que la instancia esté conectada en UltraMsg; que el número incluya el código de país; y que el despliegue haya reconstruido la caché de configuración después de modificar los Secrets.

## Seguridad

El archivo `.env.example` contiene únicamente marcadores para credenciales. Si las claves de correo que estaban previamente en ese archivo fueron reales, deben revocarse y reemplazarse desde el proveedor correspondiente, porque eliminarlas del archivo no las elimina del historial de Git.

## Referencias

[1]: https://docs.ultramsg.com/api/post/messages/chat "UltraMsg: Send a text message to phone number or group"
