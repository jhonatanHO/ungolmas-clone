#!/usr/bin/env bash
# ==============================================================================
# UNGOLMAS Core - Script para liberación preventiva de puertos en macOS / Linux
# ==============================================================================
set -e

if [ "$#" -eq 0 ]; then
    echo "❌ Error: Debe especificar al menos un puerto. Ejemplo: ./scripts/free-port.sh 8080"
    exit 1
fi

# 0. Asegurar que el daemon de Docker esté activo (auto-iniciar Colima si está detenido)
if ! docker info >/dev/null 2>&1; then
    if command -v colima >/dev/null 2>&1; then
        echo "🐳 El daemon de Docker (Colima) está detenido. Iniciando Colima..."
        colima start
    else
        echo "⚠️ Advertencia: El daemon de Docker no responde. Asegúrate de iniciar Docker Desktop o Colima."
    fi
fi

# 0.1. Autocuración: detectar contenedores propios (ugm_core_*) sin red o sin puertos publicados
for OWN_CID in $(docker ps -aq --filter "name=^ugm_core_" 2>/dev/null || true); do
    OWN_NAME=$(docker inspect --format '{{.Name}}' "$OWN_CID" 2>/dev/null | sed 's/^\///')
    NET_COUNT=$(docker inspect --format '{{len .NetworkSettings.Networks}}' "$OWN_CID" 2>/dev/null || echo "1")
    PORT_COUNT=$(docker inspect --format '{{len .NetworkSettings.Ports}}' "$OWN_CID" 2>/dev/null || echo "1")
    if [ "$NET_COUNT" -eq 0 ] || { [[ "$OWN_NAME" != "ugm_core_app" ]] && [ "$PORT_COUNT" -eq 0 ]; }; then
        echo "♻️ Contenedor '$OWN_NAME' detectado con red/puertos degradados. Recreando..."
        docker rm -f "$OWN_CID" >/dev/null 2>&1 || true
    fi
done

for PORT in "$@"; do
    echo "🔍 Verificando si el puerto $PORT está ocupado..."

    # 1. Verificar si hay contenedores Docker ajenos publicando en ese puerto
    CONFLICTING_CONTAINERS=$(docker ps -q --filter "publish=$PORT" 2>/dev/null || true)
    if [ -n "$CONFLICTING_CONTAINERS" ]; then
        for CID in $CONFLICTING_CONTAINERS; do
            CNAME=$(docker inspect --format '{{.Name}}' "$CID" 2>/dev/null | sed 's/^\///')
            if [[ "$CNAME" == ugm_core_* ]]; then
                echo "ℹ️ Contenedor propio '$CNAME' ya activo en puerto $PORT."
                continue
            fi
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
            if [[ "$CMD" == *"ssh"* ]] || [[ "$CMD" == *"colima"* ]] || [[ "$CMD" == *"lima"* ]] || [[ "$CMD" == *"docker"* ]] || [[ "$CMD" == *"vpnkit"* ]] || [[ "$CMD" == *"orbstack"* ]]; then
                echo "ℹ️ Socket de reenvío Docker/Colima detectado en puerto $PORT. Esperando sincronización..."
                sleep 1
            else
                echo "🔪 Terminando proceso local '$CMD' (PID: $PID) que bloquea el puerto $PORT..."
                kill -9 "$PID" 2>/dev/null || true
            fi
        done
    fi

    # 3. Verificación final
    if lsof -i :"$PORT" >/dev/null 2>&1; then
        # Verificar si lo que queda es solo el reenviador de docker sin contenedor ajeno
        ACTIVE=$(docker ps -q --filter "publish=$PORT" 2>/dev/null || true)
        if [ -z "$ACTIVE" ]; then
            echo "✅ Puerto $PORT libre para asignación de Docker."
        else
            ACTIVE_NAME=$(docker inspect --format '{{.Name}}' "$ACTIVE" 2>/dev/null | sed 's/^\///' | tr '\n' ' ')
            echo "ℹ️ Puerto $PORT asignado a: $ACTIVE_NAME"
        fi
    else
        echo "✅ Puerto $PORT libre y disponible."
    fi
done
