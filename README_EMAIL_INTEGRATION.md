# Integración de Correos Electrónicos - Sistema Indemni SOAT

## Overview
Sistema automatizado para procesar correos electrónicos de aseguradoras y actualizar el estado de los casos jurídicos automáticamente.

## Funcionalidades

### 1. Detección Automática
- **Identificación de aseguradoras:** Detecta automáticamente SURA, MAPFRE, HDI, Bolívar, etc.
- **Clasificación de correos:** Solicitud enviada, respuesta positiva/negativa, en proceso, etc.
- **Asociación de casos:** Relaciona correos con casos existentes

### 2. Actualización Automática de Estados
- Cambia el estado del caso según el tipo de correo recibido
- Registra automáticamente en la bitácora
- Calcula nuevos plazos y fechas importantes

### 3. Sistema de Alertas
- **30 días sin respuesta:** Alerta automática de seguimiento
- **Notificaciones inmediatas:** Cuando llega un correo importante
- **Alertas críticas:** Casos cerca de prescripción

## Instalación

### 1. Migraciones
```bash
php artisan migrate
```

### 2. Configurar Scheduler
En `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('emails:sync')->everySixHours();
}
```

### 3. Configurar Cuentas de Correo
- Gmail API: Configurar OAuth2 credentials
- Outlook API: Configurar Microsoft Graph
- IMAP: Para servidores de correo tradicionales

## Uso

### Comando Manual
```bash
php artisan emails:sync
```

### Interfaz Web
Acceder a `/emails` para:
- Configurar cuentas de correo
- Ver estadísticas
- Revisar correos procesados
- Configurar alertas

## Proveedores Soportados

### Gmail
- API REST de Gmail
- OAuth2 authentication
- Acceso a bandeja de entrada y enviados

### Outlook/Exchange
- Microsoft Graph API
- Azure AD authentication
- Soporte para Office 365

### IMAP/POP3
- Servidores tradicionales
- Conexión segura SSL/TLS
- Compatible con la mayoría de proveedores

## Clasificación de Correos

### Tipos Detectados
- **solicitud_enviada**: "enviamos solicitud", "radicamos"
- **respuesta_positiva**: "aprobada", "procede", "aceptada"
- **respuesta_negativa**: "niega", "rechaza", "negada"
- **en_proceso**: "estudiando", "analizando", "tramitando"
- **requiere_documentos**: "requiere", "solicita", "necesita"
- **citacion**: "citación", "audiencia", "conciliación"

### Aseguradoras Detectadas
- SURA, MAPFRE, HDI, Bolívar, Estado
- Liberty, AXA, Solidaria, Aseguradora La Previsora
- Y más configurables según necesidad

## Configuración

### Variables de Entorno
```env
GMAIL_CLIENT_ID=your_client_id
GMAIL_CLIENT_SECRET=your_client_secret
OUTLOOK_CLIENT_ID=your_client_id
OUTLOOK_CLIENT_SECRET=your_client_secret
EMAIL_SYNC_ENABLED=true
```

### Configuración de Alertas
- Tiempo sin respuesta: 30 días (configurable)
- Frecuencia de revisión: Cada 6 horas
- Destinatarios de alertas: Configurable

## Seguridad

### Permisos Requeridos
- Acceso solo lectura a correos
- No se envían correos automáticamente
- Tokens encriptados en base de datos

### Privacidad
- Solo se procesan correos relacionados con casos
- Información sensible encriptada
- Logs de auditoría disponibles

## Monitoreo

### Estadísticas Disponibles
- Correos procesados por día
- Casos actualizados automáticamente
- Tiempo de respuesta promedio
- Tasa de detección correcta

### Logs
- Todos los correos procesados
- Errores de detección
- Cambios de estado automáticos
- Alertas enviadas

## Troubleshooting

### Problemas Comunes
1. **Error 500**: Revisar límites de tamaño de archivos
2. **Sin detección**: Verificar patrones de búsqueda
3. **Casos no asociados**: Revisar formatos de número de caso

### Debug Mode
```env
EMAIL_DEBUG=true
```

## Mejoras Futuras

### Planificado
- Integración con IA (GPT) para mejor clasificación
- Soporte para WhatsApp y otros canales
- Dashboard en tiempo real
- Reportes automáticos

### Opcional
- Respuestas automáticas básicas
- Integración con sistemas de gestión documental
- API para integraciones externas
