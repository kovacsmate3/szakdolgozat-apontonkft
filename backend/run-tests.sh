#!/bin/bash

# Set colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}==============================================${NC}"
echo -e "${BLUE}          STARTING TEST SUITE                ${NC}"
echo -e "${BLUE}==============================================${NC}"

# Run all tests with coverage report
if [ "$1" = "--coverage" ]; then
    echo -e "${YELLOW}Running all tests with coverage report...${NC}"
    php artisan test --coverage
    exit 0
fi

# Run specific test suite if provided
if [ -n "$1" ]; then
    echo -e "${YELLOW}Running test suite: $1...${NC}"
    php artisan test --filter="Tests\\Feature\\$1"
    exit 0
fi

# Default: run all tests
echo -e "${YELLOW}Running all tests...${NC}"
php artisan test

echo -e "${BLUE}==============================================${NC}"
echo -e "${BLUE}          TEST EXECUTION COMPLETED           ${NC}"
echo -e "${BLUE}==============================================${NC}"

echo
echo -e "${YELLOW}Available test suites:${NC}"
echo -e "${GREEN}RoadRecord${NC} - Útnyilvántartás tesztjei (Járművek, Utak, Tankolások)"
echo -e "${GREEN}Shared${NC} - Megosztott komponensek tesztjei (Jogszabályok, Felhasználók)"
echo -e "${GREEN}WorkLog${NC} - Munkaidő-nyilvántartás tesztjei (Szabadság, Túlóra)"
echo -e "${GREEN}UserManagement${NC} - Felhasználó és jogosultság kezelés tesztjei"
echo -e "${GREEN}Unit${NC} - Egyedi komponens egységtesztjei"

echo
echo -e "Használat:"
echo -e "  - Összes teszt futtatása: ${YELLOW}./run-tests.sh${NC}"
echo -e "  - Adott teszttípus: ${YELLOW}./run-tests.sh RoadRecord${NC}"
echo -e "  - Lefedettségi jelentés: ${YELLOW}./run-tests.sh --coverage${NC}"
