<?php
/** @var array $contrato */
/** @var array $calculo */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .header { background-color: #d9383a; color: #ffffff; padding: 14px 18px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0 0; font-size: 11px; }
        .section { margin: 16px 18px; }
        .section h2 { font-size: 13px; color: #d9383a; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.datos td { padding: 4px 6px; vertical-align: top; }
        table.datos td.label { font-weight: bold; width: 180px; color: #475569; }
        .resumen { background-color: #fff3cd; border: 1px solid #ffe08a; padding: 10px 14px; margin-top: 6px; }
        .resumen strong { color: #7a5b00; }
        .firma { margin-top: 60px; }
        .firma .linea { border-top: 1px solid #1e293b; width: 250px; margin-top: 40px; text-align: center; padding-top: 4px; }
        .firmas-wrap { width: 100%; }
        .firmas-wrap td { width: 50%; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRATO DE ALQUILER DE VEHÍCULO N° <?= htmlspecialchars((string)$contrato['id']) ?></h1>
        <p>RENT A CAR S.A.C.</p>
    </div>

    <div class="section">
        <h2>Datos del Cliente</h2>
        <table class="datos">
            <tr><td class="label">Documento:</td><td><?= htmlspecialchars($contrato['cliente_tipo_doc'] . ': ' . $contrato['cliente_doc']) ?></td></tr>
            <tr><td class="label">Nombre / Razón Social:</td><td><?= htmlspecialchars($contrato['cliente_nombre']) ?></td></tr>
        </table>

        <h2>Datos del Vehículo</h2>
        <table class="datos">
            <tr><td class="label">Placa:</td><td><?= htmlspecialchars($contrato['placa_vehiculo']) ?></td></tr>
            <tr><td class="label">Marca / Modelo / Año:</td><td><?= htmlspecialchars($contrato['marca'] . ' ' . $contrato['modelo'] . ' (' . $contrato['anio'] . ')') ?></td></tr>
            <tr><td class="label">Color:</td><td><?= htmlspecialchars($contrato['color']) ?></td></tr>
            <tr><td class="label">Kilometraje de salida:</td><td><?= htmlspecialchars((string)$contrato['kilometraje_salida']) ?> km</td></tr>
        </table>

        <h2>Datos del Alquiler</h2>
        <table class="datos">
            <tr><td class="label">Fecha y hora de salida:</td><td><?= htmlspecialchars($contrato['fecha_hora_salida']) ?></td></tr>
            <tr><td class="label">Devolución estimada:</td><td><?= htmlspecialchars($contrato['fecha_hora_devolucion_estimada']) ?></td></tr>
            <tr><td class="label">Tarifa pactada por día:</td><td>S/ <?= number_format((float)$contrato['tarifa_dia_pactada'], 2) ?></td></tr>
            <tr><td class="label">Observaciones de salida:</td><td><?= nl2br(htmlspecialchars($contrato['observaciones_salida'] ?? '-')) ?></td></tr>
        </table>

        <div class="resumen">
            <strong>Cálculo estimado:</strong>
            <?= $calculo['dias_completos'] ?> día(s) completo(s)
            <?php if ($calculo['horas_restantes'] > 0): ?>
                + <?= $calculo['horas_restantes'] ?> hora(s) adicionales (prorrateadas)
            <?php endif; ?>
            &nbsp;=&nbsp; <strong>S/ <?= number_format($calculo['monto_total'], 2) ?></strong>
            <br><small>Este monto es estimado según la fecha de devolución prevista. El monto final se define en la Liquidación del contrato.</small>
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