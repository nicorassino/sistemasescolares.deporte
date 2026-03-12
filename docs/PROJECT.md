# Juvenilia — Documentación del Proyecto

Documento de referencia para mantener contexto del sistema. **Código y DB en INGLÉS; UI y textos en ESPAÑOL.**

---

## Stack y entorno

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Blade, Tailwind CSS, Alpine.js, Livewire 4
- **Base de datos:** MySQL (no PostgreSQL, SQLite ni MongoDB)
- **Archivos:** `storage/app/` (comprobantes en disco `local`/private; anuncios en disco `public`)
- **Correo:** Laravel Mail + SMTP Brevo
- **Producción:** VPS Hostinger (escalable a Cloud)

---

## Modelos y tablas principales

| Modelo       | Tabla          | Notas |
|-------------|----------------|--------|
| User        | users          | roles: admin, tutor, teacher |
| Student     | students       | first_name, last_name, dni, birth_date, gender, scholarship_percentage |
| Tutor       | tutors         | user_id, wallet_balance |
| Group       | groups         | teacher_id, name, description, year, level, max_capacity |
| Teacher     | teachers       | user_id, first_name, last_name, email, phone |
| Fee         | fees           | student_id, group_id, teacher_id, period (YYYY-MM), amount, paid_amount, status (pending|paid|partial|overdue|cancelled), issued_at, paid_at, last_reminder_sent_at |
| Payment     | payments       | fee_id, tutor_id, teacher_id, amount_reported, status (pending_review|approved|rejected), evidence_file_path, transfer_sender_name |
| Attendance  | attendances    | student_id, teacher_id, date, status (P|A) |
| Announcement| announcements  | title, content, image_path, author_id (users) |
| Receipt     | receipts       | fee_id, payment_id, pdf_path (opcional) |

**Relaciones clave**

- Student ↔ Tutor: `tutor_student` (relationship_type, is_primary)
- Student ↔ Group: `group_student` (from_date, to_date, is_current)
- Group → Teacher: `groups.teacher_id`
- Fee → Student, Group, Teacher; Fee hasMany Payment
- Payment → Fee, Tutor, Teacher (teacher_id para cobros en efectivo)
- Announcement → User (author_id)

---

## Rutas y portales

### Portal Tutor (padres)

- **Prefijo:** `/tutor`
- **Login:** `TeacherLogin` → auth con `role=tutor` y `user->tutor`
- **Dashboard:** `TutorDashboard` (layout `layouts.tutor`)
  - **Pestañas:** Escuela (institucional + logos), Novedades (feed Announcement), Pago de cuotas (lista fees pendientes + modal “Informar pago”)
  - Comprobantes: se guardan en disco por defecto, carpeta `payments/`, nombre `{dni}_fee{id}_{timestamp}.{ext}`

### Portal Profesor

- **Prefijo:** `/profesor` (no /teacher)
- **Login:** `TeacherLogin` → auth con `role=teacher` y `user->teacher`
- **Dashboard:** `TeacherDashboard` (layout `layouts.teacher`)
  - Selector grupo (teacher->groups) y fecha
  - Asistencia: toggle Presente/Ausente por alumno → tabla `attendances`
  - Cobros en efectivo: modal por alumno; si monto ≥ deuda marca fee paid y opcionalmente suma diferencia a tutor.wallet_balance; crea Payment con teacher_id y envía PaymentApprovedMail

### Panel administrativo

- **Prefijo:** `/admin`
- **Layout:** `layouts.app` (header con logos `public/IMG/logo_juvenilia.jpeg`, `public/IMG/logodepte.jpeg`, menú responsivo: desktop en línea, móvil hamburguesa)
- **Rutas:**
  - `GET /admin` → **AdminDashboard** (inicio: resumen novedades, pagos en tesorería, última cuota generada y pagadas por grupo)
  - `/admin/grupos` → GroupsPage (CRUD grupos)
  - `/admin/alumnos` → StudentsPage (CRUD alumnos, asignación tutor y grupo; puede crear User+Tutor)
  - `/admin/profesores` → TeachersPage (CRUD profesores, grupos a cargo, acceso portal: login_email + login_password → User role teacher)
  - `/admin/novedades` → AnnouncementManager (CRUD anuncios; tope 15, imagen opcional 2MB jpg/png en `storage/app/public/announcements`; requiere `php artisan storage:link`)
  - `/admin/cuotas/generar` → GeneradorCuotasMasivo (genera Fee por período/grupo, aplica scholarship_percentage)
  - `/admin/tesoreria` → TreasuryPanel (payments pending_review; aprobar/rechazar; aprobar envía PaymentApprovedMail; rechazar borra archivo y limpia evidence_*)
  - `/admin/tesoreria/comprobante/{payment}` → descarga archivo comprobante (Storage)
  - `/admin/deudas` → FeeManager (filtros mes/año/grupo/estado; Ver recibo [URL firmada], Enviar recordatorio → PaymentReminderMail + last_reminder_sent_at)

---

## Recibos y correos

- **Recibos PDF:** DomPDF (`barryvdh/laravel-dompdf`). `ReceiptController@download(Fee $fee)`: acceso por auth (tutor del alumno o admin) o por URL firmada (correo). Vista `pdf.receipt-pdf` (ES). Rutas: `/recibo/{fee}` (auth), `/recibo/{fee}/descargar` (signed).
- **PaymentApprovedMail:** asunto “Recibo de pago acreditado - Juvenilia”; incluye URL firmada al recibo. Se envía desde TreasuryPanel (aprobar) y TeacherDashboard (cobro efectivo).
- **PaymentReminderMail:** asunto “Aviso de vencimiento de cuota - Juvenilia”; datos de deuda y bancarios. Se envía desde FeeManager (Enviar recordatorio).
- **Brevo:** MAIL_MAILER=smtp, MAIL_HOST=smtp-relay.brevo.com, MAIL_PORT=587, etc.

---

## Limpieza y tareas programadas

- **Comando:** `php artisan payments:cleanup` (signature `payments:cleanup`)
  - Payment con evidence_file_path no null y (updated_at o paid_on_date) &lt; 90 días: borra archivo del storage y pone evidence_file_path (y size/mime) en null; no borra el registro.
  - Mensaje consola en español: “Limpieza completada: X comprobantes antiguos eliminados.”
- **Scheduler:** `routes/console.php` → `Schedule::command('payments:cleanup')->dailyAt('03:00');`

---

## Storage y assets

- **Comprobantes de pago:** disco por defecto (local), carpeta `payments/`. Descarga vía ruta admin que usa Storage.
- **Imágenes novedades:** disco `public`, carpeta `announcements/`. URL: `asset('storage/'.$announcement->image_path)` → requiere `php artisan storage:link`.
- **Logos:** `public/IMG/logo_juvenilia.jpeg`, `public/IMG/logodepte.jpeg` → `asset('IMG/...')`.

---

## Cómo ejecutar

```bash
composer install
cp .env.example .env && php artisan key:generate
# Configurar .env: DB_*, MAIL_*, APP_URL
php artisan migrate
php artisan storage:link
php artisan serve
```

Para scheduler: cron que ejecute `php artisan schedule:run` cada minuto.
