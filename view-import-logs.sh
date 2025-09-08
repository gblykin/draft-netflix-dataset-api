#!/bin/bash

echo "🔍 Netflix API Import Log Viewer"
echo "================================="

case "${1:-help}" in
    "tail"|"t")
        echo "📊 Following import log (real-time)..."
        docker-compose exec app tail -f storage/logs/import.log
        ;;
    "errors"|"e")
        echo "❌ Showing import errors only..."
        docker-compose exec app grep -E "(ERROR|WARNING)" storage/logs/import.log | tail -20
        ;;
    "summary"|"s")
        echo "📈 Showing import summaries only..."
        docker-compose exec app grep "Import completed" storage/logs/import.log | tail -10
        ;;
    "last"|"l")
        echo "📋 Showing last 20 log entries..."
        docker-compose exec app tail -20 storage/logs/import.log
        ;;
    "clear"|"c")
        echo "🧹 Clearing import log..."
        docker-compose exec app bash -c 'echo "" > storage/logs/import.log'
        echo "✅ Import log cleared!"
        ;;
    "count")
        echo "📊 Import log statistics:"
        echo "========================"
        total_lines=$(docker-compose exec app wc -l < storage/logs/import.log)
        errors=$(docker-compose exec app grep -c "ERROR" storage/logs/import.log 2>/dev/null || echo "0")
        warnings=$(docker-compose exec app grep -c "WARNING" storage/logs/import.log 2>/dev/null || echo "0")
        imports=$(docker-compose exec app grep -c "Import completed" storage/logs/import.log 2>/dev/null || echo "0")
        
        echo "Total log entries: $total_lines"
        echo "Errors: $errors"
        echo "Warnings: $warnings"
        echo "Completed imports: $imports"
        ;;
    "help"|*)
        echo ""
        echo "Usage: $0 [command]"
        echo ""
        echo "Commands:"
        echo "  tail, t      - Follow import log in real-time"
        echo "  errors, e    - Show only errors and warnings"
        echo "  summary, s   - Show only import summaries"
        echo "  last, l      - Show last 20 log entries"
        echo "  clear, c     - Clear the import log"
        echo "  count        - Show log statistics"
        echo "  help         - Show this help message"
        echo ""
        echo "Examples:"
        echo "  $0 tail      # Follow logs in real-time"
        echo "  $0 errors    # View recent errors"
        echo "  $0 summary   # View import summaries"
        ;;
esac

