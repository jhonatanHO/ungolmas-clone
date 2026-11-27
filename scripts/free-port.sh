#!/usr/bin/env bash
# ==============================================================================
# UNGOLMAS Core - Script para liberación preventiva de puertos en macOS / Linux
# ==============================================================================
set -e

PORT="$1"
if [ -z "$PORT" ]; then
    echo "❌ Error: Debe especificar un puerto. Ejemplo: ./scripts/free-port.sh 8080"
    exit 1
fi

echo "🔍 Verificando si el puerto $PORT está ocupado..."

# 1. Verificar si hay contenedores Docker publicando en ese puerto
CONFLICTING_CONTAINERS=$(docker ps -q --filter "publish=$PORT" 2>/dev/null || true)
if [ -n "$CONFLICTING_CONTAINERS" ]; then
    for CID in $CONFLICTING_CONTAINERS; do
        CNAME=$(docker inspect --format '{{.Name}}' "$CID" 2>/dev/null | sed 's/^\///')
        echo "⚠️ Contenedor Docker '$CNAME' ($CID) detectado en puerto $PORT. Deteniendo..."
        docker stop "$CID" >/dev/null 2>&1 || true
    done
    sleep 2
fi

# 2. Verificar procesos del host local (Node, PHP, Python, etc.)
# IMPORTANTE: Nunca matar procesos del sistema de virtualización (Colima, Lima, Docker Desktop)
HOST_PIDS=$(lsof -ti :"$PORT" 2>/dev/null || true)
if [ -n "$HOST_PIDS" ]; then
    for PID in $HOST_PIDS; do
        CMD=$(ps -p "$PID" -o comm= 2>/dev/null || true)
        # Si es túnel o socket de Colima/Lima/Docker, NO matar el PID porque tumbaría el daemon
        if [[ "$CMD" == *"ssh"* ]] || [[ "$CMD" == *"colima"* ]] || [[ "$CMD" == *"lima"* ]] || [[ "$CMD" == *"docker"* ]]; then
            echo "ℹ️ Socket de reenvío Docker/Colima detectado en puerto $PORT. Esperando sincronización..."
            sleep 2
        else
            echo "🔪 Terminando proceso local '$CMD' (PID: $PID) que bloquea el puerto $PORT..."
            kill -9 "$PID" 2>/dev/null || true
        fi
    done
fi

# 3. Verificación final
if lsof -i :"$PORT" >/dev/null 2>&1; then
    # Verificar si lo que queda es solo el reenviador de docker sin contenedor
    ACTIVE=$(docker ps -q --filter "publish=$PORT" 2>/dev/null || true)
    if [ -z "$ACTIVE" ]; then
        echo "✅ Puerto $PORT libre para asignación de Docker."
    else
        echo "⚠️ Advertencia: El puerto $PORT continúa ocupado. Revisa con 'docker ps'."
    fi
else
    echo "✅ Puerto $PORT libre y disponible."
fi
