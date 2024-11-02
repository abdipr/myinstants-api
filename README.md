# MyInstants REST API

A RESTful API for scraping and retrieving sound data from the [MyInstants](https://www.myinstants.com) website. This API provides endpoints for retrieving information about sounds, including titles, URLs, descriptions, tags, favorites, views, and uploader details.

## Table of Contents

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Endpoints](#endpoints)
- [Error Handling](#error-handling)
- [Examples](#examples)
- [Contributing](#contributing)
- [License](#license)

---

## Introduction

The MyInstants REST API is designed to provide a structured way to retrieve sound data from the MyInstants website. This API is suitable for applications that need to access sound information dynamically without direct interactions with the MyInstants site.

## Requirements

- PHP 7.4 or higher
- [simple_html_dom.php](https://simplehtmldom.sourceforge.io/) library for HTML parsing
- Internet access for scraping the MyInstants website

## Installation

1. Clone the repository to your server:
    ```bash
    git clone https://github.com/yourusername/myinstants-api.git
    cd myinstants-api
    ```

2. Download and include `simple_html_dom.php` in the project directory.

3. Set up your server to serve PHP files (e.g., Apache or Nginx).

## Endpoints

| Request                            | Response                  | Parameter |
| :--------------------------------- | :------------------------ | :-------: |
| `GET /trending`                    | Trending based region     |     q     |
| `GET /search`                      | Search a sound            |     q     |
| `GET /detail`                      | The sound details         |     id    |
| `GET /recent`                      | Recently uploaded sounds |           |
| `GET /best`                        | Best of all time sounds   |           |
| `GET /uploaded`                    | User's uploaded sounds    |  username |
| `GET /favorites`                   | User's favorite sounds    |  username |

### Response Parameters
  - `id`: Unique ID of the sound.
  - `url`: Direct URL to the sound page.
  - `title`: Title of the sound.
  - `mp3`: Direct URL to the MP3 file.
  - `description`: Description of the sound.
  - `tags`: Array of tags associated with the sound.
  - `favorites`: Number of users who favorited the sound.
  - `views`: View count of the sound.
  - `uploader`: Details about the uploader, including the uploader’s name and profile URL.

## Error Handling

All errors return JSON objects with a `status` code and `message` explaining the issue.

- **404 Error**:
    - When the page is not found or the query parameter is missing.
    ```json
    {
      "status": "404",
      "author": "abdiputranar",
      "message": "Page not found"
    }
    ```
  
## Examples

### Example 1: Get Sound by ID

Retrieve details of a sound using its ID:
```http
GET /sound?id=akh-26815
```

### Example 2: Search Sounds by Query

Search for sounds using a specific query string:
```http
GET /search?q=laugh
```

## Contributing

Contributions are welcome! To contribute:

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature-name`.
3. Commit your changes: `git commit -m 'Add feature'`.
4. Push to the branch: `git push origin feature-name`.
5. Submit a pull request.

## License

This project is licensed under the `MIT License`. See the [LICENSE](https://github.com/abdipr/myinstants-api/blob/main/LICENSE) file for more information.
