# Plan institucional de evolución para SGCI

## Objetivo

Convertir SGCI en una plataforma operable de punta a punta: gestión segura de
usuarios, capacitaciones presenciales, virtuales y autogestionadas, asistencia,
examen configurable, diploma automático y reportes de cumplimiento
institucional.

## Fase 0 — Seguridad, acceso y permisos con Spatie + Filament

- Instalar `spatie/laravel-permission` y publicar sus migraciones:

  ```bash
  composer require spatie/laravel-permission:^6.0
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  php artisan migrate
  ```

- Agregar `HasRoles` al modelo `User`, configurar el guard `web` y crear un
  seeder idempotente de roles y permisos.
- Integrar Spatie con Filament mediante:
  - Implementación correcta de `FilamentUser` y `canAccessPanel(Panel $panel)`.
  - Policies de Laravel para validar cada acción en backend.
  - Recursos Filament para administrar Roles y Permisos.
  - Visibilidad condicional de navegación, botones, widgets, columnas y
    acciones según permisos.
- Crear roles iniciales: Administrador, Gestor de capacitación, Instructor y
  Colaborador.
- Definir permisos por acción: administrar usuarios, agencias, puestos,
  capacitaciones, sesiones, inscripciones, asistencia, exámenes,
  calificaciones, diplomas, reportes, exportaciones y auditoría.
- Corregir el observer de usuarios para no sobrescribir contraseñas definidas;
  generar credenciales temporales solo para invitaciones administrativas.
- Deshabilitar el registro público. Las cuentas se crearán por alta individual
  o importación masiva, con invitación y enlace real de
  activación/restablecimiento.
- Proteger datos históricos mediante archivado o estados inactivos en lugar de
  eliminar capacitaciones, sesiones o usuarios que ya tengan evidencia
  académica.

## Fase 1 — Estructura académica y sesiones

- Ampliar las sesiones con modalidad: presencial, virtual o autogestionada;
  fecha de inicio/fin, ubicación, enlace virtual y reglas de cumplimiento.
- Configurar por sesión:
  - Requiere asistencia.
  - Requiere examen.
  - Emite diploma.
  - Nota mínima definida por el instructor.
  - Máximo de intentos configurable, con valor inicial de un intento.
- Usar la nota mínima del examen como valor predeterminado y guardarla en la
  sesión para preservar el historial por cohorte.
- Evitar inscripciones duplicadas con una restricción única por colaborador y
  sesión.
- Normalizar estados: asignado, en progreso, aprobado, no aprobado,
  completado, ausente y cancelado.
- Registrar asistencia:
  - Virtual: al acceder al enlace interno de reunión se registra fecha/hora y
    luego se redirige al enlace externo.
  - Presencial: el instructor muestra un QR temporal; el colaborador
    autenticado lo escanea y se valida su inscripción, horario y registro único.
  - Autogestionada: no requiere control de lectura; aprobar el examen es la
    evidencia de cumplimiento.
- Registrar auditoría de asignaciones, cambios de reglas, accesos, asistencia,
  calificaciones y diplomas.

## Fase 2 — Portal del colaborador y exámenes

- Reemplazar el dashboard genérico por “Mis capacitaciones”, con pendientes,
  próximas, en curso, aprobadas, vencidas y diplomas disponibles.
- Crear detalle de capacitación con recursos autorizados, datos de sesión,
  modalidad, instructor, requisitos, fechas y estado.
- Crear rutas internas para acceso a sesiones virtuales, check-in QR
  presencial, inicio/guardado/envío de examen y descarga de diploma.
- Crear tablas de intentos y respuestas de examen.
- Calificar automáticamente preguntas de opción múltiple; las preguntas
  abiertas quedan pendientes de revisión del instructor.
- Aplicar temporizador según el límite configurado y enviar automáticamente el
  intento cuando venza.
- Permitir intentos hasta el máximo de la sesión y conservar todos los
  resultados; usar el mejor resultado para la aprobación.
- Aprobar la capacitación únicamente si alcanza la nota mínima y, cuando
  aplique, registra asistencia válida.

## Fase 3 — Diplomas, notificaciones y analítica

- Generar automáticamente el diploma PDF cuando el colaborador cumpla todos
  los requisitos.
- Usar el fondo configurado para la sesión e incluir nombre, capacitación,
  instructor, fecha, nota, agencia y código único.
- Guardar diploma, archivo, fecha de emisión, puntaje y estado; permitir
  validación solo interna desde Filament por código o colaborador.
- Notificar por correo y dentro del sistema la invitación de cuenta, asignación
  de capacitación, recordatorios 7 días y 24 horas antes, habilitación de
  examen, resultado y diploma disponible.
- Crear tablero institucional filtrable por período, agencia, puesto,
  capacitación, sesión y modalidad.
- Mostrar asignados, asistencia, aprobación, finalización, pendientes,
  ausencias, diplomas emitidos y brechas por agencia/puesto.
- Exportar reportes y detalle de colaboradores en Excel y PDF.
- Ejecutar generación de diplomas, correos y exportaciones mediante cola;
  monitorear trabajos fallidos.

## Pruebas y criterios de aceptación

- Corregir las cuatro pruebas fallidas actuales relacionadas con la
  sobrescritura de contraseñas.
- Probar permisos por rol, restricciones de Filament y aislamiento de datos.
- Probar invitaciones, importación, usuarios inactivos y registros duplicados.
- Probar asistencia virtual, QR presencial vencido, QR de usuario no inscrito y
  doble check-in.
- Probar intentos configurables, temporizador, preguntas abiertas, puntuación y
  aprobación.
- Probar emisión única del diploma solo después de cumplir asistencia y nota.
- Probar filtros, contenido y permisos de exportaciones.
- Probar notificaciones en cola, archivos privados y registros de auditoría.

## Supuestos establecidos

- No habrá SSO ni registro público en la primera versión.
- La asistencia virtual valida el clic en el enlace, no la permanencia dentro
  de la videollamada.
- La validación de diplomas será interna.
- Las capacitaciones autogestionadas se completan al aprobar el examen.
- La primera analítica prioriza cumplimiento institucional, no costos ni
  integración con Microsoft Teams.
