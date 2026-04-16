# 🚀 GUÍA DEFINITIVA - CONFIGURACIÓN AZURE PARA INDEMNI SOAT

## 📋 PASOS CORREGIDOS PARA CUENTAS PERSONALES (@outlook.com, @hotmail.com)

---

## 🔑 PASO 1 - REGISTRAR APP EN AZURE

### 1.1 Acceder a Azure Portal
- **URL:** https://portal.azure.com
- **Navegar a:** Microsoft Entra ID → App registrations

### 1.2 Nueva App Registration
- **Click:** "New registration"
- **Nombre:** "IndemniSOAT Email System"
- **Supported account types:** 
  ```
  ✅ Accounts in any organizational directory AND personal Microsoft accounts
  ```
- **Redirect URI:** Dejar vacío (usaremos client_credentials flow)

---

## 🔐 PASO 2 - CONFIGURAR PERMISOS CORRECTOS

### 2.1 Agregar Permisos Delegados (NO Application Permissions)
- **Ir a:** API Permissions → Add a permission
- **Seleccionar:** "Microsoft Graph" → "Delegated permissions"
- **Agregar estos permisos:**
  ```
  ✅ Mail.Read
  ✅ Mail.ReadWrite  
  ✅ User.Read
  ✅ offline_access (¡INDISPENSABLE!)
  ```

### 2.2 IMPORTANTE - ¿Por qué Delegated y no Application?
- **Microsoft bloquea Application permissions** para cuentas personales
- **Tus correos son @outlook.com y @hotmail.com** (cuentas personales)
- **Delegated permissions** permiten acceso a correos de usuarios personales
- **Application permissions** solo funcionan para cuentas organizacionales

---

## 🔑 PASO 3 - OBTENER CREDENCIALES

### 3.1 Client Secret
- **Ir a:** Certificates & secrets
- **Click:** "New client secret"
- **Description:** "IndemniSOAT Production"
- **Expiry:** 24 months
- **Copiar inmediatamente** el valor (no se puede ver después)

### 3.2 Application ID
- **Copiar el "Application (client) ID"**
- **Guardar ambos valores** - se necesitan para configuración

---

## ⚙️ PASO 4 - CONFIGURACIÓN EN KOYEB

### 4.1 Variables de Entorno
Agrega estas variables exactas en el panel de Koyeb:

```env
# Microsoft Graph API Configuration (CORREGIDO para cuentas personales)
MICROSOFT_GRAPH_CLIENT_ID=XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX
MICROSOFT_GRAPH_CLIENT_SECRET=LA_CLAVE_SECRETA_GENERADA_EN_AZURE
MICROSOFT_GRAPH_TENANT_ID=consumers
MICROSOFT_GRAPH_REDIRECT_URI=https://conscious-cassandra-indemni-soat-6d20a413.koyeb.app/auth/callback

# Cuentas de correo (sin cambios)
GESTION_EMAIL=gestionsoat365@outlook.com
GESTION_PASSWORD=nbwvvzqhetsvrtug
RECLAMACIONES_EMAIL=reclamacionessoat@hotmail.com
RECLAMACIONES_PASSWORD=tcqwucljosshibse
```

### 4.2 Puntos Clave
- ✅ **Tenant ID:** `consumers` (NO `common`)
- ✅ **Endpoint:** `https://login.microsoftonline.com/consumers/oauth2/v2.0/token`
- ✅ **Permisos:** Delegated (NO Application)
- ✅ **Scope completo:** incluye `offline_access`

---

## 🎯 PASO 5 - VERIFICACIÓN FINAL

### 5.1 Esperar Deploy
- **Tiempo:** 2-3 minutos después de configurar Koyeb

### 5.2 Probar Sistema
- **URL:** https://conscious-cassandra-indemni-soat-6d20a413.koyeb.app/emails
- **Click:** "Sincronizar correos"

### 5.3 Resultado Esperado
```
🎉 Se procesaron X correos de 2 cuentas:
- gestionsoat365: X correos
- reclamaciones: X correos

🎉 X casos nuevos creados automáticamente
```

---

## 🚨 ERRORES COMUNES Y SOLUCIONES

### Error: "Invalid tenant"
- **Causa:** Usaste `common` en lugar de `consumers`
- **Solución:** Cambiar a `MICROSOFT_GRAPH_TENANT_ID=consumers`

### Error: "Insufficient privileges"
- **Causa:** Usaste Application permissions
- **Solución:** Cambiar a Delegated permissions

### Error: "Token expired"
- **Causa:** No configuraste `offline_access`
- **Solución:** Agregar permiso `offline_access` y volver a generar secret

---

## 🎉 CONFIGURACIÓN COMPLETA

### ✅ Una vez configurado:
- 🤖 **Sistema procesa correos automáticamente** cada 15 minutos
- 📧 **Detecta negocios nuevos** y crea casos automáticamente
- 🔄 **Actualiza casos existentes** con nueva información
- 📝 **Genera bitácoras inteligentes** con acciones recomendadas
- ⚠️ **Envía alertas automáticas** de casos críticos
- 🌐 **Funciona 24/7** sin intervención manual

### 🚀 El sistema trabaja para ti:
- **Sin revisión manual de correos**
- **Sin olvidos de casos importantes**
- **Con notificaciones automáticas**
- **Compatible con cualquier hosting**

---

## 📞 SOPORTE

Si tienes problemas:
1. **Verifica los permisos** (deben ser Delegated)
2. **Confirma el tenant ID** (debe ser `consumers`)
3. **Revisa el client secret** (debe estar activo)
4. **Verifica las variables en Koyeb** (deben coincidir exactamente)

---

## 🎯 LISTO PARA USAR

Una vez completados estos pasos, tu sistema Indemni SOAT estará completamente automatizado y funcionando 24/7.
