<p align="center"><img src="https://www.myinstants.com/media/apple-touch-icon-114x114.png" alt="MyInstants"></p>
<h1 align="center">MyInstants REST API</h1>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/Vercel-Deployed-000000?logo=vercel&logoColor=white" alt="Deployed on Vercel">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License MIT">
  <img src="https://img.shields.io/badge/PRs-welcome-brightgreen.svg" alt="PRs Welcome">
</p>

<p align="center">A RESTful API for scraping and retrieving sound data from the <a href="https://www.myinstants.com" target="_blank">MyInstants</a> website. This API provides endpoints for retrieving information about sounds, including titles, URLs, descriptions, tags, favorites, views, and uploader details.</p>

## ✨ Features

- ⚡ **Ultra Fast**: Powered by Vercel Edge Caching (`s-maxage=3600`) for ~0ms response times on cached requests.
- 🚀 **Serverless Ready**: Native deployment to Vercel without tweaking. Uses separate serverless functions for maximum efficiency.
- 🌐 **CORS Enabled**: Ready to be consumed directly from frontend web applications (React, Vue, etc) without cross-origin issues.
- 🎯 **Reliable Error Handling**: Returns proper HTTP status codes (e.g., 404, 400) instead of just 200 OK.

## Table of Contents

- [Features](#-features)
- [Getting Started](#-getting-started)
  - [Requirements](#requirements)
  - [Installation](#installation)
- [Reference](#%EF%B8%8F-reference)
  - [Endpoints](#endpoints)
  - [Request Parameters](#request-parameters)
  - [Response Example](#response-example)
- [Error Handling](#-error-handling)
- [Examples](#-examples)
- [Contributing](#-contributing)
- [Support](#-support)
- [License & Disclaimer](#%EF%B8%8F-license)

## 🚀 Getting Started

### Requirements

- PHP 7.4 or higher
- [simple_html_dom.php](https://simplehtmldom.sourceforge.io/) library for HTML parsing
- `curl` extension enabled in `php.ini`

### Installation

1. Clone the repository to your server:

   ```bash
   git clone https://github.com/abdipr/myinstants-api.git
   cd myinstants-api
   ```

2. Download and include `simple_html_dom.php` in the project directory.

3. **Local Development (No Apache/Nginx required)**:
   You can run the API locally using PHP's built-in web server. This project includes a `router.php` file that perfectly simulates Vercel's serverless routing environment, allowing you to access endpoints without the `.php` extension.

   ```bash
   php -S localhost:8000 router.php
   ```

   Now you can access the API locally (e.g., `http://localhost:8000/best?q=id`).

4. **Deploy to Vercel**:
   Deploying is simple. Click the button below to deploy this repository directly to your Vercel account.<br>
   [![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https%3A%2F%2Fgithub.com%2Fabdipr%2Fmyinstants-api%2F&redirect-url=https%3A%2F%2Fgithub.com%2Fabdipr%2Fmyinstants-api%2F)

## ❇️ Reference

### Endpoints

Base URL: https://myinstants-api.vercel.app

| Request          | Response                 | Parameter  |
| :--------------- | :----------------------- | :--------: |
| `GET /trending`  | Trending based region    | `q`, `page` |
| `GET /search`    | Search a sound           | `q`, `page` |
| `GET /detail`    | The sound details        |    `id`    |
| `GET /recent`    | Recently uploaded sounds |   `page`   |
| `GET /best`      | Best of all time sounds  | `q`, `page` |
| `GET /uploaded`  | User's uploaded sounds   | `username`, `page` |
| `GET /favorites` | User's favorite sounds   | `username`, `page` |

_All list endpoints (`/trending`, `/search`, `/recent`, `/best`, `/uploaded`, `/favorites`) support pagination via `?page=N` (mirrors `https://www.myinstants.com/en/index/sy/?page=4`, default `1`) and duration probing via `?with_duration=1&min_duration=2&max_duration=6`._

### Request Parameters

|   Parameter    | Description                                              |
| :------------: | :------------------------------------------------------- |
|      `q`       | Search query or region                                   |
|   `username`   | User's username                                          |
|      `id`      | Sound's Unique ID                                        |
|     `page`     | Page number (>= 1, default `1`). Example: `?page=4`      |
| `with_duration`| `1` to include MP3 `duration` (seconds) per sound        |
| `min_duration` | Only keep sounds >= N seconds (implies `with_duration`)  |
| `max_duration` | Only keep sounds <= N seconds (implies `with_duration`)  |

_Note: `myinstants.com` exposes no durations in its HTML, so `with_duration` / `min_duration` / `max_duration` probe each MP3 and parse the MPEG frame headers. Lists stay fast by default; responses are slower (parallel fetch, best-effort, `duration: null` when undetectable) only when duration params are used._

### Response Example

A typical successful response (HTTP 200) will return a JSON object like this:

```json
{
  "status": 200,
  "author": "abdipr",
  "page": 4,
  "count": 30,
  "total_pages": 50,
  "has_next": true,
  "data": [
    {
      "id": "vine-boom-sound-70972",
      "title": "VINE BOOM SOUND",
      "url": "https://www.myinstants.com/en/instant/vine-boom-sound-70972/",
      "mp3": "https://www.myinstants.com/media/sounds/vine-boom.mp3",
      "duration": 1.42
    }
  ]
}
```

_`page` / `count` / `total_pages` / `has_next` are returned by all list endpoints (`total_pages` is parsed from the upstream `Page X of Y` title, `null` when undetectable). `duration` (seconds) only appears when `with_duration=1` or `min_duration` / `max_duration` is used._

_Note: For the `/detail` endpoint, the `data` object will contain extra fields like `description`, `tags`, `favorites`, `views`, and `uploader` (plus `duration` when `?with_duration=1` is passed)._

## 💥 Error Handling

All errors return JSON objects with an appropriate HTTP status code (e.g., 404, 400) and a `message` explaining the issue.

- **404 Error**:
  - When the page is not found or an invalid endpoint is accessed.
  ```json
  {
    "status": 404,
    "author": "abdipr",
    "message": "Endpoint not found"
  }
  ```

## 🌐 Examples

### Example 1: Get Trending Sounds by Region

```http
GET https://myinstants-api.vercel.app/trending?q=id
```

### Example 2: Search Sounds by Query

```http
GET https://myinstants-api.vercel.app/search?q=laugh
```

### Example 3: Get Sound Details by ID

```http
GET https://myinstants-api.vercel.app/detail?id=akh-26815
```

### Example 4: Get Recently Uploaded Sounds

```http
GET https://myinstants-api.vercel.app/recent
```

### Example 5: Get Best of All Time Sounds

Retrieve a list of the most popular sounds of all time based on a specified region:

```http
GET https://myinstants-api.vercel.app/best?q=id
```

### Example 6: Get User's Uploaded Sounds

```http
GET https://myinstants-api.vercel.app/uploaded?username=hellmouz
```

### Example 7: Get User's Favorite Sounds

```http
GET https://myinstants-api.vercel.app/favorites?username=hellmouz
```

### Example 8: Paginate Any List (e.g. Trending Syria, Page 4)

Mirrors https://www.myinstants.com/en/index/sy/?page=4 — page 1 is the default and returns the same shape as before:

```http
GET https://myinstants-api.vercel.app/trending?q=sy&page=4
GET https://myinstants-api.vercel.app/search?q=laugh&page=2
GET https://myinstants-api.vercel.app/recent?page=2
```

### Example 9: Only 2–6 Second Sounds

```http
GET https://myinstants-api.vercel.app/search?q=laugh&min_duration=2&max_duration=6
GET https://myinstants-api.vercel.app/trending?q=sy&page=4&min_duration=2&max_duration=6
GET https://myinstants-api.vercel.app/search?q=laugh&with_duration=1
```

## 🌱 Contributing

Contributions are welcome! To contribute:

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature-name`.
3. Commit your changes: `git commit -m 'Add feature'`.
4. Push to the branch: `git push origin feature-name`.
5. Submit a pull request.

## ✨ Support

If you like this project, please star on this repository, thank you ⭐<br>
You can support me by:<br>
<a href="https://trakteer.id/abdipr" target="_blank"><img id="wse-buttons-preview" src="https://cdn.trakteer.id/images/embed/trbtn-red-1.png?date=18-11-2023" height="40" style="border: 0px; height: 40px;" alt="Trakteer Saya"></a>
<a href="https://saweria.co/abdipr" target="_blank"><img height="42" src="https://files.catbox.moe/fwpsve.png"></a>
<a href="https://www.buymeacoffee.com/abdipr" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 40px !important;width: auto !important;" ></a>

### Star History

<a href="https://www.star-history.com/?repos=abdipr%2Fmyinstants-api&type=date&legend=top-left">
 <picture>
   <source media="(prefers-color-scheme: dark)" srcset="https://api.star-history.com/chart?repos=abdipr/myinstants-api&type=date&theme=dark&legend=top-left&sealed_token=d8-oCRjEm0gdKE6IHODgty_02DfU0SBhQ6-JKph5qyufokc7Yl6jjX-xxl8ppYXzquxu-yC7c-dDkalFs2CDpDfStsFDUb6p_cw_vtxA-iUwKFWg1adHgQ" />
   <source media="(prefers-color-scheme: light)" srcset="https://api.star-history.com/chart?repos=abdipr/myinstants-api&type=date&legend=top-left&sealed_token=d8-oCRjEm0gdKE6IHODgty_02DfU0SBhQ6-JKph5qyufokc7Yl6jjX-xxl8ppYXzquxu-yC7c-dDkalFs2CDpDfStsFDUb6p_cw_vtxA-iUwKFWg1adHgQ" />
   <img alt="Star History Chart" src="https://api.star-history.com/chart?repos=abdipr/myinstants-api&type=date&legend=top-left&sealed_token=d8-oCRjEm0gdKE6IHODgty_02DfU0SBhQ6-JKph5qyufokc7Yl6jjX-xxl8ppYXzquxu-yC7c-dDkalFs2CDpDfStsFDUb6p_cw_vtxA-iUwKFWg1adHgQ" />
 </picture>
</a>

## ⚖️ License

This project is licensed under the `MIT License`. See the [LICENSE](https://github.com/abdipr/myinstants-api/blob/main/LICENSE) file for more information.

## ⚠️ Disclaimer

The sounds contained in this API are obtained from the original [MyInstants](https://www.myinstants.com) website by web scraping. Developers using this API must follow the applicable regulations by mentioning this project or the official owner in their projects and are prohibited from abusing this API for personal benefits.

[⬆️ Back to Top](#myinstants-rest-api)
