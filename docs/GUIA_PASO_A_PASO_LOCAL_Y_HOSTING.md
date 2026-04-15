# Guia Paso a Paso: Ejecutar el Proyecto y Subirlo a Hosting/Dominio

Esta guia esta pensada para alguien que inicia desde cero.
Sigue los pasos en orden.

---

## 1) Requisitos minimos

Tu companero debe tener instalado:

1. PHP 8.2 o superior
2. Composer
3. Node.js 18+ (mejor 20+)
4. MySQL (o MariaDB)
5. Git (opcional, si lo descargo en ZIP no es obligatorio)

### Verificar versiones

Abrir terminal y ejecutar:

```bash
php -v
composer -V
node -v
npm -v
mysql --version
```

Si alguno falla, primero instalarlo.

---

## 2) Preparar el proyecto en su PC

### Opcion A: si recibio ZIP

1. Descomprimir el ZIP.
2. Entrar a la carpeta del proyecto.

### Opcion B: si lo baja de GitHub

```bash
git clone https://github.com/chefft/backend-diversiones-aguirre.git
cd backend-diversiones-aguirre
```

---

## 3) Instalar dependencias

Dentro de la carpeta del proyecto:

```bash
composer install
npm install
```

---

## 4) Crear y configurar `.env`

1. Copiar archivo de entorno:

```bash
copy .env.example .env
```

En Mac/Linux:

```bash
cp .env.example .env
```

2. Abrir `.env` y confirmar:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=diversiones_aguirre
DB_USERNAME=root
DB_PASSWORD=
```

3. Si su usuario/password de MySQL son diferentes, cambiarlos aqui.

---

## 5) Crear base de datos

Crear una base vacia llamada `diversiones_aguirre`.

Puede hacerlo por phpMyAdmin o por consola SQL:

```sql
CREATE DATABASE diversiones_aguirre CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 6) Inicializar Laravel (clave, storage, tablas, seed)

Ejecutar:

```bash
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
```

Si da error de duplicados al hacer seed (por datos repetidos), usar:

```bash
php artisan migrate:fresh --seed
```

---

## 7) Levantar el proyecto (2 terminales)

### Terminal 1: Backend Laravel

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Terminal 2: Frontend Vite

```bash
npm run dev -- --host 127.0.0.1 --port 5173
```

### Abrir en navegador

Abrir:

`http://127.0.0.1:8000`

---

## 8) Donde poner modelos `.blend`, `.glb` e imagenes 360

Dentro del proyecto:

1. Fuentes Blender:
`storage/app/public/models/source/`

2. Modelos exportados `.glb`:
`storage/app/public/models/`

3. Imagenes panoramicas 360 (2:1):
`storage/app/public/panoramas/`

4. En base de datos usar rutas relativas:
- `models/rueda.glb`
- `panoramas/plaza-central.jpg`

---

## 9) Verlo en Cardboard (movil)

1. Abrir la app en movil (misma red o dominio publico).
2. Ir a tab `Panorama 360`.
3. Presionar `Activar Cardboard`.
4. Aceptar permiso de sensor/giroscopio.
5. Poner el telefono en visor Cardboard.

---

## 10) Errores comunes y solucion rapida

### Error: `Class ... not found`

```bash
composer dump-autoload
```

### Error de permisos en `storage` o `bootstrap/cache`

Dar permisos de escritura a esas carpetas.

### Error DB `Access denied`

Revisar usuario/password/host en `.env`.

### Cambie `.env` y no se refleja

```bash
php artisan config:clear
php artisan cache:clear
```

### Puerto 8000 ocupado

Cambiar puerto:

```bash
php artisan serve --port=8080
```

Y abrir `http://127.0.0.1:8080`.

---

## 11) Subir a hosting con dominio (opcion compartida: hPanel/cPanel)

Esta es la ruta mas comun para compartir con cliente sin VPS.

### 11.1 Archivos

1. Subir todo el proyecto al hosting (fuera de `public_html`), por ejemplo:
`/home/usuario/backend-diversiones-aguirre`

2. Hacer que el dominio apunte al directorio `public` de Laravel.
En hPanel/cPanel buscar "Document Root" y usar:

`/home/usuario/backend-diversiones-aguirre/public`

### 11.2 Configurar `.env` de produccion

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tu_db
DB_USERNAME=tu_user
DB_PASSWORD=tu_password
```

### 11.3 Comandos en servidor (SSH)

```bash
cd /home/usuario/backend-diversiones-aguirre
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan key:generate
php artisan storage:link
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 11.4 SSL

Activar SSL en panel del hosting (Lets Encrypt) para usar HTTPS.

---

## 12) Subir a VPS (Nginx + dominio + SSL)

Si quieren mas control/rendimiento.

### 12.1 Instalar stack

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip unzip git composer nodejs npm
```

### 12.2 Clonar proyecto

```bash
cd /var/www
sudo git clone https://github.com/chefft/backend-diversiones-aguirre.git
cd backend-diversiones-aguirre
composer install --no-dev --optimize-autoloader
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --force
```

### 12.3 Permisos

```bash
sudo chown -R www-data:www-data /var/www/backend-diversiones-aguirre
sudo chmod -R 775 /var/www/backend-diversiones-aguirre/storage /var/www/backend-diversiones-aguirre/bootstrap/cache
```

### 12.4 Nginx (server block)

Archivo:
`/etc/nginx/sites-available/backend-diversiones-aguirre`

```nginx
server {
    listen 80;
    server_name tudominio.com www.tudominio.com;
    root /var/www/backend-diversiones-aguirre/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Activar sitio:

```bash
sudo ln -s /etc/nginx/sites-available/backend-diversiones-aguirre /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 12.5 SSL con Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tudominio.com -d www.tudominio.com
```

---

## 13) Checklist final de produccion

1. `APP_DEBUG=false`
2. `APP_URL=https://tudominio.com`
3. Migraciones aplicadas
4. `storage:link` creado
5. Build frontend generado (`public/build`)
6. HTTPS activo
7. Backups de base de datos activos

---

## 14) Comandos utiles de mantenimiento

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

Si algo falla, compartir:

1. Captura del error completo
2. Contenido de `storage/logs/laravel.log`
3. Valor actual de `APP_ENV`, `APP_DEBUG` y `APP_URL` (sin mostrar passwords)
