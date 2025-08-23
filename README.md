# 🏫 Malulu

**Malulu** es un sistema de gestión integral para instituciones educativas, diseñado para manejar múltiples escuelas y múltiples roles por usuario. Está construido sobre Laravel 10 con una arquitectura SPA moderna basada en Inertia.js + Vue 3 + Vite.

---

## 🧭 Características funcionales

- 🏫 Soporte multiescuela
- 🔄 Usuarios con múltiples roles en diferentes instituciones
- 🔐 Accesos y acciones por institución, basados en roles (autoridades, docentes, tutores/as y estudiantes)
- 📚 Gestión de documentación pedagógica, personal y administrativa
- 🧑‍🏫 Organización de cursos/grupos, asistencia y calificaciones
- 💬 Comunicación interna entre miembros de la institución

---

## ⚙️ Stack técnico

Este proyecto utiliza:

### 🔧 Backend (Laravel)

- **Laravel 10** – Framework principal (requiere PHP 8.1+)
- **Sanctum** (`^3.2`) – Autenticación SPA/token
- **Spatie Laravel-Permission** (`^6.19`) – Roles y permisos asociados a `team_id` (escuelas)

### 🌐 Frontend (SPA)

- **Vite** (`^5.0`) – Bundler moderno
- **Vue 3.4** – Framework progresivo y reactivo
- **Quasar** – Framwork
- **Inertia.js** (`^0.6.8`) – Integración Laravel + Vue sin necesidad de API REST
- **SASS** – Preprocesador CSS con metodología BEM para estilos modulares y mantenibles
---

## 🛠️ Requisitos

- PHP 8.1 o superior
- Node.js 18+
- Composer
- BBDD MySQL 8.1+ / PostgreSQL

---

## 🚀 Instalación (entorno local)

```bash
git clone https://github.com/maestro-omar/malulu.git
cd malulu

# Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# Frontend
npm install
npm run build
npm run dev --hosts
