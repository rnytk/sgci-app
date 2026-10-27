# Guía de trabajo — SGCI

## Propósito

SGCI es el Sistema de Gestión de Capacitación Interna de KATO-KI. Administra
usuarios, agencias, puestos, capacitaciones, recursos de estudio, sesiones,
inscripciones, evaluaciones y diplomas.

## Tecnologías establecidas

Mantener el stack existente. No introducir otro framework o tecnología
equivalente sin una decisión explícita del responsable del proyecto.

- PHP 8.2+ y Laravel 12.
- Filament 5 para el panel administrativo (`/admin`).
- Livewire 4 y Volt para las interfaces autenticadas del colaborador.
- Blade y Tailwind CSS para las vistas.
- PostgreSQL como base de datos de producción.
- Pest para pruebas automatizadas.
- Colas de Laravel para notificaciones por correo y procesos diferidos.

## Forma de construcción por etapas

El proyecto se construye de forma incremental. Cada etapa debe estar completa,
probada y aceptada antes de iniciar la siguiente. No mezclar cambios de varias
etapas en una misma entrega salvo que exista una dependencia técnica inevitable.

Orden de trabajo vigente:

1. **Seguridad y roles:** integrar Filament Shield, roles, permisos, políticas
   y acceso seguro al panel.
2. **Evaluaciones:** persistir respuestas por intento, calificación automática
   de opción múltiple y revisión manual de preguntas abiertas.
3. **Diplomas:** generar el PDF solo para una inscripción con resultado final
   aprobado y diploma habilitado.
4. **Mejoras posteriores:** reportes, indicadores, notificaciones adicionales
   u otros módulos aprobados expresamente.

Antes de comenzar una etapa, revisar las decisiones funcionales aprobadas, las
migraciones existentes y las relaciones de Eloquent afectadas. Al terminarla,
validar y documentar el resultado antes de continuar.

## Reglas de negocio ya aprobadas

- Los roles iniciales serán `super_administrador`,
  `administrador_capacitaciones`, `evaluador` y `colaborador`.
- Solo `super_administrador` puede crear o importar usuarios y asignar roles.
- El registro público debe permanecer deshabilitado.
- Un colaborador tiene un único intento de examen por inscripción.
- Las preguntas de opción múltiple se califican automáticamente según los
  puntos de la pregunta; las abiertas admiten puntuación parcial.
- Si un examen contiene una pregunta abierta, el intento queda en
  `pendiente_calificacion` aunque las respuestas automáticas ya alcancen la
  nota mínima. El resultado definitivo se calcula solo cuando el evaluador
  termine de calificar todas las respuestas abiertas.
- Un diploma solo puede descargarse cuando el intento esté finalizado,
  aprobado y la sesión tenga habilitada la emisión de diploma.
- El diploma PDF se genera bajo demanda, sin código verificable, y contiene el
  nombre del colaborador, la capacitación, la fecha de aprobación y el fondo
  configurado para la sesión.

## Convenciones de implementación Laravel

- Usar migraciones nuevas, reversibles y con claves foráneas, índices y
  restricciones de unicidad cuando correspondan. Nunca modificar migraciones
  ya aplicadas en entornos compartidos.
- Mantener los controladores y componentes Livewire delgados. La lógica de
  calificación, autorización o emisión debe residir en servicios, acciones o
  clases de dominio con responsabilidades claras.
- Declarar relaciones, `$fillable`, `casts`, constantes o enums de estado en
  los modelos afectados. Evitar valores mágicos repetidos en vistas y acciones.
- Autorizar siempre en el servidor mediante políticas, permisos o gates. La
  visibilidad de una acción en Filament o Blade nunca sustituye una validación
  de autorización.
- Validar toda entrada en servidor. En exámenes, verificar que una opción
  pertenece a la pregunta recibida y que la inscripción pertenece al usuario
  autenticado.
- Ejecutar cambios sensibles de estado dentro de transacciones de base de
  datos, especialmente al finalizar un intento, recalcular la nota o cambiar
  una inscripción a completada.
- Usar observers solo para efectos secundarios consistentes (por ejemplo,
  notificaciones). No concentrar reglas de negocio complejas en observers.
- Mantener compatibilidad con los datos existentes; planificar explícitamente
  la migración de registros históricos antes de imponer restricciones nuevas.

## Calidad y validación obligatoria

Cada módulo debe incluir pruebas Pest antes de considerarse terminado:

- pruebas de modelos, servicios y reglas de negocio;
- pruebas de autorización por rol y por propiedad del recurso;
- pruebas de componentes Livewire o acciones Filament que cubran el flujo del
  usuario;
- pruebas de rutas protegidas y de escenarios inválidos.

Como mínimo, ejecutar antes de cerrar una etapa:

```bash
php artisan test
php artisan route:list
php artisan migrate:status
```

Además, verificar manualmente el flujo completo en una base de datos de prueba:
crear los datos, ejercer los permisos, probar errores esperados y confirmar que
las colas y el almacenamiento de archivos funcionan con la configuración del
entorno.

No avanzar a la siguiente etapa si hay pruebas fallidas, migraciones pendientes,
errores de autorización o reglas de negocio sin cubrir. Registrar cualquier
limitación conocida y solicitar una decisión si una regla funcional no está
definida.

## Límites de cambios

- No cambiar dependencias, versiones mayores, esquema global, diseño visual ni
  flujos no relacionados sin aprobación.
- Preservar las personalizaciones actuales de KATO-KI en el panel Filament y
  portal Livewire.
- No exponer contraseñas temporales, datos personales ni enlaces privados en
  logs, pruebas o mensajes de error.
- Los cambios deben ser pequeños, revisables y enfocados en una etapa.
