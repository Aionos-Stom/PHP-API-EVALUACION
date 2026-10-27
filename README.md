# Productos y divisas API

API REST desarrollada con Laravel 13 y Eloquent para gestionar productos y registrar sus precios en diferentes divisas. Todas las respuestas utilizan JSON.

## Requisitos

- Docker y Docker Compose v2, o PHP 8.5.12 y Composer 2.10.3.
- Para ejecución nativa: extensiones PDO SQLite, mbstring, OpenSSL y XML.

## Instalación con Docker

Desde la carpeta del repositorio:

```bash
docker compose build
docker compose run --rm -v "$PWD:/app" -e COMPOSER_HOME=/tmp/composer -e COMPOSER_IPRESOLVE=4 api composer install --no-dev --prefer-dist --no-interaction --no-plugins
docker compose run --rm -v "$PWD:/app" api php setup.php
docker compose up -d --wait
```

La API estará disponible en `http://127.0.0.1:8088`. Para usar otro puerto:

```bash
PRODUCTOS_PORT=8090 docker compose up -d --wait
```

Para detener el servicio conservando los datos:

```bash
docker compose down
```

## Instalación nativa

```bash
composer install --no-dev --prefer-dist --no-interaction --no-plugins
php -c config/php.ini setup.php
php -c config/php.ini -S 127.0.0.1:8088 -t public public/index.php
```

`setup.php` genera la configuración privada, crea la base SQLite y ejecuta las migraciones y las semillas. Puede ejecutarse de nuevo sin borrar el catálogo ni reemplazar las credenciales existentes.

## Autenticación

Las rutas de productos requieren esta cabecera:

```http
Authorization: Bearer <token>
```

El token se genera durante la instalación y se guarda localmente en `storage/api-token.txt`. La configuración del servidor conserva su hash SHA-256. `GET /health` permite comprobar que la aplicación responde.

## Endpoints

| Método | Ruta | Descripción |
| --- | --- | --- |
| GET | `/products` | Listar productos |
| POST | `/products` | Crear un producto |
| GET | `/products/{id}` | Consultar un producto |
| PUT | `/products/{id}` | Actualizar todos los campos editables |
| DELETE | `/products/{id}` | Eliminar un producto y sus precios |
| GET | `/products/{id}/prices` | Consultar los precios registrados |
| POST | `/products/{id}/prices` | Registrar un precio en una divisa |

Los listados admiten `page` y `per_page`, con un máximo de 100 elementos por página.

## Ejemplos

Crear o actualizar un producto:

```json
{
  "name": "Teclado",
  "description": "Teclado mecánico compacto",
  "Price": "1500.50",
  "currency_id": 1,
  "tax_cost": "270.09",
  "manufacturing_cost": "700.00"
}
```

Registrar un precio:

```json
{
  "currency_id": 2,
  "price": "25.75"
}
```

Se conserva el campo `Price` de productos tal como aparece en el modelo proporcionado. El campo `price` en minúscula corresponde a los precios registrados por divisa. Los importes se devuelven como cadenas con dos decimales.

La semilla crea DOP, USD y EUR con tasas de demostración. Los precios se registran explícitamente; no se descargan cotizaciones ni se realizan conversiones automáticas.

## Documentación

- [Contrato de la API](docs/api.md): campos, validaciones, respuestas y códigos HTTP.
- [Colección Postman](docs/productos-divisas.postman_collection.json): las siete operaciones. Configura `base_url` y `api_token`; al crear un producto se guarda su identificador en `product_id`.

## Estructura

- `app/Models`: entidades y relaciones Eloquent.
- `app/Http/Requests`: validación de entradas y paginación.
- `app/Http/Controllers`: atención de solicitudes.
- `app/Services`: operaciones de escritura y transacciones.
- `app/Http/Resources`: representación JSON.
- `database/migrations`: tablas, claves foráneas y restricciones.
- `database/seeders`: divisas iniciales.

La aplicación valida campos permitidos, limita solicitudes y tamaño del cuerpo, utiliza consultas parametrizadas y oculta detalles internos en errores. La configuración incluida está destinada a ejecución local; un despliegue público requiere un servidor de producción y HTTPS.
