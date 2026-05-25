#!/bin/bash

# Create composer.json
cat > composer.json << 'EOF'
{
    "name": "likelipop/php-mini-resource-hub",
    "description": "Student Learning Resource Hub",
    "require": {
        "php": ">=8.1",
        "aws/aws-sdk-php": "^3.0",
        "erusev/parsedown": "^1.7"
    },
    "autoload": {
        "psr-4": {
            "Core\\": "src/Core/",
            "Support\\": "src/Support/",
            "Controllers\\": "src/Controllers/"
        }
    }
}
EOF

echo "Installing Composer dependencies via Docker..."
docker run --rm -v $(pwd):/app -w /app composer:latest install

echo "Dumping autoload..."
docker run --rm -v $(pwd):/app -w /app composer:latest dump-autoload

echo "Building and starting Docker services..."
docker compose up -d --build

echo "Setup complete! Application should be available at http://localhost:8000"
