#!/bin/bash

echo "=== PHP Code Coverage Setup ==="
echo ""

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "PHP Version: $PHP_VERSION"
echo ""

# Check if Xdebug or PCOV is already installed
if php -m | grep -q xdebug; then
    echo "✅ Xdebug is already installed"
    DRIVER="xdebug"
elif php -m | grep -q pcov; then
    echo "✅ PCOV is already installed"
    DRIVER="pcov"
else
    echo "❌ No coverage driver found (xdebug or pcov)"
    echo ""
    echo "To install PCOV (recommended - faster than Xdebug):"
    echo ""
    echo "  1. Install PCOV via PECL:"
    echo "     sudo pecl install pcov"
    echo ""
    echo "  2. Or install via Homebrew (if using Homebrew PHP):"
    echo "     brew install pcov"
    echo ""
    echo "  3. Verify installation:"
    echo "     php -m | grep pcov"
    echo ""
    echo "Alternatively, install Xdebug:"
    echo "     sudo pecl install xdebug"
    echo ""
    exit 1
fi

echo ""
echo "=== Generating Coverage Reports ==="
echo ""

# Generate text coverage report
echo "📊 Generating text coverage summary..."
vendor/bin/phpunit --coverage-text --colors=never > coverage-summary.txt 2>&1

if [ $? -eq 0 ]; then
    echo "✅ Text coverage report saved to: coverage-summary.txt"
    echo ""
    echo "Coverage Summary:"
    tail -20 coverage-summary.txt
else
    echo "❌ Failed to generate coverage report"
    cat coverage-summary.txt
    exit 1
fi

echo ""

# Generate HTML coverage report
echo "📊 Generating HTML coverage report..."
vendor/bin/phpunit --coverage-html coverage-html > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo "✅ HTML coverage report saved to: coverage-html/index.html"
    echo ""
    echo "Open in browser:"
    echo "  open coverage-html/index.html"
else
    echo "⚠️  HTML coverage report failed (this is normal if you don't have $DRIVER configured for HTML)"
fi

echo ""
echo "=== Setup Complete ==="
