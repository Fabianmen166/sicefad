<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de resultados - {{ $process->process_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 1cm; color: #000; font-size: 9pt; line-height: 1.3; }
        .container { width: 100%; }
        .header { display: grid; grid-template-columns: 1fr 2fr 1fr; align-items: center; margin-bottom: 2px; }
        .header .logo img { width: 58px; height: auto; }
        .header .center { text-align: center; font-weight: bold; font-size: 10.5pt; line-height: 1.15; }
        .header .right { text-align: right; font-size: 8pt; }
        .muted { font-size: 8pt; }
        .label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        thead th { background: #f4f4f4; text-align: center; }
        .title-row { background: #f4f4f4; font-weight: bold; text-align: center; border: 1px solid #000; padding: 6px; margin-top: 8px; }
        .no-border { border: none; }
        .small { font-size: 7pt; }
        .divider { width: 100%; height: 0; border-top: 1px solid #000; margin: 4px 0 4px 0; }
        .page-break { page-break-before: always; }
        .notes { font-size: 9pt; line-height: 1.3; padding: 6px 8px; text-align: justify; }
        .notes ol { padding-left: 16px; }
        .notes li { margin-bottom: 6px; }
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
                </div>
            </div>
            <div class="right small">
                <div><span class="label">Versión:</span> 2</div>
                <div><span class="label">Código:</span> F-LCB-034</div>
            </div>
        </div>
        <div class="divider"></div>

        <!-- Fila con 3 celdas: Código interno / Informe número / Fecha emisión del informe -->
        <table>
            <tr>
                <td style="width:40%"><span class="label">Código interno ítem de ensayo:</span> {{ $process->item_code ?? '—' }}</td>
                <td style="width:30%"><span class="label">Informe número:</span> {{ $process->process_id }}</td>
                <td style="width:30%"><span class="label">Fecha emisión del Informe:</span> {{ optional($issuedAt)->format('Y-m-d') }}</td>
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
        <table class="no-border" style="width:100%; margin-top:26px;">
            <tr>
                <td class="no-border" style="width:50%; text-align:center; padding:0 12px;">
                    <div style="height:40px;"></div>
                    <div style="width:75%; margin:22px auto 8px auto; border-top:1px solid #444;"></div>
                    <div style="font-size:8.5pt; line-height:1.2;">
                        <strong>REVISADO POR:</strong><br>
                        RESPONSABLE DE GESTIÓN<br>
                        TÉCNICA SERVICIOS
                    </div>
                </td>
                <td class="no-border" style="width:50%; text-align:center; padding:0 12px;">
                    <div style="height:40px;"></div>
                    <div style="width:75%; margin:22px auto 8px auto; border-top:1px solid #444;"></div>
                    <div style="font-size:8.5pt; line-height:1.2;">
                        <strong>AUTORIZADO POR:</strong><br>
                        RESPONSABLE DE SERVICIOS<br>
                        TECNOLÓGICOS
                    </div>
                </td>
            </tr>
        </table>

        <!-- Información de contacto -->
        <div style="text-align:center; margin-top:10px;">
            <div class="small">Centro de Formación Agroindustrial, Laboratorio de Ciencias Básicas</div>
            <div class="small">Km 38 vía al sur de Neiva, Campoalegre–Huila</div>
            <div class="small">Correo electrónico: st-angostura@sena.edu.co &nbsp; Tel: (+57) 6015461500 ext. 83596</div>
        </div>

        <!-- Segunda página: NOTAS -->
        <div class="page-break"></div>

        <div class="header">
            <div class="logo">
                <img src="{{ $hasAccredited ? public_path('img/logo_acreditado.jpg') : public_path('img/logo.jpg') }}" alt="logo">
            </div>
            <div class="center">
                <div>
                    <div>INFORME DE RESULTADOS</div>
                    <div>LABORATORIO DE CIENCIAS BÁSICAS</div>
                    <div>CENTRO DE FORMACIÓN AGROINDUSTRIAL</div>
                </div>
            </div>
            <div class="right small">
                <div><span class="label">Versión:</span> 2</div>
                <div><span class="label">Código:</span> F-LCB-034</div>
            </div>
        </div>
        <div class="divider"></div>

        <div class="title-row" style="text-align:left; padding-left:6px;">NOTAS:</div>
        <div class="notes" style="border:1px solid #000; border-top:none;">
            <ol>
                <li>El Laboratorio de Ciencias Básicas del Centro de Formación Agroindustrial Regional Huila no se hace responsable por aquella información suministrada por el cliente que afecte la validez de los resultados obtenidos, así como por las decisiones que se deriven de los mismos.</li>
                <li>Todos los ensayos realizados y presentados en este informe se realizaron en las instalaciones del Laboratorio de Ciencias Básicas del Centro de Formación Agroindustrial.</li>
                <li>Dado que el Laboratorio de Ciencias Básicas del Centro de Formación Agroindustrial, no realiza muestreo, los resultados consignados en este informe corresponden al ítem en las condiciones en que fue recibido.</li>
                <li>Este informe no debe ser reproducido sin la aprobación del laboratorio, excepto cuando se reproduzca en su totalidad. Un informe sin firma carece de validez.</li>
                <li>Este informe se refiere exclusivamente al ítem de ensayo que es identificado en la primera información del informe.</li>
                <li>Toda la información recibida del cliente por cualquiera de los canales disponibles (presencial, telefónico, correo electrónico, etc.) o la generada en el laboratorio a partir de su solicitud, se considera confidencial. Sin embargo, cuando el Laboratorio de Ciencias Básicas sección fisicoquímica, pretenda poner información del cliente al alcance del público, podrá hacerlo si se cumplen las siguientes condiciones:
                    <ol type="a">
                        <li>Se debe contar con el consentimiento del cliente para que su información pueda ser compartida, o que él mismo, luego de recibirla la publique a su conveniencia. Cuando el laboratorio necesite utilizar información del cliente y ponerla al alcance del público se debe tener con antelación su aprobación por medio de un correo electrónico con la aceptación respectiva.</li>
                        <li>Si por disposición de un juez de la república, el LCB tiene que hacer pública la información correspondiente a los resultados de los ensayos de un cliente, se deberá hacer dicha publicación y posteriormente notificar al cliente de la situación, a menos que en el mismo requerimiento legal, no sea posible informarle.</li>
                        <li>La información sobre el cliente, obtenida de fuentes distintas del cliente (por ejemplo, la que proviene de un denunciante o los organismos reglamentarios) se mantendrá confidencial entre el cliente y el laboratorio. El proveedor (fuente) de esta información es confidencial para el laboratorio y no se comparte con el cliente, a menos que la fuente lo autorice.</li>
                    </ol>
                </li>
                <li>Dispone de 30 días calendario a partir de la emisión del informe de resultados para presentar cualquier reclamo relacionado con los resultados emitidos.</li>
                <li>Peticiones, Quejas, Reclamos o Sugerencias (PQRS) sobre los resultados y/o los servicios prestados, puede utilizar el correo electrónico: ST-angostura@sena.edu.co.</li>
            </ol>
        </div>

        <div class="title-row" style="margin-top:10px; text-align:center;">FIN DEL INFORME DE RESULTADOS</div>
    </div>

    <!-- Pie de página con numeración dinámica para todas las páginas -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("Arial", "normal");
            $size = 8;
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = $pdf->get_width() - $width - 36; // 36 = 0.5 inch from right
            $y = $pdf->get_height() - 28; // 28 px from bottom
            $pdf->page_text($x, $y, $text, $font, $size, array(0,0,0));
        }
    </script>

</body>
</html>
