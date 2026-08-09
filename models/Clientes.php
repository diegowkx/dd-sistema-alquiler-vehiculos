<?php

declare(strict_types=1);

class Clientes {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT tipo_documento, numero_documento, razon_social, licencia_conducir, correo, telefono,
                   direccion, pais, activo, created_at, updated_at
            FROM clientes
            WHERE activo = 1
            ORDER BY razon_social ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllInactive(): array {
        $stmt = $this->pdo->query("
            SELECT tipo_documento, numero_documento, razon_social, licencia_conducir, correo, telefono,
                   direccion, pais, activo, created_at, updated_at
            FROM clientes
            WHERE activo = 0
            ORDER BY razon_social ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDocumento(string $numeroDocumento): array|false {
        $stmt = $this->pdo->prepare("
            SELECT tipo_documento, numero_documento, razon_social, licencia_conducir, correo, telefono,
                   direccion, pais, activo, created_at, updated_at
            FROM clientes
            WHERE numero_documento = ?
        ");
        $stmt->execute([$numeroDocumento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Búsqueda rápida por documento, razón social o licencia. Pensada para autocompletado.
     */
    public function buscar(string $termino, int $limite = 20): array {
        $like = '%' . $termino . '%';
        $limite = max(1, min($limite, 50));
        $stmt = $this->pdo->prepare("
            SELECT tipo_documento, numero_documento, razon_social, licencia_conducir, correo, telefono
            FROM clientes
            WHERE activo = 1 AND (
                numero_documento LIKE ? OR 
                razon_social LIKE ? OR 
                licencia_conducir LIKE ?
            )
            ORDER BY razon_social ASC
            LIMIT " . $limite . "
        ");
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO clientes (
                tipo_documento, numero_documento, razon_social, licencia_conducir, 
                correo, telefono, direccion, pais
            ) VALUES (
                :tipo_documento, :numero_documento, :razon_social, :licencia_conducir, 
                :correo, :telefono, :direccion, :pais
            )
        ");

        $licencia = isset($data['licencia_conducir']) ? trim((string)$data['licencia_conducir']) : '';

        return $stmt->execute([
            ':tipo_documento'    => $data['tipo_documento'],
            ':numero_documento'  => trim((string)$data['numero_documento']),
            ':razon_social'      => trim((string)$data['razon_social']),
            ':licencia_conducir' => $licencia !== '' ? $licencia : null,
            ':correo'            => !empty($data['correo']) ? trim((string)$data['correo']) : null,
            ':telefono'          => !empty($data['telefono']) ? trim((string)$data['telefono']) : null,
            ':direccion'         => !empty($data['direccion']) ? trim((string)$data['direccion']) : null,
            ':pais'              => !empty($data['pais']) ? trim((string)$data['pais']) : 'Perú',
        ]);
    }

    /**
     * Actualiza los datos de contacto del cliente.
     */
    public function update(string $numeroDocumento, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE clientes
            SET razon_social = :razon_social,
                licencia_conducir = :licencia_conducir,
                correo = :correo,
                telefono = :telefono,
                direccion = :direccion,
                pais = :pais
            WHERE numero_documento = :numero_documento
        ");

        $licencia = isset($data['licencia_conducir']) ? trim((string)$data['licencia_conducir']) : '';

        return $stmt->execute([
            ':razon_social'      => trim((string)$data['razon_social']),
            ':licencia_conducir' => $licencia !== '' ? $licencia : null,
            ':correo'            => !empty($data['correo']) ? trim((string)$data['correo']) : null,
            ':telefono'          => !empty($data['telefono']) ? trim((string)$data['telefono']) : null,
            ':direccion'         => !empty($data['direccion']) ? trim((string)$data['direccion']) : null,
            ':pais'              => !empty($data['pais']) ? trim((string)$data['pais']) : 'Perú',
            ':numero_documento'  => $numeroDocumento,
        ]);
    }

    /**
     * Corrige el tipo/número de documento (PK) de un cliente ya registrado.
     * Gracias al Foreign Key ON UPDATE CASCADE, MySQL propagará el cambio a 
     * contratos_alquiler y comprobantes_pago automáticamente.
     */
    public function cambiarDocumento(string $numeroActual, string $nuevoTipo, string $nuevoNumero): bool {
        $stmt = $this->pdo->prepare("
            UPDATE clientes
            SET tipo_documento = :nuevo_tipo, numero_documento = :nuevo_numero
            WHERE numero_documento = :numero_actual
        ");
        return $stmt->execute([
            ':nuevo_tipo'    => $nuevoTipo,
            ':nuevo_numero'  => trim($nuevoNumero),
            ':numero_actual' => $numeroActual,
        ]);
    }

    public function delete(string $numeroDocumento): bool {
        $stmt = $this->pdo->prepare("UPDATE clientes SET activo = 0 WHERE numero_documento = ?");
        return $stmt->execute([$numeroDocumento]);
    }

    public function restore(string $numeroDocumento): bool {
        $stmt = $this->pdo->prepare("UPDATE clientes SET activo = 1 WHERE numero_documento = ?");
        return $stmt->execute([$numeroDocumento]);
    }

    public function existeDocumento(string $numeroDocumento): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM clientes WHERE numero_documento = ?");
        $stmt->execute([$numeroDocumento]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Comprueba si el cliente tiene contratos de alquiler o comprobantes emitidos.
     * Útil antes de permitir acciones como eliminación física o al mostrar advertencias.
     */
    public function tieneMovimientos(string $numeroDocumento): bool {
        $stmt = $this->pdo->prepare("
            SELECT
                (SELECT COUNT(*) FROM contratos_alquiler WHERE cliente_doc = ?) +
                (SELECT COUNT(*) FROM comprobantes_pago WHERE cliente_num_doc = ?) AS total
        ");
        $stmt->execute([$numeroDocumento, $numeroDocumento]);
        return (int)$stmt->fetchColumn() > 0;
    }
}