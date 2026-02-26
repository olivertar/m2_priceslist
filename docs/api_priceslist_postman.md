# Orangecat PricesList REST API - Postman Guide

Esta guía detalla cómo consumir los endpoints del módulo `Orangecat_PricesList` para gestionar listas de precios, sus productos y asociaciones con compañías.

> [!IMPORTANT]
> **Autenticación:**
> Todos los endpoints requieren un Bearer Token con permisos administrativos (`Orangecat_PricesList::priceslist`).
> En Postman, ve a la pestaña **Authorization**, selecciona **Bearer Token** y pega tu token.

---

## 1. Obtener Token de Administrador

### Obtener el Token

Este endpoint permite generar un **Bearer Token** administrativo. Debes enviarlo en el encabezado `Authorization` de todas las llamadas posteriores.

* **Método:** `POST`
* **Endpoint:** `/rest/V1/integration/admin/token`
* **Body (raw JSON):**
```json
{
  "username": "tu_usuario",
  "password": "tu_password"
}
```

---

## 2. Gestión de Listas de Precios (CRUD)

### Crear Lista
Crea una nueva lista de precios.

* **Método:** `POST`
* **Endpoint:** `/rest/V1/priceslist`
* **Body (raw JSON):**
```json
{
  "priceList": {
    "name": "Lista de Verano 2026",
    "code": "SUMMER26",
    "is_active": true,
    "description": "Precios especiales para la temporada de verano",
    "start_date": "2026-06-01 00:00:00",
    "end_date": "2026-08-31 23:59:59"
  }
}
```

### Actualizar Lista (por ID)
Actualiza una lista de precios existente utilizando su `entity_id`.

* **Método:** `PUT` (También se puede usar `POST` a `/rest/V1/priceslist` incluyendo el ID)
* **Endpoint:** `/rest/V1/priceslist/:entityId`
* **Body (raw JSON):**
```json
{
  "priceList": {
    "entity_id": 1,
    "name": "Lista Actualizada",
    "is_active": true
  }
}
```

### Obtener por ID
Recupera los detalles de una lista de precios específica usando su ID interno (`entity_id`). 
**Nota:** El parámetro debe ser un número entero.

* **Método:** `GET`
* **Endpoint:** `/rest/V1/priceslist/:entityId`
* **Ejemplo:** `/rest/V1/priceslist/1`

### Obtener por Código
Recupera los detalles de una lista de precios específica usando su código identificador único (String).
**Importante:** Debes incluir `/code/` en la URL.

* **Método:** `GET`
* **Endpoint:** `/rest/V1/priceslist/code/:code`
* **Ejemplo:** `/rest/V1/priceslist/code/SUMMER26`

### Eliminar por ID
Elimina la lista de precios utilizando su ID interno. Esto borra sus precios asociados.

* **Método:** `DELETE`
* **Endpoint:** `/rest/V1/priceslist/:entityId`
* **Ejemplo:** `/rest/V1/priceslist/1`

### Eliminar por Código
Elimina físicamente la lista de precios basándose en su código.

* **Método:** `DELETE`
* **Endpoint:** `/rest/V1/priceslist/code/:code`
* **Ejemplo:** `/rest/V1/priceslist/code/SUMMER26`

### Buscar Listas (SearchCriteria)
Permite buscar y filtrar colecciones de listas de precios utilizando el estándar `searchCriteria` de Magento.

* **Método:** `GET`
* **Endpoint:** `/rest/V1/priceslist/search`
* **Ejemplo:** `/rest/V1/priceslist/search?searchCriteria[filter_groups][0][filters][0][field]=is_active&searchCriteria[filter_groups][0][filters][0][value]=1`

---

## 3. Gestión de Precios en la Lista

### Listar Precios de una Lista
Devuelve todos los productos (SKUs), montos y cantidades mínimas configurados dentro de una lista de precios específica.

* **Método:** `GET`
* **Endpoint:** `/rest/V1/priceslist/:priceListCode/prices`
* **Ejemplo:** `/rest/V1/priceslist/SUMMER26/prices`

### Añadir o Actualizar Precios (Bulk)
Permite gestionar los precios de forma masiva. Si el `sku` y la `qty` ya existen, se **actualiza** el precio; si no, se **crea** un nuevo nivel de precio (tier).

* **Método:** `POST`
* **Endpoint:** `/rest/V1/priceslist/:priceListCode/prices`
* **Ejemplo:** `/rest/V1/priceslist/SUMMER26/prices`
* **Body (raw JSON):**
```json
{
  "prices": [
    {
      "sku": "24-MB01",
      "discount_type": "fixed_price",
      "amount": 45.00,
      "qty": 1
    },
    {
      "sku": "24-MB02",
      "discount_type": "percentage",
      "amount": 10.00,
      "qty": 5
    }
  ]
}
```

### Eliminar Precios por SKU
Elimina las reglas de precios asociadas a los SKUs proporcionados dentro de la lista especificada.

* **Método:** `POST`
* **Endpoint:** `/rest/V1/priceslist/:priceListCode/prices/remove`
* **Ejemplo:** `/rest/V1/priceslist/SUMMER26/prices/remove`
* **Body (raw JSON):**
```json
{
  "skus": ["24-MB01", "24-MB02"]
}
```

---

## 4. Asociación con Compañías

### Listar Compañías Asociadas
Obtiene el listado de todas las compañías que tienen asignada esta lista de precios.

* **Método:** `GET`
* **Endpoint:** `/rest/V1/priceslist/:priceListCode/companies`
* **Ejemplo:** `/rest/V1/priceslist/SUMMER26/companies`

### Asociar Compañía o Actualizar Prioridad
Vincula una lista de precios a una compañía o actualiza su `priority` si ya existe el vínculo.

* **Método:** `POST`
* **Endpoint:** `/rest/V1/priceslist/:priceListCode/companies`
* **Ejemplo:** `/rest/V1/priceslist/SUMMER26/companies`
* **Body (raw JSON):**
```json
{
  "companyId": 12,
  "priority": 10
}
```

### Eliminar Asociación
Desvincula la lista de precios de la compañía especificada.

* **Método:** `DELETE`
* **Endpoint:** `/rest/V1/priceslist/:priceListCode/companies/:companyId`
* **Ejemplo:** `/rest/V1/priceslist/SUMMER26/companies/12`
