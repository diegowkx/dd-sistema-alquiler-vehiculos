<?php
/** @var array $contrato */
/** @var array $liquidacion */
/** @var array $cargos */
/** @var array $calculo */
$ca = $calculo['calculo_alquiler'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .header { background-color: #0b667c; color: #ffffff; padding: 14px 18px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0 0; font-size: 11px; }
        .section { margin: 16px 18px; }
        .section h2 { font-size: 13px; color: #0b667c; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.datos td { padding: 4px 6px; vertical-align: top; }
        table.datos td.label { font-weight: bold; width: 200px; color: #475569; }
        table.cargos { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.cargos th, table.cargos td { border: 1px solid #cbd5e1; padding: 5px 8px; font-size: 11px; }
        table.cargos th { background-color: #f1f5f9; text-align: left; }
        table.cargos td.num { text-align: right; }
        .resumen { background-color: #fff3cd; border: 1px solid #ffe08a; padding: 10px 14px; margin-top: 10px; }
        .resumen .fila { display: block; }
        .resumen .total { font-size: 15px; color: #7a5b00; font-weight: bold; margin-top: 6px; }
        .firma { margin-top: 50px; }
        .firma .linea { border-top: 1px solid #1e293b; width: 250px; margin-top: 40px; text-align: center; padding-top: 4px; }
        .firmas-wrap { width: 100%; }
        .firmas-wrap td { width: 50%; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LIQUIDACIÓN DEL CONTRATO N° <?= htmlspecialchars((string)$contrato['id']) ?></h1>
        <p>RENT A CAR S.A.C.</p>
    </div>

    <div class="section">
        <h2>Datos del Contrato</h2>
        <table class="datos">
            <tr><td class="label">Cliente:</td><td><?= htmlspecialchars($contrato['cliente_tipo_doc'] . ': ' . $contrato['cliente_doc'] . ' — ' . $contrato['cliente_nombre']) ?></td></tr>
            <tr><td class="label">Vehículo:</td><td><?= htmlspecialchars($contrato['placa_vehiculo'] . ' — ' . $contrato['marca'] . ' ' . $contrato['modelo'] . ' (' . $contrato['anio'] . ')') ?></td></tr>
            <tr><td class="label">Fecha/hora de salida:</td><td><?= htmlspecialchars($contrato['fecha_hora_salida']) ?></td></tr>
            <tr><td class="label">Devolución estimada:</td><td><?= htmlspecialchars($contrato['fecha_hora_devolucion_estimada']) ?></td></tr>
            <tr><td class="label">Tarifa pactada por día:</td><td>S/ <?= number_format((float)$contrato['tarifa_dia_pactada'], 2) ?></td></tr>
        </table>

        <h2>Datos de Devolución</h2>
        <table class="datos">
            <tr><td class="label">Fecha/hora de devolución real:</td><td><?= htmlspecialchars($liquidacion['fecha_hora_devolucion_real']) ?></td></tr>
            <tr><td class="label">Kilometraje de llegada:</td><td><?= htmlspecialchars((string)$liquidacion['kilometraje_llegada']) ?> km</td></tr>
            <tr><td class="label">Responsable del retraso:</td><td><?= htmlspecialchars($liquidacion['responsable_retraso']) ?></td></tr>
            <tr><td class="label">Observaciones de llegada:</td><td><?= nl2br(htmlspecialchars($liquidacion['observaciones_llegada'] ?? '-')) ?></td></tr>
        </table>

        <?php if (!empty($cargos)): ?>
        <h2>Cargos Extra de Alquiler</h2>
        <table class="cargos">
            <thead>
                <tr><th>Concepto</th><th class="num">Cant.</th><th class="num">P. Unit.</th><th class="num">Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($cargos as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['concepto']) ?></td>
                    <td class="num"><?= htmlspecialchars((string)$c['cantidad']) ?></td>
                    <td class="num">S/ <?= number_format((float)$c['precio_unitario'], 2) ?></td>
                    <td class="num">S/ <?= number_format((float)$c['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="resumen">
            <span class="fila">Días completos: <?= $ca['dias_completos'] ?><?php if ($ca['horas_restantes'] > 0): ?> + <?= $ca['horas_restantes'] ?>h prorrateadas<?php endif; ?></span>
            <span class="fila">Monto por alquiler: S/ <?= number_format($ca['monto_total'], 2) ?></span>
            <span class="fila">Total cargos extra: S/ <?= number_format($calculo['total_cargos'], 2) ?></span>
            <span class="total">TOTAL LIQUIDACIÓN: S/ <?= number_format($calculo['total_final'], 2) ?></span>
        </div>
    </div>

    <div class="section firma">
        <table class="firmas-wrap">
            <tr>
                <td><div class="linea">Firma del Cliente</div></td>
                <td><div class="linea">Firma del Representante</div></td>
            </tr>
        </table>
    </div>
</body>
</html>