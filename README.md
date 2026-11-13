
## Qué necesitas tener instalado

- Docker Desktop
- WSL2 (si estás en Windows, corre todo desde ahí y no desde `/mnt/c/...`, porque se pone lento)
- Git
- Composer (si no lo tienes, mira la nota de abajo)

## Cómo correrlo

```bash
# 1. Clonar el repositorio
git clone https://github.com/manuela-aguirre/ModuloBibliotecaProyecto.git
cd ModuloBibliotecaProyecto

# 2. Copiar las variables de entorno
cp .env.example .env

# 3. Instalar las dependencias de PHP
composer install

# 4. Levantar los contenedores (Laravel + MySQL)
./vendor/bin/sail up -d

# 5. Generar la key de la app
./vendor/bin/sail artisan key:generate

# 6. Crear las tablas y cargar los datos de prueba
./vendor/bin/sail artisan migrate:fresh --seed

# 7. Instalar y compilar el frontend (Tailwind y Vite)
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

Con eso ya se entra desde el navegador a `http://localhost`. Se puede crear una cuenta en `/register` o entrar con el usuario que crea el seeder: `test@example.com` / `password` usuario admin.

estudiante@example.com -> Estudiante
rosa.reservas@example.com  -> Otro usuario Estudiante



> Si el puerto 80 está ocupado, cambia `APP_PORT` en el `.env` (por ejemplo `APP_PORT=8080`) y reinicia con `./vendor/bin/sail down` y `./vendor/bin/sail up -d`.
>
> Si no tienes Composer, el paso 3 se puede hacer con Docker:
> ```bash
> docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs
> ```


```bash
./vendor/bin/sail artisan migrate:status   # estado de las migraciones
./vendor/bin/sail artisan tinker           # probar consultas y relaciones
./vendor/bin/sail artisan route:list       # rutas registradas
./vendor/bin/sail artisan kardex:inicializar # saldo inicial para bases existentes
./vendor/bin/sail down                     # apagar el entorno
```

El kardex registra los ejemplares disponibles, sus reservas, préstamos, devoluciones y ajustes. Tras aplicar las migraciones a una base existente, ejecuta `kardex:inicializar` una sola vez; el comando es idempotente y no reconstruye movimientos históricos anteriores. Desde Reportes se pueden descargar préstamos, inventario y reservas en CSV, PDF o Excel (.xlsx).

Las multas usan el SMLMV configurado en `BIBLIOTECA_SMLMV` (SMDLV = SMLMV / 30), descuentan domingos, festivos nacionales de Colombia y fechas locales de cierre en `BIBLIOTECA_DIAS_CIERRE` (fechas `YYYY-MM-DD` separadas por comas). La biblioteca presta servicio de lunes a sábado. La colección General cobra 1 % del SMDLV por día de servicio; Reserva y Otras colecciones, 2 %.

El catálogo público permite buscar por varios términos, filtrar por autor, área/género, tema y disponibilidad, y mostrar enlaces a recursos digitales autorizados que se configuran en la ficha de cada libro.

El módulo administrativo de adquisiciones permite registrar proveedores reutilizables y solicitudes por título existente, costo, cantidad, colección y ubicación. Al recibir un pedido aprobado, se crean automáticamente los ejemplares con códigos de barras `ADQ{id}-{número}`. El historial de cada ejemplar registra altas, préstamos, devoluciones, reservas, cambios de estado/ubicación, reparaciones, pérdidas y eliminaciones.

La lista de espera funciona por orden de llegada. Cuando vuelve a estar disponible un ejemplar, se crea una reserva automática para el siguiente usuario elegible, con fecha de retiro según `BIBLIOTECA_DIAS_RETIRO_LISTA_ESPERA` (por defecto, 2 días y limitada por `BIBLIOTECA_DIAS_MAX_RESERVA`). El usuario consulta asignaciones desde «Mis reservas».

El dashboard administrativo muestra préstamos por título y carrera de los últimos 30 días, préstamos vencidos actuales y títulos con lista de espera, junto con los indicadores operativos existentes. La importación de inventario acepta CSV, XLSX y PDF digital con las 20 columnas de ejemplar publicadas en la plantilla; no procesa PDFs escaneados. Cada fila representa una copia física. Los códigos de barras duplicados se omiten y reportan; si el libro ya existe por ISBN o por título y editorial, la copia nueva se asocia a ese registro. Los libros nuevos se clasifican inicialmente como «Sin clasificar».


## Tecnologías

Laravel · Laravel Sail · Docker · MySQL · Blade · Tailwind CSS · Vite · Git / GitHub
