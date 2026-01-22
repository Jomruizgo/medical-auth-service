# Backlog - Medical Auth Microservice

## Historias de Usuario

---

### HU-AUTH-01: Registro de Usuario

**Como** usuario nuevo
**Quiero** registrarme en el sistema
**Para** poder acceder a las funcionalidades de la aplicación

#### Criterios de Aceptación
- El usuario debe proporcionar: email, password, nombre, apellido
- El email debe ser único en el sistema
- El password debe tener mínimo 8 caracteres
- Se debe validar el formato del email
- Al registrarse exitosamente, se retorna el usuario creado (sin password)
- Se debe hashear el password con bcrypt antes de almacenarlo

#### Endpoint
```
POST /api/auth/register
```

#### Request Body
```json
{
    "email": "usuario@ejemplo.com",
    "password": "password123",
    "first_name": "Juan",
    "last_name": "Pérez"
}
```

#### Response (201 Created)
```json
{
    "success": true,
    "data": {
        "id": 1,
        "email": "usuario@ejemplo.com",
        "first_name": "Juan",
        "last_name": "Pérez",
        "role": "patient",
        "created_at": "2024-01-15T10:30:00Z"
    }
}
```

#### Errores
- 400: Datos inválidos o faltantes
- 409: Email ya registrado

---

### HU-AUTH-02: Inicio de Sesión (Login)

**Como** usuario registrado
**Quiero** iniciar sesión con mis credenciales
**Para** obtener un token de acceso al sistema

#### Criterios de Aceptación
- El usuario debe proporcionar email y password
- Se valida que el email exista y el password sea correcto
- Al autenticarse exitosamente, se retorna access_token y refresh_token
- El access_token tiene validez de 1 hora
- El refresh_token tiene validez de 7 días
- Se debe registrar la fecha del último login

#### Endpoint
```
POST /api/auth/login
```

#### Request Body
```json
{
    "email": "usuario@ejemplo.com",
    "password": "password123"
}
```

#### Response (200 OK)
```json
{
    "success": true,
    "data": {
        "access_token": "eyJhbGciOiJIUzI1NiIs...",
        "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
        "token_type": "Bearer",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "email": "usuario@ejemplo.com",
            "first_name": "Juan",
            "last_name": "Pérez",
            "role": "patient"
        }
    }
}
```

#### Errores
- 400: Datos inválidos o faltantes
- 401: Credenciales incorrectas

---

### HU-AUTH-03: Refrescar Token

**Como** usuario autenticado
**Quiero** refrescar mi token de acceso
**Para** mantener mi sesión activa sin volver a ingresar credenciales

#### Criterios de Aceptación
- El usuario debe proporcionar un refresh_token válido
- Se valida que el refresh_token no esté expirado
- Se genera un nuevo par de access_token y refresh_token
- El refresh_token anterior se invalida

#### Endpoint
```
POST /api/auth/refresh
```

#### Request Body
```json
{
    "refresh_token": "eyJhbGciOiJIUzI1NiIs..."
}
```

#### Response (200 OK)
```json
{
    "success": true,
    "data": {
        "access_token": "eyJhbGciOiJIUzI1NiIs...",
        "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}
```

#### Errores
- 400: Refresh token no proporcionado
- 401: Refresh token inválido o expirado

---

### HU-AUTH-04: Obtener Perfil Actual

**Como** usuario autenticado
**Quiero** obtener mi información de perfil
**Para** ver mis datos personales en el sistema

#### Criterios de Aceptación
- Requiere autenticación (Bearer token)
- Se retorna la información del usuario autenticado
- No se retorna el password ni información sensible

#### Endpoint
```
GET /api/auth/me
```

#### Headers
```
Authorization: Bearer <access_token>
```

#### Response (200 OK)
```json
{
    "success": true,
    "data": {
        "id": 1,
        "email": "usuario@ejemplo.com",
        "first_name": "Juan",
        "last_name": "Pérez",
        "role": "patient",
        "created_at": "2024-01-15T10:30:00Z",
        "last_login": "2024-01-20T08:15:00Z"
    }
}
```

#### Errores
- 401: No autenticado o token inválido

---

### HU-AUTH-05: Cambiar Contraseña

**Como** usuario autenticado
**Quiero** cambiar mi contraseña
**Para** mantener la seguridad de mi cuenta

#### Criterios de Aceptación
- Requiere autenticación (Bearer token)
- El usuario debe proporcionar la contraseña actual y la nueva
- Se valida que la contraseña actual sea correcta
- La nueva contraseña debe tener mínimo 8 caracteres
- La nueva contraseña no puede ser igual a la actual
- Al cambiar la contraseña, se invalidan todos los refresh tokens anteriores

#### Endpoint
```
PUT /api/auth/password
```

#### Headers
```
Authorization: Bearer <access_token>
```

#### Request Body
```json
{
    "current_password": "password123",
    "new_password": "newPassword456"
}
```

#### Response (200 OK)
```json
{
    "success": true,
    "message": "Contraseña actualizada exitosamente"
}
```

#### Errores
- 400: Datos inválidos o nueva contraseña no cumple requisitos
- 401: No autenticado o contraseña actual incorrecta

---

## Resumen de Endpoints

| ID | Método | Endpoint | Auth | Descripción |
|----|--------|----------|------|-------------|
| HU-AUTH-01 | POST | `/api/auth/register` | No | Registro de usuario |
| HU-AUTH-02 | POST | `/api/auth/login` | No | Inicio de sesión |
| HU-AUTH-03 | POST | `/api/auth/refresh` | No | Refrescar token |
| HU-AUTH-04 | GET | `/api/auth/me` | Sí | Obtener perfil |
| HU-AUTH-05 | PUT | `/api/auth/password` | Sí | Cambiar contraseña |

---

## Roles del Sistema

| Rol | Descripción |
|-----|-------------|
| admin | Administrador del sistema |
| doctor | Médico/Doctor |
| patient | Paciente |

---

## Modelo de Datos: User

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | Identificador único |
| email | VARCHAR(255) | Email único |
| password | VARCHAR(255) | Password hasheado |
| first_name | VARCHAR(100) | Nombre |
| last_name | VARCHAR(100) | Apellido |
| role | ENUM | admin, doctor, patient |
| created_at | TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | Última actualización |
| last_login | TIMESTAMP | Último inicio de sesión |
