<?php

declare(strict_types=1);

class Habitaciones {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT h.numero_habitacion, h.tipo_nombre, h.piso, h.estado_disponibilidad, h.observaciones,
                   h.activo, h.created_at, h.updated_at, t.precio_base_noche, t.capacidad_personas
            FROM habitaciones h
            INNER JOIN tipos_habitacion t ON h.tipo_nombre = t.nombre
            WHERE h.activo = 1
            ORDER BY h.piso ASC, h.numero_habitacion ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllInactive(): array {
        $stmt = $this->pdo->query("
            SELECT h.numero_habitacion, h.tipo_nombre, h.piso, h.estado_disponibilidad, h.observaciones,
                   h.activo, h.created_at, h.updated_at, t.precio_base_noche, t.capacidad_personas
            FROM habitaciones h
            INNER JOIN tipos_habitacion t ON h.tipo_nombre = t.nombre
            WHERE h.activo = 0
            ORDER BY h.piso ASC, h.numero_habitacion ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Solo habitaciones con estado DISPONIBLE. Útil para selects de Reservas/Estadías.
     */
    public function getAllDisponibles(): array {
        $stmt = $this->pdo->query("
            SELECT h.numero_habitacion, h.tipo_nombre, h.piso, t.precio_base_noche, t.capacidad_personas
            FROM habitaciones h
            INNER JOIN tipos_habitacion t ON h.tipo_nombre = t.nombre
            WHERE h.activo = 1 AND h.estado_disponibilidad = 'DISPONIBLE'
            ORDER BY h.piso ASC, h.numero_habitacion ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByNumero(string $numeroHabitacion): array|false {
        $stmt = $this->pdo->prepare("
            SELECT h.numero_habitacion, h.tipo_nombre, h.piso, h.estado_disponibilidad, h.observaciones,
                   h.activo, h.created_at, h.updated_at
            FROM habitaciones h
            WHERE h.numero_habitacion = ?
        ");
        $stmt->execute([$numeroHabitacion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO habitaciones (numero_habitacion, tipo_nombre, piso, observaciones)
            VALUES (:numero_habitacion, :tipo_nombre, :piso, :observaciones)
        ");
        return $stmt->execute([
            ':numero_habitacion' => trim((string)$data['numero_habitacion']),
            ':tipo_nombre'       => strtoupper(trim((string)$data['tipo_nombre'])),
            ':piso'              => (int)($data['piso'] ?? 1),
            ':observaciones'     => $data['observaciones'] ?? null,
        ]);
    }

    /**
     * Actualiza tipo, piso y observaciones. El número (PK) no se toca aquí porque
     * cambiar el identificador de una habitación con reservas/estadías activas es riesgoso;
     * si necesitas renombrarla, bórrala e ingrésala de nuevo.
     */
    public function update(string $numeroHabitacion, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE habitaciones
            SET tipo_nombre = :tipo_nombre, piso = :piso, observaciones = :observaciones
            WHERE numero_habitacion = :numero_habitacion
        ");
        return $stmt->execute([
            ':tipo_nombre'       => strtoupper(trim((string)$data['tipo_nombre'])),
            ':piso'              => (int)($data['piso'] ?? 1),
            ':observaciones'     => $data['observaciones'] ?? null,
            ':numero_habitacion' => $numeroHabitacion,
        ]);
    }

    public function updateEstado(string $numeroHabitacion, string $nuevoEstado): bool {
        $stmt = $this->pdo->prepare("UPDATE habitaciones SET estado_disponibilidad = ? WHERE numero_habitacion = ?");
        return $stmt->execute([$nuevoEstado, $numeroHabitacion]);
    }

    public function delete(string $numeroHabitacion): bool {
        $stmt = $this->pdo->prepare("UPDATE habitaciones SET activo = 0 WHERE numero_habitacion = ?");
        return $stmt->execute([$numeroHabitacion]);
    }

    public function restore(string $numeroHabitacion): bool {
        $stmt = $this->pdo->prepare("UPDATE habitaciones SET activo = 1 WHERE numero_habitacion = ?");
        return $stmt->execute([$numeroHabitacion]);
    }

    public function existeNumero(string $numeroHabitacion): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM habitaciones WHERE numero_habitacion = ?");
        $stmt->execute([$numeroHabitacion]);
        return (int)$stmt->fetchColumn() > 0;
    }
}