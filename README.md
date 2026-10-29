
## Qué necesitas tener instalado

- Docker Desktop
- WSL2 (si estás en Windows, corre todo desde ahí y no desde `/mnt/c/...`, porque se pone lento)
- Git
- Composer (si no lo tienes, mira la nota de abajo)

## Cómo correrlo

```bash
# 1. Clonar el repositorio
git clone https://github.com/manuela-aguirre/Avance-proyectoSGE.git
cd Avance-proyectoSGE

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

Con eso ya se entra desde el navegador a `http://localhost`. Se puede crear una cuenta en `/register` o entrar con el usuario que crea el seeder: `test@example.com` / `password`.

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
./vendor/bin/sail down                     # apagar el entorno
```


## Tecnologías

Laravel · Laravel Sail · Docker · MySQL · Blade · Tailwind CSS · Vite · Git / GitHub
