# String Analyzer API

A RESTful API service that analyzes strings and stores their computed properties.  
Built with **Laravel 12**, deployed with **Laravel Cloud**, and developed in **PHPStorm**.

---

##  Features

For every analyzed string, the API computes and stores:

- **length** → number of characters
- **is_palindrome** → true if the string reads the same forwards and backwards
- **unique_characters** → count of distinct characters
- **word_count** → number of words separated by whitespace
- **sha256_hash** → SHA-256 hash of the string
- **character_frequency_map** → mapping of each character to its frequency

---

## ⚙️ Tech Stack

- PHP 8.4
- Laravel 12
-  SQLite for local testing
- Laravel Herd (for local development)
- Laravel Cloud (for deployment)

---

##  API Endpoints

### Create / Analyze String
**POST** `/api/strings`

#### Request Body
```json
{
  "value": "madam"
}
Response (201 Created)
json
Copy code
{
  "id": "765cc52b3dbc1bb8ec279ef9c8ec3d0f251c0c92a6ecdc1870be8f7dc7538b21",
  "value": "madam",
  "properties": {
    "length": 5,
    "is_palindrome": true,
    "unique_characters": 3,
    "word_count": 1,
    "sha256_hash": "765cc52b3dbc1bb8ec279ef9c8ec3d0f251c0c92a6ecdc1870be8f7dc7538b21",
    "character_frequency_map": {
      "m": 2,
      "a": 2,
      "d": 1
    }
  },
  "created_at": "2025-10-22T12:00:00Z"
}
Get Specific String
GET /api/strings/{string_value}

Example:
GET /api/strings/madam

Response (200 OK)
json
Copy code
{
  "id": "765cc52b3dbc1bb8ec279ef9c8ec3d0f251c0c92a6ecdc1870be8f7dc7538b21",
  "value": "madam",
  "properties": {
    "length": 5,
    "is_palindrome": true,
    "unique_characters": 3,
    "word_count": 1,
    "sha256_hash": "765cc52b3dbc1bb8ec279ef9c8ec3d0f251c0c92a6ecdc1870be8f7dc7538b21",
    "character_frequency_map": {
      "m": 2,
      "a": 2,
      "d": 1
    }
  },
  "created_at": "2025-10-22T12:00:00Z"
}
Get All Strings (with filters)
GET /api/strings?is_palindrome=true&min_length=3&contains_character=a

Response (200 OK)
json
Copy code
{
  "data": [...],
  "count": 5,
  "filters_applied": {
    "is_palindrome": true,
    "min_length": 3,
    "contains_character": "a"
  }
}
Natural Language Filtering
GET /api/strings/filter-by-natural-language?query=all+single+word+palindromic+strings

Response (200 OK)
json
Copy code
{
  "data": [...],
  "count": 3,
  "interpreted_query": {
    "original": "all single word palindromic strings",
    "parsed_filters": {
      "is_palindrome": true,
      "word_count": 1
    }
  }
}
 Delete String
DELETE /api/strings/{string_value}

Response (204 No Content)
No response body

 Local Setup
PHP 8.4
Composer
Laravel Herd
Git

Installation
bash
Copy code
git clone https://github.com/<your-username>/string-analyzer.git
cd string-analyzer
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
Run locally using Laravel Herd or:

php artisan serve
Your app will be available at:

http://string_analyzer.test/api

☁️ Deployment
Deployed using Laravel Cloud.
Base URL:
https://<your-app-name>.loud.run/api

🧑‍💻 Author
Name:Anita Rodney-Ajayi
Email: anotarodney30@gmail.com
GitHub: github.com/yourusername

