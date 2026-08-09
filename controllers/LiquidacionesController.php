<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class LiquidacionesController {

    use ChecksPermisos;

    private const RESPONSABLES = ['NINGUNO', 'CLIENTE', 'EMPRESA'];

    /**
     * Devuelve todo lo necesario para pintar el modal: datos del contrato,
     * la liquidación existente (o null si aún no se ha guardado un borrador),
     * los cargos de alquiler y, si ya hay datos de devolución, el cálculo actualizado.
     */
    public function obtenerContexto(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        $contratoId = (int)($_GET['contrato_id'] ?? 0);
        if ($contratoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Contrato no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Contratos.php';
        require_once __DIR__ . '/../models/Liquidaciones.php';

        $contratosModel = new Contratos($pdo);
        $liquidacionesModel = new Liquidaciones($pdo);

        $contrato = $contratosModel->getById($contratoId);
        if (!$contrato || $contrato['firmado'] != 1) {
            echo json_encode(['success' => false, 'message' => 'El contrato no existe o aún no está firmado.']);
            exit;
        }

        $liquidacion = $liquidacionesModel->getByContratoId($contratoId);
        $cargos = $liquidacionesModel->getCargos($contratoId);

        $calculo = null;
        if ($liquidacion) {
            $calculo = $liquidacionesModel->calcularMontoLiquidacion(
                $contrato,
                $liquidacion['fecha_hora_devolucion_real'],
                $liquidacion['responsable_retraso']
            );
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'contrato'     => $contrato,
                'liquidacion'  => $liquidacion ?: null,
                'cargos'       => $cargos,
                'calculo'      => $calculo,
            ]
        ]);
        exit;
    }

    /**
     * Recalcula en vivo (sin guardar) para previsualizar el total mientras se llena el formulario.
     */
    public function calcularEnVivo(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        $contratoId = (int)($_GET['contrato_id'] ?? 0);
        $fechaReal = trim((string)($_GET['fecha_hora_devolucion_real'] ?? ''));
        $responsable = strtoupper(trim((string)($_GET['responsable_retraso'] ?? 'NINGUNO')));

        if ($contratoId <= 0 || $fechaReal === '' || !in_array($responsable, self::RESPONSABLES, true)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos para calcular.']);
            exit;
        }

        require_once __DIR__ . '/../models/Contratos.php';
        require_once __DIR__ . '/../models/Liquidaciones.php';

        $contratosModel = new Contratos($pdo);
        $liquidacionesModel = new Liquidaciones($pdo);

        $contrato = $contratosModel->getById($contratoId);
        if (!$contrato) {
            echo json_encode(['success' => false, 'message' => 'Contrato no encontrado.']);
            exit;
        }

        try {
            $calculo = $liquidacionesModel->calcularMontoLiquidacion($contrato, $fechaReal, $responsable);
            echo json_encode(['success' => true, 'data' => $calculo]);
        } catch (InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function guardarLiquidacion(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $contratoId = (int)($_POST['contrato_id'] ?? 0);
        $fechaReal = trim((string)($_POST['fecha_hora_devolucion_real'] ?? ''));
        $kilometraje = (int)($_POST['kilometraje_llegada'] ?? -1);
        $responsable = strtoupper(trim((string)($_POST['responsable_retraso'] ?? 'NINGUNO')));
        $observaciones = trim((string)($_POST['observaciones_llegada'] ?? ''));

        if ($contratoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Contrato no válido.']);
            exit;
        }
        if ($fechaReal === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar la fecha y hora de devolución real.']);
            exit;
        }
        if ($kilometraje < 0) {
            echo json_encode(['success' => false, 'message' => 'El kilometraje de llegada no es válido.']);
            exit;
        }
        if (!in_array($responsable, self::RESPONSABLES, true)) {
            echo json_encode(['success' => false, 'message' => 'Responsable de retraso no válido.']);
            exit;
        }

        require_once __DIR__ . '/../models/Contratos.php';
        require_once __DIR__ . '/../models/Liquidaciones.php';

        $contratosModel = new Contratos($pdo);
        $liquidacionesModel = new Liquidaciones($pdo);

        $contrato = $contratosModel->getById($contratoId);
        if (!$contrato || $contrato['firmado'] != 1) {
            echo json_encode(['success' => false, 'message' => 'El contrato no existe o aún no está firmado.']);
            exit;
        }

        if (strtotime($fechaReal) < strtotime($contrato['fecha_hora_salida'])) {
            echo json_encode(['success' => false, 'message' => 'La devolución real no puede ser anterior a la fecha de salida.']);
            exit;
        }

        if (strtotime($fechaReal) > strtotime($contrato['fecha_hora_devolucion_estimada']) && $responsable === 'NINGUNO') {
            echo json_encode(['success' => false, 'message' => 'Hubo retraso respecto a lo estimado: indique si el responsable es el Cliente o la Empresa.']);
            exit;
        }

        $data = [
            'usuario_cierre'               => $_SESSION['username'] ?? ($_SESSION['user_name'] ?? ''),
            'fecha_hora_devolucion_real'    => $fechaReal,
            'kilometraje_llegada'           => $kilometraje,
            'responsable_retraso'           => $responsable,
            'observaciones_llegada'         => $observaciones,
        ];

        try {
            $resultado = $liquidacionesModel->guardarBorrador($contratoId, $data);
            $calculo = $liquidacionesModel->calcularMontoLiquidacion($contrato, $fechaReal, $responsable);

            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Liquidación guardada correctamente' : 'No se pudo guardar la liquidación',
                'data'    => $calculo,
            ]);
        } catch (RuntimeException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (PDOException $e) {
            error_log('Error en LiquidacionesController::guardarLiquidacion: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al guardar la liquidación']);
        }
        exit;
    }

    public function guardarCargo(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $contratoId = (int)($_POST['contrato_id'] ?? 0);
        $concepto = trim((string)($_POST['concepto'] ?? ''));
        $cantidad = (int)($_POST['cantidad'] ?? 0);
        $precioUnitario = (float)($_POST['precio_unitario'] ?? 0);

        if ($contratoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Contrato no válido.']);
            exit;
        }
        if ($concepto === '' || mb_strlen($concepto) > 150) {
            echo json_encode(['success' => false, 'message' => 'El concepto es obligatorio (máx. 150 caracteres).']);
            exit;
        }
        if ($cantidad <= 0) {
            echo json_encode(['success' => false, 'message' => 'La cantidad debe ser mayor a 0.']);
            exit;
        }
        if ($precioUnitario <= 0) {
            echo json_encode(['success' => false, 'message' => 'El precio unitario debe ser mayor a 0.']);
            exit;
        }

        require_once __DIR__ . '/../models/Liquidaciones.php';
        $model = new Liquidaciones($pdo);

        try {
            $id = $model->agregarCargo($contratoId, [
                'concepto'         => $concepto,
                'cantidad'         => $cantidad,
                'precio_unitario'  => $precioUnitario,
            ]);
            echo json_encode(['success' => $id !== false, 'message' => $id !== false ? 'Cargo agregado' : 'No se pudo agregar el cargo']);
        } catch (RuntimeException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function eliminarCargo(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $cargoId = (int)($_POST['cargo_id'] ?? 0);
        if ($cargoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Cargo no válido.']);
            exit;
        }

        require_once __DIR__ . '/../models/Liquidaciones.php';
        $model = new Liquidaciones($pdo);

        try {
            $resultado = $model->eliminarCargo($cargoId);
            echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Cargo eliminado' : 'No se pudo eliminar el cargo']);
        } catch (RuntimeException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function firmar(PDO $pdo): void {
        $this->requierePermisoApi('CONTRATOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $contratoId = (int)($_POST['contrato_id'] ?? 0);
        if ($contratoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Contrato no válido.']);
            exit;
        }

        require_once __DIR__ . '/../models/Liquidaciones.php';
        $model = new Liquidaciones($pdo);

        try {
            $resultado = $model->firmar($contratoId);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado
                    ? 'Liquidación firmada. El vehículo volvió a estado DISPONIBLE y el contrato pasó a Finalizados.'
                    : 'No se pudo firmar (guarde primero la liquidación, o verifique que no esté ya firmada)'
            ]);
        } catch (Throwable $e) {
            error_log('Error en LiquidacionesController::firmar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al firmar la liquidación']);
        }
        exit;
    }

    /**
     * Previsualización/descarga del PDF de la liquidación con Dompdf.
     * ?c=liquidaciones&a=pdf&contrato_id=1&modo=descargar
     */
    public function pdf(PDO $pdo): void {
        $this->requierePermiso('CONTRATOS');

        $contratoId = (int)($_GET['contrato_id'] ?? 0);
        $modo = ($_GET['modo'] ?? 'ver') === 'descargar' ? 'descargar' : 'ver';

        require_once __DIR__ . '/../models/Contratos.php';
        require_once __DIR__ . '/../models/Liquidaciones.php';

        $contratosModel = new Contratos($pdo);
        $liquidacionesModel = new Liquidaciones($pdo);

        $contrato = $contratosModel->getById($contratoId);
        $liquidacion = $liquidacionesModel->getByContratoId($contratoId);

        if (!$contrato || !$liquidacion) {
            http_response_code(404);
            echo 'Liquidación no encontrada';
            exit;
        }

        $cargos = $liquidacionesModel->getCargos($contratoId);
        $calculo = $liquidacionesModel->calcularMontoLiquidacion(
            $contrato,
            $liquidacion['fecha_hora_devolucion_real'],
            $liquidacion['responsable_retraso']
        );

        require_once __DIR__ . '/../vendor/autoload.php';

        ob_start();
        require __DIR__ . '/../views/contratos/pdf_liquidacion.php'; // usa $contrato, $liquidacion, $cargos, $calculo
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = 'liquidacion_contrato_' . $contrato['id'] . '.pdf';
        $dompdf->stream($nombreArchivo, ['Attachment' => $modo === 'descargar']);
        exit;
    }
}