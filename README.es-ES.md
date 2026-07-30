

<p align="center"><img src="https://www.myinstants.com/media/apple-touch-icon-114x114.png" alt="MyInstants"></p>
<h1 align="center">MyInstants REST API</h1>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white" alt="Versión de PHP">
  <img src="https://img.shields.io/badge/Vercel-Deployed-000000?logo=vercel&logoColor=white" alt="Desplegado en Vercel">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="Licencia MIT">
  <img src="https://img.shields.io/badge/PRs-welcome-brightgreen.svg" alt="PRs Bienvenidas">
</p>

<p align="center">Una API RESTful para extraer y obtener datos de sonidos desde el sitio web <a href="https://www.myinstants.com" target="_blank">MyInstants</a>. Esta API proporciona endpoints para recuperar información sobre sonidos, incluyendo títulos, URLs, descripciones, etiquetas, favoritos, vistas y detalles del subidor.</p>

## ✨ Características

- ⚡ **Ultra Rápida**: Impulsada por Vercel Edge Caching (`s-maxage=3600`) para tiempos de respuesta de ~0ms en solicitudes con caché.
- 🚀 **Listo para Serverless**: Despliegue nativo en Vercel sin ajustes. Utiliza funciones serverless separadas para máxima eficiencia.
- 🌐 **CORS Habilitado**: Listo para ser consumido directamente desde aplicaciones web frontend (React, Vue, etc.) sin problemas de origen cruzado.
- 🎯 **Manejo de Errores Confiable**: Devuelve códigos de estado HTTP adecuados (por ejemplo, 404, 400) en lugar de solo 200 OK.

## Tabla de Contenidos

