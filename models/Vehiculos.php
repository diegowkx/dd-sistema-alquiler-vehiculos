<?php

declare(strict_types=1);

class Vehiculos {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT v.placa, v.tipo_nombre, v.marca, v.modelo, v.anio, v.color, 
                   v.kilometraje, v.estado_disponibilidad, v.imagen_url, v.observaciones, 
                   v.activo, v.created_at, v.updated_at,
                   t.precio_base_dia
            FROM vehiculos v
            INNER JOIN tipos_vehiculo t ON UPPER(TRIM(v.tipo_nombre)) = UPPER(TRIM(t.nombre))
            WHERE v.activo = 1
            ORDER BY v.marca ASC, v.modelo ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllInactive(): array {
        $stmt = $this->pdo->query("
            SELECT v.placa, v.tipo_nombre, v.marca, v.modelo, v.anio, v.color, 
                   v.kilometraje, v.estado_disponibilidad, v.imagen_url, v.observaciones, 
                   v.activo, v.created_at, v.updated_at,
                   t.precio_base_dia
            FROM vehiculos v
            INNER JOIN tipos_vehiculo t ON UPPER(TRIM(v.tipo_nombre)) = UPPER(TRIM(t.nombre))
            WHERE v.activo = 0
            ORDER BY v.marca ASC, v.modelo ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDisponibles(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                v.placa,
                v.tipo_nombre,
                v.marca,
                v.modelo,
                v.anio,
                v.color,
                v.kilometraje,
                v.observaciones,
                t.precio_base_dia
            FROM vehiculos v
            INNER JOIN tipos_vehiculo t 
                ON UPPER(TRIM(v.tipo_nombre)) = UPPER(TRIM(t.nombre))
            WHERE v.activo = 1 
            AND v.estado_disponibilidad = 'DISPONIBLE'
            ORDER BY v.marca ASC, v.modelo ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByPlaca(string $placa): array|false {
        $stmt = $this->pdo->prepare("
            SELECT v.placa, v.tipo_nombre, v.marca, v.modelo, v.anio, v.color, 
                   v.kilometraje, v.estado_disponibilidad, v.imagen_url, v.observaciones, 
                   v.activo, v.created_at, v.updated_at,
                   t.precio_base_dia
            FROM vehiculos v
            LEFT JOIN tipos_vehiculo t ON UPPER(TRIM(v.tipo_nombre)) = UPPER(TRIM(t.nombre))
            WHERE v.placa = ?
        ");
        $stmt->execute([strtoupper(trim($placa))]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO vehiculos (placa, tipo_nombre, marca, modelo, anio, color, kilometraje, imagen_url, observaciones)
            VALUES (:placa, :tipo_nombre, :marca, :modelo, :anio, :color, :kilometraje, :imagen_url, :observaciones)
        ");
        return $stmt->execute([
            ':placa'         => strtoupper(trim((string)$data['placa'])),
            ':tipo_nombre'   => trim((string)$data['tipo_nombre']),
            ':marca'         => trim((string)$data['marca']),
            ':modelo'        => trim((string)$data['modelo']),
            ':anio'          => (int)($data['anio'] ?? date('Y')),
            ':color'         => trim((string)$data['color']),
            ':kilometraje'   => (int)($data['kilometraje'] ?? 0),
            ':imagen_url'    => !empty($data['imagen_url']) ? trim((string)$data['imagen_url']) : 'assets/vehiculos/default.png',
            ':observaciones' => $data['observaciones'] ?? null,
        ]);
    }

    public function update(string $placa, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE vehiculos
            SET tipo_nombre = :tipo_nombre, 
                marca = :marca, 
                modelo = :modelo, 
                anio = :anio, 
                color = :color, 
                kilometraje = :kilometraje, 
                imagen_url = :imagen_url, 
                observaciones = :observaciones
            WHERE placa = :placa
        ");
        return $stmt->execute([
            ':tipo_nombre'   => trim((string)$data['tipo_nombre']),
            ':marca'         => trim((string)$data['marca']),
            ':modelo'        => trim((string)$data['modelo']),
            ':anio'          => (int)$data['anio'],
            ':color'         => trim((string)$data['color']),
            ':kilometraje'   => (int)($data['kilometraje'] ?? 0),
            ':imagen_url'    => !empty($data['imagen_url']) ? trim((string)$data['imagen_url']) : 'assets/vehiculos/default.png',
            ':observaciones' => $data['observaciones'] ?? null,
            ':placa'         => strtoupper(trim($placa)),
        ]);
    }

    public function updateEstado(string $placa, string $nuevoEstado): bool {
        $stmt = $this->pdo->prepare("UPDATE vehiculos SET estado_disponibilidad = ? WHERE placa = ?");
        return $stmt->execute([strtoupper(trim($nuevoEstado)), strtoupper(trim($placa))]);
    }

    public function delete(string $placa): bool {
        $stmt = $this->pdo->prepare("UPDATE vehiculos SET activo = 0 WHERE placa = ?");
        return $stmt->execute([strtoupper(trim($placa))]);
    }

    public function restore(string $placa): bool {
        $stmt = $this->pdo->prepare("UPDATE vehiculos SET activo = 1 WHERE placa = ?");
        return $stmt->execute([strtoupper(trim($placa))]);
    }

    public function existePlaca(string $placa): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM vehiculos WHERE placa = ?");
        $stmt->execute([strtoupper(trim($placa))]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Corrige la placa (Primary Key) de un vehículo registrado.
     * Propaga el cambio si existen tablas relacionadas con FK u ON UPDATE CASCADE.
     */
    public function cambiarPlaca(string $placaActual, string $nuevaPlaca): bool {
        $stmt = $this->pdo->prepare("
            UPDATE vehiculos
            SET placa = :nueva_placa
            WHERE placa = :placa_actual
        ");
        return $stmt->execute([
            ':nueva_placa' => $nuevaPlaca,
            ':placa_actual' => $placaActual
        ]);
    }

}