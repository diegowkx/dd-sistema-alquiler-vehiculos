<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class ComprobantesController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('FACTURACION');

        $title = "Facturación de Contratos";
        require_once __DIR__ . '/../views/comprobantes/index.php';
    }

    /**
     * Contratos FINALIZADOS (liquidación firmada) que todavía no tienen comprobante emitido.
     */
    public function listarPendientes(PDO $pdo): void {
        $this->requierePermisoApi('FACTURACION');

        require_once __DIR__ . '/../models/Comprobantes.php';
        $model = new Comprobantes($pdo);

        echo json_encode(['success' => true, 'data' => $model->getPendientes()]);
        exit;
    }

    /**
     * Comprobantes ya emitidos.
     */
    public function listarEmitidos(PDO $pdo): void {
        $this->requierePermisoApi('FACTURACION');

        require_once __DIR__ . '/../models/Comprobantes.php';
        $model = new Comprobantes($pdo);

        echo json_encode(['success' => true, 'data' => $model->getComprobantes()]);
        exit;
    }

    /**
     * Devuelve todo lo necesario para pintar el modal de emisión: datos del contrato
     * pendiente de facturar (con datos del cliente prellenados), sus cargos extra y
     * el cálculo del monto a pagar (idéntico al de la liquidación firmada).
     */
    public function obtenerContexto(PDO $pdo): void {
        $this->requierePermisoApi('FACTURACION');

        $contratoId = (int)($_GET['contrato_id'] ?? 0);
        if ($contratoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Contrato no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Comprobantes.php';
        require_once __DIR__ . '/../models/Liquidaciones.php';

        $comprobantesModel = new Comprobantes($pdo);
        $liquidacionesModel = new Liquidaciones($pdo);

        $contrato = $comprobantesModel->getContratoPendientePorId($contratoId);
        if (!$contrato) {
            echo json_encode(['success' => false, 'message' => 'El contrato no existe, no está finalizado, o ya tiene comprobante emitido.']);
            exit;
        }

        $cargos = $liquidacionesModel->getCargos($contratoId);
        $calculo = $liquidacionesModel->calcularMontoLiquidacion(
            $contrato,
            $contrato['fecha_hora_devolucion_real'],
            $contrato['responsable_retraso']
        );

        echo json_encode([
            'success' => true,
            'data' => [
                'contrato' => $contrato,
                'cargos'   => $cargos,
                'calculo'  => $calculo,
            ]
        ]);
        exit;
    }
    
    public function generar(PDO $pdo): void {
        $this->requierePermisoApi('FACTURACION');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $contratoId = (int)($_POST['contrato_id'] ?? 0);
        $tipoComprobante = strtoupper(trim((string)($_POST['tipo_comprobante'] ?? '')));
        $clienteTipoDoc = strtoupper(trim((string)($_POST['cliente_tipo_doc'] ?? '')));
        $clienteNumDoc = trim((string)($_POST['cliente_num_doc'] ?? ''));
        $clienteRazonSocial = trim((string)($_POST['cliente_razon_social'] ?? ''));
        $clienteDireccion = trim((string)($_POST['cliente_direccion'] ?? ''));
        $descuento = (float)($_POST['descuento'] ?? 0);
        $metodoPago = strtoupper(trim((string)($_POST['metodo_pago'] ?? '')));
        $observaciones = trim((string)($_POST['observaciones'] ?? ''));

        // Validaciones
        if ($contratoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Contrato no válido.']);
            exit;
        }
        if (!in_array($tipoComprobante, ['BOLETA', 'FACTURA'], true)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de comprobante no válido.']);
            exit;
        }
        if (!in_array($clienteTipoDoc, ['DNI', 'RUC', 'CE', 'PASAPORTE'], true)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de documento del cliente no válido.']);
            exit;
        }
        if ($tipoComprobante === 'FACTURA' && $clienteTipoDoc !== 'RUC') {
            echo json_encode(['success' => false, 'message' => 'Para emitir FACTURA el cliente debe tener RUC.']);
            exit;
        }
        if ($clienteNumDoc === '' || $clienteRazonSocial === '') {
                echo json_encode(['success' => false, 'message' => 'Faltan datos del cliente.']);
                exit;
            }

            $patronesDoc = [
                'DNI'       => '/^\d{8}$/',
                'RUC'       => '/^\d{11}$/',
                'CE'        => '/^[A-Za-z0-9]{9,12}$/',
                'PASAPORTE' => '/^[A-Za-z0-9]{5,15}$/',
            ];
            if (!preg_match($patronesDoc[$clienteTipoDoc], $clienteNumDoc)) {
                echo json_encode(['success' => false, 'message' => "El número de documento no tiene el formato válido para {$clienteTipoDoc}."]);
                exit;
            }
        if ($descuento < 0) {
            echo json_encode(['success' => false, 'message' => 'El descuento no puede ser negativo.']);
            exit;
        }
        if (!in_array($metodoPago, ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'MIXTO'], true)) {
            echo json_encode(['success' => false, 'message' => 'Método de pago no válido.']);
            exit;
        }

        require_once __DIR__ . '/../models/Comprobantes.php';
        require_once __DIR__ . '/../models/Liquidaciones.php';

        $comprobantesModel = new Comprobantes($pdo);
        $liquidacionesModel = new Liquidaciones($pdo);

        // Se recalcula todo en el servidor (nunca se confía en montos que vengan del formulario)
        $contrato = $comprobantesModel->getContratoPendientePorId($contratoId);
        if (!$contrato) {
            echo json_encode(['success' => false, 'message' => 'El contrato no existe, no está finalizado, o ya tiene comprobante emitido.']);
            exit;
        }

        $calculo = $liquidacionesModel->calcularMontoLiquidacion(
            $contrato,
            $contrato['fecha_hora_devolucion_real'],
            $contrato['responsable_retraso']
        );

        if ($descuento >= $calculo['total_final']) {
            echo json_encode(['success' => false, 'message' => 'El descuento no puede ser mayor o igual al monto a pagar.']);
            exit;
        }

        $data = [
            'contrato_id'          => $contratoId,
            'usuario_emision'      => $_SESSION['username'] ?? ($_SESSION['user_name'] ?? ''),
            'cliente_tipo_doc'     => $clienteTipoDoc,
            'cliente_num_doc'      => $clienteNumDoc,
            'cliente_razon_social' => $clienteRazonSocial,
            'cliente_direccion'    => $clienteDireccion,
            'tipo_comprobante'     => $tipoComprobante,
            'subtotal_alquiler'    => $calculo['calculo_alquiler']['monto_total'],
            'total_cargos_extra'   => $calculo['total_cargos'],
            'descuento'            => $descuento,
            'monto_a_pagar'        => $calculo['total_final'],
            'metodo_pago'          => $metodoPago,
            'observaciones'        => $observaciones,
        ];

        try {
            $id = $comprobantesModel->generar($data);
            echo json_encode(['success' => true, 'message' => 'Comprobante emitido correctamente', 'data' => ['id' => $id]]);
        } catch (RuntimeException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            error_log('Error en ComprobantesController::generar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al emitir el comprobante']);
        }
        exit;
    }

    /**
     * Previsualización/descarga del PDF del comprobante con Dompdf.
     * ?c=comprobantes&a=pdf&id=1&modo=descargar
     */
    public function pdf(PDO $pdo): void {
        $this->requierePermiso('FACTURACION');

        $id = (int)($_GET['id'] ?? 0);
        $modo = ($_GET['modo'] ?? 'ver') === 'descargar' ? 'descargar' : 'ver';

        require_once __DIR__ . '/../models/Comprobantes.php';
        require_once __DIR__ . '/../models/Contratos.php';

        $comprobantesModel = new Comprobantes($pdo);
        $contratosModel = new Contratos($pdo);

        $comprobante = $comprobantesModel->getById($id);
        if (!$comprobante) {
            http_response_code(404);
            echo 'Comprobante no encontrado';
            exit;
        }

        // Datos adicionales del contrato (vehículo, fechas) solo para contexto visual en el PDF
        $contrato = $contratosModel->getById((int)$comprobante['contrato_id']);

        require_once __DIR__ . '/../vendor/autoload.php'; // Dompdf via Composer

        ob_start();
        require __DIR__ . '/../views/comprobantes/pdf_comprobante.php'; // usa $comprobante y $contrato
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = $comprobante['tipo_comprobante'] . '_' . $comprobante['serie'] . '-' . $comprobante['numero'] . '.pdf';
        $dompdf->stream($nombreArchivo, ['Attachment' => $modo === 'descargar']);
        exit;
    }
}