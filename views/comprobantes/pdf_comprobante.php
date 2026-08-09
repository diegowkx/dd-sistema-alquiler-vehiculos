<?php
/** @var array $comprobante */
/** @var array|false $contrato */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .header { background-color: #6d28d9; color: #ffffff; padding: 14px 18px; overflow: hidden; }
        .header-left { float: left; }
        .header-right { float: right; text-align: right; border: 2px solid #ffffff; border-radius: 6px; padding: 8px 14px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0 0; font-size: 11px; }
        .header-right .tipo { font-size: 14px; font-weight: bold; }
        .header-right .serie { font-size: 15px; font-weight: bold; margin-top: 4px; }
        .section { margin: 16px 18px; }
        .section h2 { font-size: 13px; color: #6d28d9; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.datos td { padding: 4px 6px; vertical-align: top; }
        table.datos td.label { font-weight: bold; width: 160px; color: #475569; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.items th, table.items td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 11px; }
        table.items th { background-color: #f1f5f9; text-align: left; }
        table.items td.num { text-align: right; }
        .resumen { width: 260px; float: right; margin-top: 10px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px; }
        .resumen .fila { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 3px; }
        .resumen .total { font-size: 14px; color: #6d28d9; font-weight: bold; border-top: 1px solid #cbd5e1; padding-top: 6px; margin-top: 6px; }
        .clear { clear: both; }
        .footer-note { margin: 30px 18px 0; font-size: 10px; color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1><?= htmlspecialchars($comprobante['emisor_razon_social']) ?></h1>
            <p>RUC: <?= htmlspecialchars($comprobante['emisor_ruc']) ?></p>
            <p><?= htmlspecialchars($comprobante['emisor_direccion']) ?></p>
        </div>
        <div class="header-right">
            <div class="tipo"><?= htmlspecialchars($comprobante['tipo_comprobante']) ?></div>
            <div class="serie"><?= htmlspecialchars($comprobante['serie'] . '-' . $comprobante['numero']) ?></div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section">
        <h2>Datos del Cliente</h2>
        <table class="datos">
            <tr><td class="label">Documento:</td><td><?= htmlspecialchars($comprobante['cliente_tipo_doc'] . ': ' . $comprobante['cliente_num_doc']) ?></td></tr>
            <tr><td class="label">Cliente:</td><td><?= htmlspecialchars($comprobante['cliente_razon_social']) ?></td></tr>
            <?php if (!empty($comprobante['cliente_direccion'])): ?>
            <tr><td class="label">Dirección:</td><td><?= htmlspecialchars($comprobante['cliente_direccion']) ?></td></tr>
            <?php endif; ?>
            <tr><td class="label">Fecha de emisión:</td><td><?= htmlspecialchars($comprobante['fecha_emision']) ?></td></tr>
            <tr><td class="label">Método de pago:</td><td><?= htmlspecialchars($comprobante['metodo_pago']) ?></td></tr>
        </table>

        <?php if ($contrato): ?>
        <h2>Referencia del Servicio</h2>
        <table class="datos">
            <tr><td class="label">Contrato N°:</td><td><?= htmlspecialchars((string)$contrato['id']) ?></td></tr>
            <tr><td class="label">Vehículo:</td><td><?= htmlspecialchars($contrato['placa_vehiculo'] . ' — ' . $contrato['marca'] . ' ' . $contrato['modelo'] . ' (' . $contrato['anio'] . ')') ?></td></tr>
            <tr><td class="label">Periodo:</td><td><?= htmlspecialchars($contrato['fecha_hora_salida'] . ' al ' . $contrato['fecha_hora_devolucion_estimada']) ?></td></tr>
        </table>
        <?php endif; ?>

        <h2>Detalle</h2>
        <table class="items">
            <thead>
                <tr><th>Concepto</th><th class="num">Importe</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Servicio de alquiler de vehículo (Contrato N° <?= htmlspecialchars((string)$comprobante['contrato_id']) ?>)</td>
                    <td class="num">S/ <?= number_format((float)$comprobante['subtotal_alquiler'], 2) ?></td>
                </tr>
                <?php if ((float)$comprobante['total_cargos_extra'] > 0): ?>
                <tr>
                    <td>Cargos extra de alquiler</td>
                    <td class="num">S/ <?= number_format((float)$comprobante['total_cargos_extra'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)$comprobante['descuento'] > 0): ?>
                <tr>
                    <td>Descuento</td>
                    <td class="num">- S/ <?= number_format((float)$comprobante['descuento'], 2) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="resumen">
            <div class="fila"><span>Op. Gravadas:</span><span>S/ <?= number_format((float)$comprobante['op_gravadas'], 2) ?></span></div>
            <div class="fila"><span>IGV (18%):</span><span>S/ <?= number_format((float)$comprobante['igv'], 2) ?></span></div>
            <div class="fila total"><span>TOTAL:</span><span>S/ <?= number_format((float)$comprobante['monto_total'], 2) ?></span></div>
        </div>
        <div class="clear"></div>

        <?php if (!empty($comprobante['observaciones'])): ?>
        <h2>Observaciones</h2>
        <p><?= nl2br(htmlspecialchars($comprobante['observaciones'])) ?></p>
        <?php endif; ?>
    </div>

    <div class="footer-note">
        Representación impresa del comprobante de pago. Estado: <?= htmlspecialchars($comprobante['estado_comprobante']) ?>.
    </div>
</body>
</html>