#!/bin/bash

echo "🎉 Starting Event RSVP Generator Server..."
echo "📱 Server will be available at: http://localhost:8000"
echo "🛑 Press Ctrl+C to stop the server"
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP first."
    exit 1
fi

# Start PHP development server
php -S localhost:8000 