<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de resultados - {{ $process->process_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 1.6cm; color: #000; font-size: 9pt; line-height: 1.35; }
        .container { width: 100%; }
        .header { display: grid; grid-template-columns: 1fr 2fr 1fr; align-items: start; margin-bottom: 8px; }
        .header .logo img { width: 70px; height: auto; }
        .header .center { text-align: center; font-weight: bold; font-size: 11pt; }
        .header .right { text-align: right; font-size: 8pt; }
        .muted { font-size: 8pt; }
        .label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        thead th { background: #f4f4f4; text-align: center; }
        .title-row { background: #f4f4f4; font-weight: bold; text-align: center; border: 1px solid #000; padding: 6px; margin-top: 8px; }
        .no-border { border: none; }
        .small { font-size: 7pt; }
    </style>
    </head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ $hasAccredited ? public_path('img/logo_acreditado.jpg') : public_path('img/logo.jpg') }}" alt="logo">
            </div>
            <div class="center">
                <div>
                    <div>INFORME DE RESULTADOS</div>
                    <div>LABORATORIO DE CIENCIAS BÁSICAS</div>
                    <div>CENTRO DE FORMACIÓN AGROINDUSTRIAL</div>
                    <div class="muted" style="margin-top:4px;">Informe número: {{ $process->process_id }}</div>
                </div>
            </div>
            <div class="right small">
                <div><span class="label">Código:</span> {{ $process->process_id }}</div>
                <div>Página 1 de 1</div>
            </div>
        </div>

        <!-- Fila con 3 celdas: Código interno / Informe número / Fecha emisión del informe -->
        <table>
            <tr>
                <td><span class="label">Código interno ítem de ensayo:</span> {{ $process->item_code ?? '—' }}</td>
                <td><span class="label">Informe número:</span> {{ $process->process_id }}</td>
                <td><span class="label">Fecha emisión del Informe:</span> {{ optional($issuedAt)->format('Y-m-d') }}</td>
            </tr>
        </table>

        <!-- Datos del cliente e información de la muestra (dos columnas) -->
        @php($qc = optional(optional($process->quote)->customer))
        <table style="margin-top:6px;">
            <tr>
                <th style="width:50%; text-align:left;">DATOS DEL CLIENTE</th>
                <th style="width:50%; text-align:left;">INFORMACIÓN DE LA MUESTRA</th>
            </tr>
            <tr>
                <td>
                    <div><span class="label">NIT/CC:</span> {{ $qc->tax_id ?? '—' }}</div>
                    <div><span class="label">Solicitante:</span> {{ $qc->applicant ?? '—' }}</div>
                    <div><span class="label">Contacto:</span> {{ $qc->applicant ?? '—' }}</div>
                    <div><span class="label">Teléfono:</span> {{ $qc->phone ?? '—' }}</div>
                    <div><span class="label">Correo electrónico:</span> {{ $qc->email ?? '—' }}</div>
                </td>
                <td>
                    <div><span class="label">Lugar de muestreo:</span> {{ $process->sampling_place ?? '—' }}</div>
                    <div><span class="label">Matriz:</span> Suelo</div>
                    <div><span class="label">Descripción:</span> {{ $process->description ?? '—' }}</div>
                    <div><span class="label">Fecha de muestreo:</span> {{ $process->sampling_date ?? '—' }}</div>
                    <div><span class="label">Fecha recepción:</span> {{ $process->reception_date ?? '—' }}</div>
                </td>
            </tr>
        </table>

        <!-- Resultados -->
        <div class="title-row" style="margin-top:8px;">RESULTADOS</div>
        <table>
            <thead>
                <tr>
                    <th style="width:30%">ENSAYO</th>
                    <th style="width:14%">RESULTADO</th>
                    <th style="width:12%">UNIDAD</th>
                    <th style="width:16%">FECHA DE ANÁLISIS<br><span class="small">(aaaa-mm-dd)</span></th>
                    <th style="width:16%">TÉCNICA</th>
                    <th style="width:12%">DOCUMENTO NORMATIVO</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['ensayo'] }}</td>
                        <td style="text-align:center;">{{ $row['resultado'] }}</td>
                        <td style="text-align:center;">{{ $row['unidad'] }}</td>
                        <td style="text-align:center;">{{ $row['fecha_analisis'] }}</td>
                        <td>{{ $row['tecnica'] }}</td>
                        <td style="text-align:center;">{{ $row['documento'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;">No hay resultados para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Observaciones -->
        <div class="title-row" style="margin-top:8px; text-align:left; padding-left:6px;">OBSERVACIONES:</div>
        <table class="no-border" style="border:1px solid #000; border-top:none;">
            <tr>
                <td class="no-border" style="height:80px; border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;"></td>
            </tr>
        </table>

        <!-- Firmas -->
        <table class="no-border" style="width:100%; margin-top:14px;">
            <tr>
                <td class="no-border" style="width:50%; text-align:center; padding:0 12px;">
                    <div style="width:70%; margin:18px auto 6px auto; border-top:1px solid #444;"></div>
                    <div style="font-size:8.5pt; line-height:1.2;">
                        <strong>REVISADO POR:</strong><br>
                        RESPONSABLE DE GESTIÓN<br>
                        TÉCNICA SERVICIOS
                    </div>
                </td>
                <td class="no-border" style="width:50%; text-align:center; padding:0 12px;">
                    <div style="width:70%; margin:18px auto 6px auto; border-top:1px solid #444;"></div>
                    <div style="font-size:8.5pt; line-height:1.2;">
                        <strong>AUTORIZADO POR:</strong><br>
                        RESPONSABLE DE SERVICIOS<br>
                        TECNOLÓGICOS
                    </div>
                </td>
            </tr>
        </table>

        <!-- Información de contacto -->
        <div style="text-align:center; margin-top:8px;">
            <div class="small">Centro de Formación Agroindustrial, Laboratorio de Ciencias Básicas</div>
            <div class="small">Km 38 vía al sur de Neiva, Campoalegre–Huila</div>
            <div class="small">Correo electrónico: st-angostura@sena.edu.co &nbsp; Tel: (+57) 6015461500 ext. 83596</div>
        </div>
    </div>
</body>
</html>
