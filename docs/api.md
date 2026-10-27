# Contrato de la API

URL local inicial: `http://127.0.0.1:8088`. Si el puerto está ocupado, utiliza la URL que imprime el iniciador. Sin prefijo `/api`. Respuestas `application/json`, incluso errores y eliminación. Fechas y campos adicionales no solicitados no se incorporaron a las tablas de negocio.

## Autenticación

Todas las operaciones de productos requieren `Authorization: Bearer <token>`. La configuración inicial genera 32 bytes aleatorios y guarda su representación hexadecimal en `storage/api-token.txt`, con permisos 600. El servidor verifica SHA-256 contra `API_TOKEN_HASH`, mediante `hash_equals`. El token no se acepta por query ni cookie. `GET /health` comprueba acceso a la tabla de productos sin devolver datos.

## Campos

Producto:

| Campo | Entrada | Regla |
| --- | --- | --- |
| `id` | Solo salida | Entero autoincremental |
| `name` | Obligatoria | Texto no vacío, hasta 255 caracteres |
| `description` | Obligatoria | Texto no vacío, hasta 2000 caracteres |
| `Price` | Obligatoria | De 0 a 9999999999.99, máximo dos decimales |
| `currency_id` | Obligatoria | Entero de una divisa existente |
| `tax_cost` | Obligatoria | Mismo rango que `Price` |
| `manufacturing_cost` | Obligatoria | Mismo rango que `Price` |

Se recomienda enviar dinero como cadena (`"10.25"`). Se aceptan valores JSON numéricos si su representación satisface la validación. La respuesta siempre usa cadenas con dos posiciones. No se redondean silenciosamente cadenas con más decimales. No se aceptan importes negativos, exponentes en cadenas ni separadores de miles. `Price` es sensible a mayúsculas; `price` solo pertenece al registro de precios.

Divisa: `id` entero; `name` único; `symbol` texto de hasta 16 caracteres; `exchange_rate` decimal con seis posiciones. La semilla crea DOP, USD y EUR con tasas de muestra `1.000000`, `60.000000` y `65.000000`, expresadas como DOP por una unidad de la divisa. No son tasas actuales ni se descargan de Internet. La prueba no exige CRUD de divisas: se gestionan mediante semillas locales.

Precio adicional: `id` entero; `product_id` lo asigna el servidor desde la ruta; `currency_id` identifica una divisa existente; `price` sigue el mismo rango monetario. Se permite un registro por producto y divisa. Un segundo registro devuelve 409 y conserva el anterior.

El precio base y los precios registrados son importes explícitos independientes. No se crea automáticamente un registro en `product_prices` al crear el producto, no se suman impuestos al precio y no se recalculan importes al cambiar una tasa o la divisa base. El documento no define esas reglas. Se puede registrar la divisa base en `product_prices` con el mismo mecanismo.

## Recorrido de peticiones

1. `POST /products` con el cuerpo de ejemplo del README: 201, cabecera `Location: /products/{id}` y objeto bajo `data`.
2. `GET /products`: 200, `data` como lista, `links` y `meta` de paginación. Admite `page` (1–1000000) y `per_page` (1–100, predeterminado 15). Orden estable por `id` ascendente.
3. `GET /products/{id}`: 200, producto bajo `data`, con objeto `currency` embebido; 404 si no existe.
4. `PUT /products/{id}`: mismos seis campos obligatorios de creación. Reemplazo completo de los campos editables; no equivale a PATCH. Devuelve 200 y el producto actualizado. Un fallo de validación no modifica datos.
5. `POST /products/{id}/prices`: cuerpo `{"currency_id":2,"price":"25.75"}`; 201 y precio bajo `data`, con su divisa.
6. `GET /products/{id}/prices`: 200, solo los precios del producto indicado, paginados con las mismas reglas. Si el producto existe y no tiene precios, `data` es `[]`. Si no existe, 404.
7. `DELETE /products/{id}`: 200 con `{"message":"Producto eliminado."}`. La clave foránea elimina sus precios en cascada, conservando las divisas. Una segunda eliminación devuelve 404.

Ejemplo de respuesta de producto:

```json
{
  "data": {
    "id": 1,
    "name": "Teclado",
    "description": "Teclado mecánico compacto",
    "Price": "1500.50",
    "currency_id": 1,
    "tax_cost": "270.09",
    "manufacturing_cost": "700.00",
    "currency": {
      "id": 1,
      "name": "DOP",
      "symbol": "RD$",
      "exchange_rate": "1.000000"
    }
  }
}
```

## Errores y límites

| HTTP | Significado |
| --- | --- |
| 400 | JSON mal formado, array o escalar donde se espera objeto |
| 401 | Token ausente o incorrecto |
| 404 | Producto o ruta inexistente |
| 405 | Método no implementado, por ejemplo PATCH |
| 409 | Precio duplicado para producto y divisa |
| 413 | Más de 16 KiB de cuerpo, también sin `Content-Length` |
| 415 | POST/PUT sin tipo `application/json` |
| 422 | Campo obligatorio ausente, inválido o no permitido |
| 429 | Más de 60 peticiones por minuto e IP; incluye `Retry-After` |
| 500 | Fallo interno sin SQL, trazas ni secretos |
| 503 | Configuración privada ausente/inválida o entorno no local/pruebas |

Ejemplo de validación: `{"message":"Datos de entrada inválidos.","errors":{"name":["…"]}}`. El texto de cada campo procede del validador de Laravel; el contrato estable es el código HTTP y las claves del objeto. Los demás errores solo incluyen `message`.

La autenticación se ejecuta antes del enlace de modelos, por lo que sin token no se revela si un identificador existe. Los parámetros desconocidos se rechazan en las solicitudes de creación, actualización y paginación. No se ofrecen CORS de navegador, cookies de sesión ni subidas de archivos.
