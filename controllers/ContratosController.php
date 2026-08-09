<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class ContratosController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('CONTRATOS');

        $title = "Gestión de Contratos de Alquiler";
        require_once __DIR__ . '/../views/contratos/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        require_once __DIR__ . '/../models/Contratos.php';
        $model = new Contratos($pdo);

        $estado = strtoupper(trim((string)($_GET['estado'] ?? 'ACTIVO')));
        if (!in_array($estado, ['ACTIVO', 'FINALIZADO', 'CANCELADO'], true)) {
            $estado = 'ACTIVO';
        }

        $data = match ($estado) {
            'FINALIZADO' => $model->getFinalizados(),
            'CANCELADO'  => $model->getCancelados(),
            default      => $model->getActivos(),
        };

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Contrato no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Contratos.php';
        $model = new Contratos($pdo);

        $contrato = $model->getById($id);
        if ($contrato) {
            echo json_encode(['success' => true, 'data' => $contrato]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Contrato no encontrado']);
        }
        exit;
    }

    /**
     * Cálculo de días y monto EN VIVO mientras se llena el formulario (fecha salida,
     * fecha devolución estimada y tarifa por día). No guarda nada, solo calcula.
     */
    public function calcularCosto(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        $fechaSalida = trim((string)($_GET['fecha_hora_salida'] ?? ''));
        $fechaDevolucion = trim((string)($_GET['fecha_hora_devolucion'] ?? ''));
        $tarifa = (float)($_GET['tarifa_dia'] ?? 0);

        if ($fechaSalida === '' || $fechaDevolucion === '' || $tarifa <= 0) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos para calcular el costo (fechas y tarifa).']);
            exit;
        }

        require_once __DIR__ . '/../models/Contratos.php';
        $model = new Contratos($pdo);

        try {
            $calculo = $model->calcularDiasYMonto($fechaSalida, $fechaDevolucion, $tarifa);
            echo json_encode(['success' => true, 'data' => $calculo]);
        } catch (InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $esEdicion = !empty($_POST['es_edicion']) && $_POST['es_edicion'] === '1';
        $id = (int)($_POST['id'] ?? 0);

        $placa = strtoupper(trim((string)($_POST['placa_vehiculo'] ?? '')));
        $clienteDoc = trim((string)($_POST['cliente_doc'] ?? ''));
        $fechaSalida = trim((string)($_POST['fecha_hora_salida'] ?? ''));
        $fechaDevolucion = trim((string)($_POST['fecha_hora_devolucion_estimada'] ?? ''));
        $tarifa = (float)($_POST['tarifa_dia_pactada'] ?? 0);
        $kilometraje = (int)($_POST['kilometraje_salida'] ?? -1);
        $observaciones = trim((string)($_POST['observaciones_salida'] ?? ''));

        // Validaciones
        if ($placa === '') {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un vehículo.']);
            exit;
        }
        if ($clienteDoc === '') {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un cliente.']);
            exit;
        }
        if ($fechaSalida === '' || $fechaDevolucion === '') {
            echo json_encode(['success' => false, 'message' => 'Debe completar ambas fechas.']);
            exit;
        }
        if ($tarifa <= 0) {
            echo json_encode(['success' => false, 'message' => 'La tarifa por día debe ser mayor a 0.']);
            exit;
        }
        if ($kilometraje < 0) {
            echo json_encode(['success' => false, 'message' => 'El kilometraje de salida no es válido.']);
            exit;
        }

        require_once __DIR__ . '/../models/Contratos.php';
        $model = new Contratos($pdo);

        try {
            // Verifica que las fechas sean coherentes (reutiliza el cálculo, que lanza excepción si no lo son)
            $model->calcularDiasYMonto($fechaSalida, $fechaDevolucion, $tarifa);
        } catch (InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }

        // No permitir dos contratos activos sobre el mismo vehículo (salvo que sea edición del mismo)
        if (!$esEdicion && $model->existeContratoActivoParaVehiculo($placa)) {
            echo json_encode(['success' => false, 'message' => 'Este vehículo ya tiene un contrato activo en curso.']);
            exit;
        }

        $data = [
            'placa_vehiculo'                    => $placa,
            'cliente_doc'                       => $clienteDoc,
            'usuario_creacion'                  => $_SESSION['user_name'] ?? '',
            'fecha_hora_salida'                 => $fechaSalida,
            'fecha_hora_devolucion_estimada'    => $fechaDevolucion,
            'tarifa_dia_pactada'                => $tarifa,
            'kilometraje_salida'                => $kilometraje,
            'observaciones_salida'              => $observaciones,
        ];

        try {
            if ($esEdicion) {
                if ($id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Contrato no válido para editar.']);
                    exit;
                }
                $resultado = $model->update($id, $data);
                $mensajeOk = 'Contrato actualizado correctamente';
                $mensajeError = 'No se pudo actualizar el contrato (verifique que no esté firmado)';
            } else {
                $nuevoId = $model->create($data);
                $resultado = $nuevoId !== false;
                $mensajeOk = 'Contrato registrado correctamente';
                $mensajeError = 'No se pudo registrar el contrato';
            }

            echo json_encode(['success' => $resultado, 'message' => $resultado ? $mensajeOk : $mensajeError]);
        } catch (PDOException $e) {
            error_log('Error en ContratosController::guardar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al guardar el contrato']);
        }
        exit;
    }

    public function firmar(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Contrato no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Contratos.php';
        $model = new Contratos($pdo);

        try {
            $resultado = $model->firmar($id);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado
                    ? 'Contrato firmado. El vehículo quedó marcado como ALQUILADO.'
                    : 'No se pudo firmar el contrato (verifique que no esté ya firmado o cancelado)'
            ]);
        } catch (Throwable $e) {
            error_log('Error en ContratosController::firmar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al firmar el contrato']);
        }
        exit;
    }

    public function cancelar(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Contrato no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Contratos.php';
        $model = new Contratos($pdo);

        $resultado = $model->cancelar($id);
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Contrato cancelado correctamente' : 'No se pudo cancelar (verifique que no esté firmado)'
        ]);
        exit;
    }

    /**
     * Previsualización/descarga del PDF del contrato con Dompdf.
     * ?c=contratos&a=pdf&id=1&modo=descargar (por defecto: ver / inline)
     */
    public function pdf(PDO $pdo): void {
        $this->requierePermiso('CONTRATOS');

        $id = (int)($_GET['id'] ?? 0);
        $modo = ($_GET['modo'] ?? 'ver') === 'descargar' ? 'descargar' : 'ver';

        require_once __DIR__ . '/../models/Contratos.php';
        $model = new Contratos($pdo);

        $contrato = $model->getById($id);
        if (!$contrato) {
            http_response_code(404);
            echo 'Contrato no encontrado';
            exit;
        }

        $calculo = $model->calcularDiasYMonto(
            $contrato['fecha_hora_salida'],
            $contrato['fecha_hora_devolucion_estimada'],
            (float)$contrato['tarifa_dia_pactada']
        );

        require_once __DIR__ . '/../vendor/autoload.php'; // Dompdf via Composer

        ob_start();
        require __DIR__ . '/../views/contratos/pdf_contrato.php'; // usa $contrato y $calculo
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = 'contrato_' . $contrato['id'] . '.pdf';
        $dompdf->stream($nombreArchivo, ['Attachment' => $modo === 'descargar']);
        exit;
    }
}