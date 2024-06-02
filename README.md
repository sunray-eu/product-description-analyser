# Product Description Analyser

This is a Laravel application designed for analyzing product description's sentiments. It provides tools to upload files, process product descriptions, and display analysis scores.

## Table of Contents

- [Product Description Analyser](#product-description-analyser)
  - [Table of Contents](#table-of-contents)
  - [Requirements](#requirements)
  - [Features](#features)
  - [Installation](#installation)
    - [Step 1: Clone the repository](#step-1-clone-the-repository)
    - [Step 2: Run setup script](#step-2-run-setup-script)
    - [Step 3: Environment setup](#step-3-environment-setup)
    - [Step 4: Generate application key](#step-4-generate-application-key)
    - [Step 5: Run migrations](#step-5-run-migrations)
  - [Running the Application](#running-the-application)
    - [Using Artisan CLI](#using-artisan-cli)
    - [Using run script](#using-run-script)
  - [Project Structure](#project-structure)
    - [Controllers](#controllers)
    - [Models](#models)
    - [Routes](#routes)
    - [Views](#views)
    - [Services](#services)
    - [Utilities](#utilities)
    - [Events](#events)
    - [Providers](#providers)
  - [Contributing](#contributing)
  - [License](#license)
  - [Support](#support)

## Requirements

- PHP ^8.3
- Composer
- Node.js & npm (for frontend dependencies)

## Features

- Analyzes product descriptions.
- Integrates with Google Cloud Natural Language API.
- Real-time notifications using Laravel Broadcast/Reverb and Laravel Echo.
- Utilizes Redis for caching.
- Sentiment analysis to determine the positivity or negativity of product descriptions.

## Installation

### Step 1: Clone the repository

```sh
git clone https://github.com/sunray-eu/product-description-analyser.git
cd product-description-analyser
```

### Step 2: Run setup script

This script will install the necessary PHP and Node.js dependencies and build the frontend assets.

```sh
./setup.sh
```

### Step 3: Environment setup

Copy the `.env.example` to `.env` and configure your environment variables.

```sh
cp .env.example .env
```

### Step 4: Generate application key

```sh
php artisan key:generate
```

### Step 5: Run migrations

Ensure you have a database configured in your `.env` file and then run:

```sh
php artisan migrate
```

## Running the Application

### Using Artisan CLI

To start the application, you can use Laravel's built-in development server:

```sh
php artisan serve
```

### Using run script

This script starts the application using Supervisor.

```sh
./run.sh
```

## Project Structure

### Controllers

- **FileController**: Handles file uploads and displays product descriptions. It includes methods to upload files, parse and process file data, and display the list of products and their analysis scores.

### Models

- **Product**: Represents a product with a name, description, and score.
- **File**: Represents an uploaded file containing product descriptions.
- **User**: Represents the application users.

### Routes

The application defines routes in the `web.php` file for handling file uploads and displaying product descriptions.

```php
use SunrayEu\ProductDescriptionAnalyser\App\Http\Controllers\FileController;

Route::get('/', [FileController::class, 'index'])->name('index');
Route::post('/upload', [FileController::class, 'upload'])->name('upload');
Route::post('/reanalyse', [FileController::class, 'reanalyse'])->name('re-analyse');
Route::post('/file/unselect', [FileController::class, 'deselect'])->name('file-unselect');
```

### Views

- **index.blade.php**: Displays the uploaded file's product descriptions and their analysis scores. It includes a form for uploading files and a table to show the analysis results.

### Services

- No specific service files were found, but services like file parsing and data processing are handled within the controller.

### Utilities

- **LanguageClientInstance**: Singleton pattern implementation for Google Cloud LanguageClient.

### Events

- **ProductUpdated**: Broadcasts product updates using Pusher channels.

### Providers

- **AppServiceProvider**: Registers application services and bootstraps any necessary services.

## Contributing

Contributions are welcome! Please create an issue or submit a pull request with your changes.

## License

This project is licensed under the MIT License.

## Support

For any questions or support, please open an issue on the GitHub repository.
