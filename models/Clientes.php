<?php

declare(strict_types=1);

class Clientes {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT tipo_documento, numero_documento, razon_social, correo, telefono,
                   direccion, pais, activo, created_at, updated_at
            FROM clientes
            WHERE activo = 1
            ORDER BY razon_social ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllInactive(): array {
        $stmt = $this->pdo->query("
            SELECT tipo_documento, numero_documento, razon_social, correo, telefono,
                   direccion, pais, activo, created_at, updated_at
            FROM clientes
            WHERE activo = 0
            ORDER BY razon_social ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDocumento(string $numeroDocumento): array|false {
        $stmt = $this->pdo->prepare("
            SELECT tipo_documento, numero_documento, razon_social, correo, telefono,
                   direccion, pais, activo, created_at, updated_at
            FROM clientes
            WHERE numero_documento = ?
        ");
        $stmt->execute([$numeroDocumento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Búsqueda rápida por documento o razón social. Pensada para autocompletado
     * (usada, por ejemplo, desde el formulario de Reservas).
     */
    public function buscar(string $termino, int $limite = 20): array {
        $like = '%' . $termino . '%';
        $limite = max(1, min($limite, 50));
        $stmt = $this->pdo->prepare("
            SELECT tipo_documento, numero_documento, razon_social, correo, telefono
            FROM clientes
            WHERE activo = 1 AND (numero_documento LIKE ? OR razon_social LIKE ?)
            ORDER BY razon_social ASC
            LIMIT " . $limite . "
        ");
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO clientes (tipo_documento, numero_documento, razon_social, correo, telefono, direccion, pais)
            VALUES (:tipo_documento, :numero_documento, :razon_social, :correo, :telefono, :direccion, :pais)
        ");
        return $stmt->execute([
            ':tipo_documento'   => $data['tipo_documento'],
            ':numero_documento' => trim((string)$data['numero_documento']),
            ':razon_social'     => trim((string)$data['razon_social']),
            ':correo'           => $data['correo'] !== '' ? $data['correo'] : null,
            ':telefono'         => $data['telefono'] !== '' ? $data['telefono'] : null,
            ':direccion'        => $data['direccion'] !== '' ? $data['direccion'] : null,
            ':pais'             => $data['pais'] !== '' ? $data['pais'] : 'Perú',
        ]);
    }

    /**
     * Actualiza los datos de contacto del cliente. El tipo y número de documento (PK)
     * NO se tocan aquí a propósito: para eso está cambiarDocumento().
     */
    public function update(string $numeroDocumento, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE clientes
            SET razon_social = :razon_social, correo = :correo, telefono = :telefono,
                direccion = :direccion, pais = :pais
            WHERE numero_documento = :numero_documento
        ");
        return $stmt->execute([
            ':razon_social'     => trim((string)$data['razon_social']),
            ':correo'           => $data['correo'] !== '' ? $data['correo'] : null,
            ':telefono'         => $data['telefono'] !== '' ? $data['telefono'] : null,
            ':direccion'        => $data['direccion'] !== '' ? $data['direccion'] : null,
            ':pais'             => $data['pais'] !== '' ? $data['pais'] : 'Perú',
            ':numero_documento' => $numeroDocumento,
        ]);
    }

    /**
     * Corrige el tipo/número de documento (PK) de un cliente ya registrado, para
     * cuando hubo un error de digitación al crearlo. Gracias a los FOREIGN KEY con
     * ON UPDATE CASCADE en reservas/estadias/comprobantes_pago, MySQL propaga el
     * nuevo numero_documento automáticamente a todo el historial del cliente.
     */
    public function cambiarDocumento(string $numeroActual, string $nuevoTipo, string $nuevoNumero): bool {
        $stmt = $this->pdo->prepare("
            UPDATE clientes
            SET tipo_documento = :nuevo_tipo, numero_documento = :nuevo_numero
            WHERE numero_documento = :numero_actual
        ");
        return $stmt->execute([
            ':nuevo_tipo'    => $nuevoTipo,
            ':nuevo_numero'  => $nuevoNumero,
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
     * true si el cliente tiene reservas o estadías registradas. Útil como referencia
     * antes de un cambio de documento (por si luego quieres mostrar una advertencia
     * extra en el front cuando el cliente ya tiene historial).
     */
    public function tieneMovimientos(string $numeroDocumento): bool {
        $stmt = $this->pdo->prepare("
            SELECT
                (SELECT COUNT(*) FROM reservas WHERE cliente_doc = ?) +
                (SELECT COUNT(*) FROM estadias WHERE cliente_doc = ?) AS total
        ");
        $stmt->execute([$numeroDocumento, $numeroDocumento]);
        return (int)$stmt->fetchColumn() > 0;
    }
}