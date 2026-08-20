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

## Tabla recomendada para Koyeb

Koyeb permite interpolar variables propias, incluyendo `KOYEB_PUBLIC_DOMAIN`, usando la sintaxis `{{ KOYEB_PUBLIC_DOMAIN }}`; la sustitución se procesa durante el deploy [2]. Usa la siguiente configuración como referencia y guarda las credenciales como Secrets cuando corresponda:

| Variable | Valor recomendado | Observación |
|---|---|---|
| `APP_ENV` | `production` | Entorno del servicio desplegado. |
| `APP_DEBUG` | `false` | No debe estar activo en producción. |
| `APP_URL` | `https://{{ KOYEB_PUBLIC_DOMAIN }}` | Sin barra final; evita duplicarla en el callback OAuth. |
| `APP_TIMEZONE` | `America/Bogota` | Alinea fechas y horarios del negocio. |
| `DB_CONNECTION` | `pgsql` | Compatible con Supabase PostgreSQL. |
| `DB_SSLMODE` | `require` | Requerido para la conexión TLS a Supabase. |
| `PGSSLMODE` | `require` | Complementario para clientes PostgreSQL que lo utilicen. |
| `SESSION_DRIVER` | `database` | La migración del proyecto crea la tabla `sessions`. |
| `SESSION_SECURE_COOKIE` | `true` | Recomendado porque Koyeb expone el servicio por HTTPS. |
| `CACHE_STORE` | `database` | Usa las tablas `cache` y `cache_locks` existentes. |
| `QUEUE_CONNECTION` | `database` | Requiere un worker si se desea procesar correos en cola. |
| `LOG_CHANNEL` | `stderr` | Adecuado para consultar logs desde Koyeb. |
| `LOG_LEVEL` | `info` | Evita el volumen excesivo de `debug` en producción. |
| `MAIL_MAILER` | `smtp` | Activa el envío SMTP de Laravel. |
| `MAIL_SCHEME` | `smtps` | Valor correcto para SMTP implícito por el puerto 465. |
| `MAIL_HOST` | `smtp.gmail.com` | Servidor SMTP de Gmail. |
| `MAIL_PORT` | `465` | Puerto TLS implícito. |
| `WHATSAPP_BASE_URL` | `https://api.ultramsg.com` | Endpoint de UltraMsg. |
| `WHATSAPP_INSTANCE_ID` | Secret de UltraMsg | Identificador de la instancia conectada. |
| `WHATSAPP_TOKEN` | Secret de UltraMsg | Token de autenticación de la instancia. |

Para el procesamiento automático de correos también deben existir `GESTION_EMAIL`, `GESTION_PASSWORD`, `RECLAMACIONES_EMAIL`, `RECLAMACIONES_PASSWORD`, `MICROSOFT_GRAPH_CLIENT_ID`, `MICROSOFT_GRAPH_CLIENT_SECRET`, `MICROSOFT_GRAPH_TENANT_ID=consumers` y `MICROSOFT_GRAPH_REDIRECT_URI=https://{{ KOYEB_PUBLIC_DOMAIN }}/outlook/callback`. Las variables `OUTLOOK_CLIENT_ID` y `OUTLOOK_CLIENT_SECRET` solo son necesarias si se utiliza la conexión OAuth desde el panel. Aunque existe `EMAIL_SYNC_ENABLED`, el comando `emails:auto-process` actualmente no consulta ese flag, por lo que no funciona como interruptor de ejecución.

El código acepta todavía `MAIL_ENCRYPTION=ssl` como compatibilidad heredada, pero la variable preferida para este proyecto es `MAIL_SCHEME=smtps`, porque `config/mail.php` usa la configuración de Symfony Mailer de Laravel 12.

## Flujo automático

El contenedor ejecuta `php artisan schedule:run` cada 60 segundos mediante Supervisor y mantiene un worker `queue:work database` para procesar los correos encolados. La tarea `whatsapp:notificar` se ejecuta diariamente a las **08:00 de Colombia**, con prevención de ejecuciones simultáneas. Antes de enviar, la aplicación revisa los contactos activos, resuelve la alerta actual de cada caso, aplica la ventana de reenvío y registra cada envío en `whatsapp_notificaciones_enviadas.enviado_en`.

Las alertas de recordatorio se vuelven a enviar según los días definidos en `config/whatsapp.php`. Las alertas con valor `0` se envían una sola vez por caso, alerta y número; cuando cambia la alerta del caso, las notificaciones obsoletas se limpian.

## Verificación después del deploy

Desde la pantalla administrativa de WhatsApp, registra un contacto con número colombiano de 10 dígitos o con prefijo `57`, activa el contacto y usa la acción de prueba. Para una comprobación sin envío real, ejecuta en el contenedor:

```bash
php artisan whatsapp:notificar --dry-run --debug koyeb
```

El argumento final `koyeb` es intencional: la consola web de Koyeb puede adjuntar un retorno de carro al final de la línea y Symfony Console lo interpretaba como un argumento inesperado. La aplicación ahora acepta y descarta ese sufijo. Para revisar el scheduler, consulta los logs de Koyeb y el archivo `storage/logs/whatsapp-scheduler.log`. Los errores de autenticación, respuesta inesperada, número inválido o excepción de red se registran sin incluir el token de UltraMsg.

Si el envío manual falla, verifica en este orden: que las dos credenciales estén configuradas en el servicio correcto de Koyeb; que la instancia esté conectada en UltraMsg; que el número incluya el código de país; y que el despliegue haya reconstruido la caché de configuración después de modificar los Secrets.

## Seguridad

El archivo `.env.example` contiene únicamente marcadores para credenciales. Si las claves de correo que estaban previamente en ese archivo fueron reales, deben revocarse y reemplazarse desde el proveedor correspondiente, porque eliminarlas del archivo no las elimina del historial de Git.

## Referencias

[1]: https://docs.ultramsg.com/api/post/messages/chat "UltraMsg: Send a text message to phone number or group"
[2]: https://www.koyeb.com/docs/build-and-deploy/environment-variables "Koyeb: Environment Variables"
[3]: https://symfony.com/doc/current/mailer.html "Symfony Mailer: Sending Emails with Mailer"
