<?php

declare(strict_types=1);

class Usuarios {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Usado por AuthController::process_login(). Verifica credenciales por correo.
     */
    public function authenticate(string $correo, string $password): array|false {
        $stmt = $this->pdo->prepare("
            SELECT documento_id, rol_name, username, nombres, apellidos, correo, telefono, password_hash, activo
            FROM usuarios
            WHERE correo = ?
        ");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || (int)$usuario['activo'] !== 1) {
            return false;
        }
        if (!password_verify($password, $usuario['password_hash'])) {
            return false;
        }

        unset($usuario['password_hash']);
        return $usuario;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT documento_id, rol_name, username, nombres, apellidos, correo, telefono, activo, created_at, updated_at
            FROM usuarios WHERE activo = 1 ORDER BY nombres ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllInactive(): array {
        $stmt = $this->pdo->query("
            SELECT documento_id, rol_name, username, nombres, apellidos, correo, telefono, activo, created_at, updated_at
            FROM usuarios WHERE activo = 0 ORDER BY nombres ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDocumento(string $documentoId): array|false {
        $stmt = $this->pdo->prepare("
            SELECT documento_id, rol_name, username, nombres, apellidos, correo, telefono, activo, created_at, updated_at
            FROM usuarios WHERE documento_id = ?
        ");
        $stmt->execute([$documentoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios (documento_id, rol_name, username, nombres, apellidos, correo, telefono, password_hash)
            VALUES (:documento_id, :rol_name, :username, :nombres, :apellidos, :correo, :telefono, :password_hash)
        ");
        return $stmt->execute([
            ':documento_id'  => trim((string)$data['documento_id']),
            ':rol_name'      => strtoupper(trim((string)$data['rol_name'])),
            ':username'      => trim((string)$data['username']),
            ':nombres'       => trim((string)$data['nombres']),
            ':apellidos'     => trim((string)$data['apellidos']),
            ':correo'        => strtolower(trim((string)$data['correo'])),
            ':telefono'      => $data['telefono'] ?? null,
            ':password_hash' => password_hash((string)$data['password'], PASSWORD_DEFAULT),
        ]);
    }

    /**
     * Si $data['password'] viene vacío, mantiene la contraseña actual (no la toca).
     */
    public function update(string $documentoId, array $data): bool {
        $cambiaPassword = !empty($data['password']);

        $sql = "UPDATE usuarios SET rol_name = :rol_name, username = :username, nombres = :nombres,
                    apellidos = :apellidos, correo = :correo, telefono = :telefono";
        if ($cambiaPassword) {
            $sql .= ", password_hash = :password_hash";
        }
        $sql .= " WHERE documento_id = :documento_id";

        $params = [
            ':rol_name'     => strtoupper(trim((string)$data['rol_name'])),
            ':username'     => trim((string)$data['username']),
            ':nombres'      => trim((string)$data['nombres']),
            ':apellidos'    => trim((string)$data['apellidos']),
            ':correo'       => strtolower(trim((string)$data['correo'])),
            ':telefono'     => $data['telefono'] ?? null,
            ':documento_id' => $documentoId,
        ];
        if ($cambiaPassword) {
            $params[':password_hash'] = password_hash((string)$data['password'], PASSWORD_DEFAULT);
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(string $documentoId): bool {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET activo = 0 WHERE documento_id = ?");
        return $stmt->execute([$documentoId]);
    }

    public function restore(string $documentoId): bool {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET activo = 1 WHERE documento_id = ?");
        return $stmt->execute([$documentoId]);
    }

    public function existeUsername(string $username, ?string $excludeDocumento = null): bool {
        if ($excludeDocumento !== null) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ? AND documento_id != ?");
            $stmt->execute([$username, $excludeDocumento]);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public function existeCorreo(string $correo, ?string $excludeDocumento = null): bool {
        $correo = strtolower(trim($correo));
        if ($excludeDocumento !== null) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ? AND documento_id != ?");
            $stmt->execute([$correo, $excludeDocumento]);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public function existeDocumento(string $documentoId): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE documento_id = ?");
        $stmt->execute([$documentoId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}