- [Características](#-features)
- [Primeros Pasos](#-getting-started)
  - [Requisitos](#requirements)
  - [Instalación](#installation)
- [Referencia](#%EF%B8%8F-reference)
  - [Endpoints](#endpoints)
  - [Parámetros de Solicitud](#request-parameters)
  - [Ejemplo de Respuesta](#response-example)
- [Manejo de Errores](#-error-handling)
- [Ejemplos](#-examples)
- [Contribuir](#-contributing)
- [Soporte](#-support)
- [Licencia y Descargo de Responsabilidad](#%EF%B8%8F-license)

## 🚀 Primeros Pasos

### Requisitos

- PHP 7.4 o superior
- Biblioteca [simple_html_dom.php](https://simplehtmldom.sourceforge.io/) para análisis HTML
- Extensión `curl` habilitada en `php.ini`

### Instalación

1. Clona el repositorio en tu servidor:

   ```bash
   git clone https://github.com/abdipr/myinstants-api.git
   cd myinstants-api
   ```

2. Descarga e incluye `simple_html_dom.php` en el directorio del proyecto.

3. **Desarrollo Local (No se requiere Apache/Nginx)**:
   Puedes ejecutar la API localmente utilizando el servidor web integrado de PHP. Este proyecto incluye un archivo `router.php` que simula perfectamente el entorno de enrutamiento serverless de Vercel, permitiéndote acceder a los endpoints sin la extensión `.php`.

   ```bash
   php -S localhost:8000 router.php
   ```

   Ahora puedes acceder a la API localmente (por ejemplo, `http://localhost:8000/best?q=id`).

4. **Desplegar en Vercel**:
   Desplegar es sencillo. Haz clic en el botón de abajo para desplegar este repositorio directamente en tu cuenta de Vercel.<br>
   [![Desplegar con Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https%3A%2F%2Fgithub.com%2Fabdipr%2Fmyinstants-api%2F&redirect-url=https%3A%2F%2Fgithub.com%2Fabdipr%2Fmyinstants-api%2F)

## ❇️ Referencia

### Endpoints

URL Base: https://myinstants-api.vercel.app

| Petición          | Respuesta                 | Parámetro  |
| :--------------- | :----------------------- | :--------: |
| `GET /trending`  | Tendencias por región    |    `q`     |
| `GET /search`    | Buscar un sonido         |    `q`     |
| `GET /detail`    | Detalles del sonido      |    `id`    |
| `GET /recent`    | Sonidos subidos recientemente |            |
| `GET /best`      | Mejores sonidos de todos los tiempos |    `q`     |
| `GET /uploaded`  | Sonidos subidos por el usuario | `username` |
| `GET /favorites` | Sonidos favoritos del usuario | `username` |

### Parámetros de Solicitud

| Parámetro  | Descripción            |
| :--------: | :--------------------- |
|    `q`     | Consulta de búsqueda o región |
| `username` | Nombre de usuario        |
|    `id`    | ID Único del sonido      |

### Ejemplo de Respuesta

Una respuesta exitosa típica (HTTP 200) devolverá un objeto JSON como este:

```json
{
  "status": 200,
  "author": "abdipr",
  "data": [
    {
      "id": "vine-boom-sound-70972",
      "title": "VINE BOOM SOUND",
      "url": "https://www.myinstants.com/en/instant/vine-boom-sound-70972/",
      "mp3": "https://www.myinstants.com/media/sounds/vine-boom.mp3"
    }
  ]
}
```

_Nota: Para el endpoint `/detail`, el objeto `data` contendrá campos adicionales como `description`, `tags`, `favorites`, `views` y `uploader`._

## 💥 Manejo de Errores

Todos los errores devuelven objetos JSON con un código de estado HTTP apropiado (por ejemplo, 404, 400) y un `message` que explica el problema.

- **Error 404**:
  - Cuando no se encuentra la página o se accede a un endpoint inválido.
  ```json
  {
    "status": 404,
    "author": "abdipr",
    "message": "Endpoint not found"
  }
  ```

## 🌐 Ejemplos

### Ejemplo 1: Obtener sonidos en tendencia por región

```http
GET https://myinstants-api.vercel.app/trending?q=id
```

### Ejemplo 2: Buscar sonidos por consulta

```http
GET https://myinstants-api.vercel.app/search?q=laugh
```

### Ejemplo 3: Obtener detalles de un sonido por ID

```http
GET https://myinstants-api.vercel.app/detail?id=akh-26815
```

### Ejemplo 4: Obtener sonidos subidos recientemente

```http
GET https://myinstants-api.vercel.app/recent
```

### Ejemplo 5: Obtener los mejores sonidos de todos los tiempos

Obtén una lista de los sonidos más populares de todos los tiempos basada en una región especificada:

```http
GET https://myinstants-api.vercel.app/best?q=id
```

### Ejemplo 6: Obtener sonidos subidos por un usuario

```http
GET https://myinstants-api.vercel.app/uploaded?username=hellmouz
```

### Ejemplo 7: Obtener sonidos favoritos de un usuario

```http
GET https://myinstants-api.vercel.app/favorites?username=hellmouz
```

## 🌱 Contribuir

¡Las contribuciones son bienvenidas! Para contribuir:

1. Haz un fork del repositorio.
2. Crea una rama de características: `git checkout -b feature-name`.
3. Confirma tus cambios: `git commit -m 'Agregar característica'`.
4. Publica en la rama: `git push origin feature-name`.
5. Envía una pull request.

## ✨ Soporte

Si te gusta este proyecto, por favor dale una estrella en este repositorio, gracias ⭐<br>
Puedes apoyarme mediante:<br>
<a href="https://trakteer.id/abdipr" target="_blank"><img id="wse-buttons-preview" src="https://cdn.trakteer.id/images/embed/trbtn-red-1.png?date=18-11-2023" height="40" style="border: 0px; height: 40px;" alt="Trakteer Saya"></a>
<a href="https://saweria.co/abdipr" target="_blank"><img height="42" src="https://files.catbox.moe/fwpsve.png"></a>
<a href="https://www.buymeacoffee.com/abdipr" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 40px !important;width: auto !important;" ></a>

## ⚖️ Licencia

Este proyecto está licenciado bajo la `MIT License`. Consulta el archivo [LICENSE](https://github.com/abdipr/myinstants-api/blob/main/LICENSE) para más información.

## ⚠️ Descargo de Responsabilidad

Los sonidos contenidos en esta API se obtienen del sitio web original [MyInstants](https://www.myinstants.com) mediante web scraping. Los desarrolladores que utilicen esta API deben cumplir con las normativas aplicables mencionando este proyecto o al propietario oficial en sus proyectos, y está prohibido abusar de esta API para beneficios personales.

[⬆️ Volver al Inicio](#myinstants-rest-api)
