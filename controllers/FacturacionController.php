<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class FacturacionController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('FACTURACION');

        $title = "Facturación";
        require_once __DIR__ . '/../views/facturacion/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('FACTURACION');

        require_once __DIR__ . '/../models/Comprobantes.php';
        $model = new Comprobantes($pdo);

        $estado = strtoupper(trim((string)($_GET['estado'] ?? '')));
        if (!in_array($estado, ['EMITIDO', 'ANULADO'], true)) {
            $estado = null;
        }
        $tipo = strtoupper(trim((string)($_GET['tipo'] ?? '')));
        if (!in_array($tipo, ['BOLETA', 'FACTURA'], true)) {
            $tipo = null;
        }

        echo json_encode(['success' => true, 'data' => $model->getAll($estado, $tipo)]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('FACTURACION');

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de comprobante no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Comprobantes.php';
        $model = new Comprobantes($pdo);

        $comprobante = $model->getById($id);
        if ($comprobante) {
            echo json_encode(['success' => true, 'data' => $comprobante]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Comprobante no encontrado']);
        }
        exit;
    }

    /**
     * Buscador del modal de Facturación (?c=facturacion&a=buscarAJAX&origen=estadia|reserva&q=...).
     */
    public function buscarAJAX(PDO $pdo): void {
        $this->requierePermisoApi('FACTURACION');

        $origen = strtolower(trim((string)($_GET['origen'] ?? '')));
        $termino = trim((string)($_GET['q'] ?? ''));
        if (mb_strlen($termino) < 2 || !in_array($origen, ['estadia', 'reserva'], true)) {
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        if ($origen === 'estadia') {
            require_once __DIR__ . '/../models/Estadias.php';
            $model = new Estadias($pdo);
            echo json_encode(['success' => true, 'data' => $model->buscarFinalizadasParaFacturar($termino)]);
        } else {
            require_once __DIR__ . '/../models/Reservas.php';
            $model = new Reservas($pdo);
            echo json_encode(['success' => true, 'data' => $model->buscarConAdelantoParaFacturar($termino)]);
        }
        exit;
    }

    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('FACTURACION');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $origen = strtolower(trim((string)($_POST['origen'] ?? ''))); // 'estadia' | 'reserva'
        $refId = filter_var($_POST['ref_id'] ?? null, FILTER_VALIDATE_INT);
        $tipoComprobante = strtoupper(trim((string)($_POST['tipo_comprobante'] ?? '')));
        $metodoPago = strtoupper(trim((string)($_POST['metodo_pago'] ?? 'EFECTIVO')));
        $descuentoRaw = $_POST['descuento'] ?? '0';
        $observaciones = trim((string)($_POST['observaciones'] ?? ''));

        $usuarioUsername = $_SESSION['user_name'] ?? null;
        if (!$usuarioUsername) {
            echo json_encode(['success' => false, 'message' => 'No se pudo identificar al usuario. Inicie sesión nuevamente.']);
            exit;
        }

        if (!$refId || !in_array($origen, ['estadia', 'reserva'], true)) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar una estadía o reserva de la lista de resultados.']);
            exit;
        }
        if (!in_array($tipoComprobante, ['BOLETA', 'FACTURA'], true)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de comprobante no válido.']);
            exit;
        }
        if (!in_array($metodoPago, ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'MIXTO'], true)) {
            echo json_encode(['success' => false, 'message' => 'Método de pago no válido.']);
            exit;
        }
        if (!is_numeric($descuentoRaw) || (float)$descuentoRaw < 0) {
            echo json_encode(['success' => false, 'message' => 'El descuento debe ser un número mayor o igual a 0.']);
            exit;
        }
        $descuento = round((float)$descuentoRaw, 2);

        require_once __DIR__ . '/../models/Comprobantes.php';
        require_once __DIR__ . '/../models/Estadias.php';
        require_once __DIR__ . '/../models/Reservas.php';
        $comprobantesModel = new Comprobantes($pdo);
        $estadiasModel = new Estadias($pdo);
        $reservasModel = new Reservas($pdo);

        try {
            if ($origen === 'estadia') {
                $estadia = $estadiasModel->getById($refId);
                if (!$estadia) {
                    echo json_encode(['success' => false, 'message' => 'La estadía seleccionada no existe.']);
                    exit;
                }
                if ($estadia['estado'] !== 'FINALIZADA') {
                    echo json_encode(['success' => false, 'message' => "Solo se pueden facturar estadías FINALIZADA (actual: {$estadia['estado']})."]);
                    exit;
                }
                if ($comprobantesModel->existeComprobanteParaEstadia($refId)) {
                    echo json_encode(['success' => false, 'message' => 'Esta estadía ya tiene un comprobante emitido.']);
                    exit;
                }

                $clienteDoc = $estadia['cliente_doc'];
                $clienteTipoDoc = $estadia['cliente_tipo_doc'];
                $clienteRazonSocial = $estadia['cliente_nombre'];

                $noches = max(1, (int)ceil(
                    (strtotime($estadia['fecha_hora_checkout']) - strtotime($estadia['fecha_hora_checkin'])) / 86400
                ));
                $subtotalHospedaje = round($noches * (float)$estadia['precio_noche_pactado'], 2);
                $totalCargos = round($estadiasModel->getTotalCargos($refId), 2);

                // Si la reserva de origen tiene adelanto y ese adelanto NO fue
                // facturado por separado, se descuenta aquí del total final.
                $montoAdelantoAplicado = 0.0;
                if (!empty($estadia['reserva_id'])) {
                    $reserva = $reservasModel->getById((int)$estadia['reserva_id']);
                    if ($reserva && (float)$reserva['monto_adelanto'] > 0
                        && !$comprobantesModel->existeAdelantoFacturadoParaReserva((int)$estadia['reserva_id'])) {
                        $montoAdelantoAplicado = round((float)$reserva['monto_adelanto'], 2);
                    }
                }

                $reservaIdParaComprobante = null; // el comprobante de ESTADIA no referencia reserva_id (constraint chk_origen_comprobante)
                $estadiaIdParaComprobante = $refId;
            } else {
                $reserva = $reservasModel->getById($refId);
                if (!$reserva) {
                    echo json_encode(['success' => false, 'message' => 'La reserva seleccionada no existe.']);
                    exit;
                }
                if (!in_array($reserva['estado'], ['CONFIRMADA', 'CHECKED_IN'], true)) {
                    echo json_encode(['success' => false, 'message' => "No se puede facturar el adelanto de una reserva '{$reserva['estado']}'."]);
                    exit;
                }
                if ((float)$reserva['monto_adelanto'] <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Esta reserva no tiene un adelanto registrado.']);
                    exit;
                }
                if ($comprobantesModel->existeAdelantoFacturadoParaReserva($refId)) {
                    echo json_encode(['success' => false, 'message' => 'El adelanto de esta reserva ya fue facturado.']);
                    exit;
                }

                $clienteDoc = $reserva['cliente_doc'];
                $clienteTipoDoc = $reserva['cliente_tipo_doc'];
                $clienteRazonSocial = $reserva['cliente_nombre'];

                $subtotalHospedaje = round((float)$reserva['monto_adelanto'], 2);
                $totalCargos = 0.0;
                $montoAdelantoAplicado = 0.0;
                $descuento = 0.0; // el comprobante de adelanto no admite descuento

                $reservaIdParaComprobante = $refId;
                $estadiaIdParaComprobante = null;
            }

            if ($tipoComprobante === 'FACTURA' && $clienteTipoDoc !== 'RUC') {
                echo json_encode(['success' => false, 'message' => 'Solo se puede emitir FACTURA a clientes con RUC. Use BOLETA.']);
                exit;
            }

            // Datos completos del cliente (dirección) para el snapshot del comprobante.
            require_once __DIR__ . '/../models/Clientes.php';
            $clientesModel = new Clientes($pdo);
            $clienteInfo = $clientesModel->getByDocumento($clienteDoc);
            $clienteDireccion = $clienteInfo['direccion'] ?? null;

            $montoTotal = round($subtotalHospedaje + $totalCargos - $descuento - $montoAdelantoAplicado, 2);
            if ($montoTotal <= 0) {
                echo json_encode(['success' => false, 'message' => 'El monto total del comprobante debe ser mayor a 0.']);
                exit;
            }
            $opGravadas = round($montoTotal / 1.18, 2);
            $igv = round($montoTotal - $opGravadas, 2);

            $pdo->beginTransaction();

            $numeracion = $comprobantesModel->obtenerYReservarNumero($tipoComprobante);

            $idComprobante = $comprobantesModel->create([
                'tipo_origen'             => $origen === 'estadia' ? 'ESTADIA' : 'RESERVA',
                'reserva_id'              => $reservaIdParaComprobante,
                'estadia_id'              => $estadiaIdParaComprobante,
                'usuario_emision'         => $usuarioUsername,
                'cliente_tipo_doc'        => $clienteTipoDoc,
                'cliente_num_doc'         => $clienteDoc,
                'cliente_razon_social'    => $clienteRazonSocial,
                'cliente_direccion'       => $clienteDireccion,
                'tipo_comprobante'        => $tipoComprobante,
                'serie'                   => $numeracion['serie'],
                'numero'                  => $numeracion['numero'],
                'subtotal_hospedaje'      => $subtotalHospedaje,
                'total_cargos_extra'      => $totalCargos,
                'descuento'               => $descuento,
                'monto_adelanto_aplicado' => $montoAdelantoAplicado,
                'op_gravadas'             => $opGravadas,
                'igv'                     => $igv,
                'monto_total'             => $montoTotal,
                'metodo_pago'             => $metodoPago,
                'observaciones'           => $observaciones,
            ]);

            if (!$idComprobante) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'No se pudo generar el comprobante.']);
                exit;
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "{$tipoComprobante} {$numeracion['serie']}-{$numeracion['numero']} generada correctamente",
                'data'    => ['id' => $idComprobante],
            ]);
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error en FacturacionController::guardar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al generar el comprobante']);
        }
        exit;
    }

    public function anular(PDO $pdo): void {
        $this->requierePermisoApi('FACTURACION');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Comprobantes.php';
        $model = new Comprobantes($pdo);

        $resultado = $model->anular($id);
        echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Comprobante anulado correctamente' : 'No se pudo anular (verifique que esté EMITIDO)']);
        exit;
    }

    /**
     * Genera el PDF del comprobante con Dompdf. Soporta dos modos vía ?modo=:
     *   - "ver"       -> se muestra inline (para el <iframe> del modal de vista previa)
     *   - "descargar" -> se fuerza la descarga (Content-Disposition: attachment). Es el valor por defecto.
     * Requiere: composer require dompdf/dompdf
     */
    public function descargarPdf(PDO $pdo): void {
        $this->requierePermisoApi('FACTURACION');

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo 'ID de comprobante no válido';
            exit;
        }

        $modo = strtolower(trim((string)($_GET['modo'] ?? 'descargar')));
        $comoAdjunto = $modo !== 'ver';

        require_once __DIR__ . '/../models/Comprobantes.php';
        require_once __DIR__ . '/../models/Estadias.php';
        $comprobantesModel = new Comprobantes($pdo);
        $estadiasModel = new Estadias($pdo);

        $comprobante = $comprobantesModel->getById($id);
        if (!$comprobante) {
            http_response_code(404);
            echo 'Comprobante no encontrado';
            exit;
        }

        $cargos = [];
        if ($comprobante['tipo_origen'] === 'ESTADIA' && $comprobante['estadia_id']) {
            $cargos = $estadiasModel->getCargos((int)$comprobante['estadia_id']);
        }

        // Ajusta esta ruta si tu vendor/ de Composer está en otra ubicación.
        require_once __DIR__ . '/../vendor/autoload.php';

        $html = $this->buildHtmlComprobante($comprobante, $cargos);

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => false]);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        $nombreArchivo = "{$comprobante['tipo_comprobante']}_{$comprobante['serie']}-{$comprobante['numero']}.pdf";

        $dompdf->stream($nombreArchivo, ['Attachment' => $comoAdjunto]);
        exit;
    }

    private function buildHtmlComprobante(array $c, array $cargos): string {
        $fmt = fn($n) => number_format((float)$n, 2);
        $esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

        $filasCargos = '';
        if ($c['tipo_origen'] === 'ESTADIA') {
            $noches = max(1, (int)ceil((strtotime($c['fecha_hora_checkout']) - strtotime($c['fecha_hora_checkin'])) / 86400));
            $precioNoche = (float)$c['precio_noche_pactado'];
            $filasCargos .= "<tr>
                <td>Hospedaje — Hab. {$esc($c['numero_habitacion'])} ({$noches} noche(s) x S/ {$fmt($precioNoche)})</td>
                <td class='text-right'>S/ {$fmt($c['subtotal_hospedaje'])}</td>
            </tr>";
            foreach ($cargos as $cg) {
                $filasCargos .= "<tr>
                    <td>{$esc($cg['concepto'])} (x{$esc($cg['cantidad'])})</td>
                    <td class='text-right'>S/ {$fmt($cg['subtotal'])}</td>
                </tr>";
            }
        } else {
            $filasCargos .= "<tr>
                <td>Adelanto de reserva — Hab. {$esc($c['numero_habitacion'])}</td>
                <td class='text-right'>S/ {$fmt($c['subtotal_hospedaje'])}</td>
            </tr>";
        }

        if ((float)$c['descuento'] > 0) {
            $filasCargos .= "<tr><td>Descuento</td><td class='text-right'>- S/ {$fmt($c['descuento'])}</td></tr>";
        }
        if ((float)$c['monto_adelanto_aplicado'] > 0) {
            $filasCargos .= "<tr><td>Adelanto ya abonado</td><td class='text-right'>- S/ {$fmt($c['monto_adelanto_aplicado'])}</td></tr>";
        }

        return "<!DOCTYPE html>
        <html><head><meta charset='UTF-8'>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
            .header { background-color: #ff6b4a; color: #fff; padding: 16px; border-radius: 8px; }
            .header h1 { margin: 0; font-size: 18px; }
            .header p { margin: 2px 0; font-size: 11px; }
            .box-comprobante { float: right; background: #fff; color: #ea580c; border: 2px solid #ea580c; border-radius: 8px; padding: 10px 16px; text-align: center; margin-top: -60px; }
            .box-comprobante strong { font-size: 15px; }
            .datos { margin-top: 20px; }
            .datos table { width: 100%; }
            .datos td { padding: 2px 0; }
            table.items { width: 100%; border-collapse: collapse; margin-top: 20px; }
            table.items th { background-color: #f1f5f9; text-align: left; padding: 8px; border-bottom: 2px solid #cbd5e1; }
            table.items td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
            .text-right { text-align: right; }
            .totales { width: 280px; margin-left: auto; margin-top: 10px; }
            .totales td { padding: 4px 8px; }
            .totales .final { font-size: 14px; font-weight: bold; border-top: 2px solid #1e293b; }
            .footer { margin-top: 40px; font-size: 10px; color: #64748b; text-align: center; }
        </style></head>
        <body>
            <div class='header'>
                <h1>{$esc($c['emisor_razon_social'] ?? 'HOTEL SUITES & RESORT S.A.C.')}</h1>
                <p>RUC: {$esc($c['emisor_ruc'] ?? '20123456789')}</p>
                <p>{$esc($c['emisor_direccion'] ?? 'Av. Principal 123, Lima, Perú')}</p>
            </div>
            <div class='box-comprobante'>
                <div>{$esc($c['tipo_comprobante'])} ELECTRÓNICA</div>
                <strong>{$esc($c['serie'])}-{$esc($c['numero'])}</strong>
            </div>
            <div style='clear: both;'></div>

            <div class='datos'>
                <table>
                    <tr><td width='50%'><strong>Cliente:</strong> {$esc($c['cliente_razon_social'])}</td><td><strong>Fecha de emisión:</strong> {$esc($c['fecha_emision'])}</td></tr>
                    <tr><td><strong>{$esc($c['cliente_tipo_doc'])}:</strong> {$esc($c['cliente_num_doc'])}</td><td><strong>Método de pago:</strong> {$esc($c['metodo_pago'])}</td></tr>
                    <tr><td colspan='2'><strong>Dirección:</strong> " . ($c['cliente_direccion'] ? $esc($c['cliente_direccion']) : '—') . "</td></tr>
                </table>
            </div>

            <table class='items'>
                <thead><tr><th>Concepto</th><th class='text-right'>Importe</th></tr></thead>
                <tbody>{$filasCargos}</tbody>
            </table>

            <table class='totales'>
                <tr><td>Op. Gravadas</td><td class='text-right'>S/ {$fmt($c['op_gravadas'])}</td></tr>
                <tr><td>IGV (18%)</td><td class='text-right'>S/ {$fmt($c['igv'])}</td></tr>
                <tr class='final'><td>TOTAL</td><td class='text-right'>S/ {$fmt($c['monto_total'])}</td></tr>
            </table>

            <div class='footer'>Documento generado por el sistema de gestión hotelera. " . ($c['observaciones'] ? 'Obs: ' . $esc($c['observaciones']) : '') . "</div>
        </body></html>";
    }
}