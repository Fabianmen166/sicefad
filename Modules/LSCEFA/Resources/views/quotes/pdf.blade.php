<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $quote->quote_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 2cm;
            color: #333;
            font-size: 5pt;
            line-height: 1.4;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 0;
        }
        .header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            align-items: center;
            background-color: #fff;
            padding: 8px;
            margin-bottom: 12px;
        }
        .header img {
            max-width: 60px;
            height: auto;
        }
        .center-text {
            text-align: center;
            font-weight: bold;
            font-size: 5pt;
            line-height: 1.5;
            color: #000;
        }
        .version-info {
            font-size: 5pt;
            text-align: right;
            line-height: 1.4;
            color: #000;
        }
        .divider {
            border-top: 2px solid #edf5ed;
            margin: 8px 0;
        }
        .title {
            font-size: 5pt;
            font-weight: bold;
            color: #000000;
            margin: 6px 0;
            text-align: center;
            line-height: 1.4;
        }
        .title.applicant-title, .title.lab-title {
            background-color: rgb(146, 198, 148);
            padding: 3px;
            width: 100%;
            border-radius: 0px;
        }
        .info, .filler, .extra-info {
            margin-bottom: 8px;
        }
        .info p, .filler p, .extra-info p {
            margin: 3px 0;
            line-height: 1.4;
            font-size: 5pt;
        }
        .section {
            margin-bottom: 1px;
        }
        .section.applicant-section {
            margin-bottom: 0;
        }
        table.services-table, table.signature-table, table.applicant-table, table.lab-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 5pt;
        }
        table.services-table th {
            background-color: #ffffff;
            color: #000000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000000;
        }
        table.services-table td {
            padding: 5px;
            border: 1px solid #000000;
            text-align: center;
        }
        table.services-table th:nth-child(1), table.services-table td:nth-child(1) {
            width: 10%;
        }
        table.services-table th:nth-child(2), table.services-table td:nth-child(2) {
            width: 60%;
        }
        table.services-table th:nth-child(3), table.services-table td:nth-child(3) {
            width: 10%;
        }
        table.services-table th:nth-child(4), table.services-table td:nth-child(4) {
            width: 10%;
        }
        table.services-table th:nth-child(5), table.services-table td:nth-child(5) {
            width: 10%;
        }
        table.signature-table th {
            background-color: #c8e6c9;
            color: #000000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            border: 1px solid rgb(249, 249, 249);
        }
        table.signature-table td {
            padding: 5px;
            border: 1px solid #a5d6a7;
            text-align: center;
        }
        table.applicant-table td, table.lab-info-table td {
            text-align: left;
            vertical-align: top;
            border: none;
            background: none;
            color: #000;
            padding: 3px 0;
        }
        table.applicant-table td {
            width: 33.33%;
        }
        table.lab-info-table td {
            width: 50%;
        }
        table.applicant-table .label, table.lab-info-table .label {
            font-weight: bold;
            color: #000;
            display: inline;
            margin-right: 3px;
        }
        table.applicant-table .info-item, table.lab-info-table .info-item {
            margin-bottom: 3px;
            line-height: 1.4;
        }
        .total {
            font-weight: bold;
            font-size: 5pt;
            color: #000000;
            text-align: right;
            margin: 6px 0;
        }
        .services-text {
            margin-bottom: 1px;
        }
        .signature-section {
            margin-top: 1px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <!-- Columna izquierda Logo -->
            <div class="logo">
                <?php
                    $allAccredited = true;
                    foreach ($quote->quoteServices as $quoteService) {
                        if ($quoteService->service && !$quoteService->service->acreditado) {
                            $allAccredited = false;
                            break;
                        } elseif ($quoteService->servicePackage) {
                            foreach ($quoteService->servicePackage->included_services as $service) {
                                if (!$service->acreditado) {
                                    $allAccredited = false;
                                    break 2;
                                }
                            }
                        }
                    }
                ?>
                <img src="{{ $allAccredited ? public_path('img/logo_acreditado.jpg') : public_path('img/logo.jpg') }}" alt="{{ $allAccredited ? 'logo_acreditado' : 'Logo' }}" style="width: 80px; height: auto; transform: translate(0px, 80px);">
            </div>
            <!-- Columna centro Textos -->
            <div class="center-text" style="transform: translate(0px, 30px);">
                <div>LABORATORIO DE CIENCIAS BÁSICAS</div>
                <div>PROCEDIMIENTO DE REVISIÓN DE SOLICITUDES, OFERTAS Y CONTRATOS</div>
                <div>FORMATO COTIZACION DE CLIENTES</div>
            </div>
            <!-- Columna derecha Versión, Código, Página -->
            <div class="version-info" style="width: 80px; height: auto; transform: translate(550px, 20px);">
                <div><strong>Versión:</strong> 2</div>
                <div><strong>Código:</strong> #{{ $quote->quote_id }}</div>
                <div><strong>Páginas:</strong> 1 de 1</div>
            </div>
        </div>

        <!-- Datos del solicitante -->
        <div class="section info applicant-section">
            <div class="title applicant-title">Datos del Solicitante</div>
            <table class="applicant-table">
                <tbody>
                    <tr>
                        <td>
                            <div class="info-item">
                                <span class="label">NIT/CC:</span>
                                {{ $quote->customer->nit ?? 'No disponible' }}
                            </div>
                            <div class="info-item">
                                <span class="label">Correo electrónico:</span>
                                {{ $quote->customer->correo ?? 'No Reportado' }}
                            </div>
                        </td>
                        <td>
                            <div class="info-item">
                                <span class="label">Solicitante:</span>
                                {{ $quote->customer->razon_social ?? 'No disponible' }}
                            </div>
                            <div class="info-item">
                                <span class="label">Contacto:</span>
                                {{ $quote->customer->contacto ?? 'No disponible' }}
                            </div>
                        </td>
                        <td>
                            <div class="info-item">
                                <span class="label">Fecha:</span>
                                {{ $quote->created_at->format('d/m/Y H:i:s') }}
                            </div>
                            <div class="info-item">
                                <span class="label">Teléfono:</span>
                                {{ $quote->customer->telefono ?? 'No disponible' }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Información del laboratorio -->
        <div class="section info" style="transform: translate(0px, -17px);">
            <div class="title lab-title">Información del Laboratorio</div>
            <table class="lab-info-table">
                <tbody>
                    <tr>
                        <td>
                            <div class="info-item">
                                <span class="label">Nombre:</span>
                                Laboratorio de Ciencias Básicas - Centro de Formación Agroindustrial La Angostura - SENA
                            </div>
                            <div class="info-item">
                                <span class="label">Dirección:</span>
                                km 38 vía al sur de Neiva
                            </div>
                            <div class="info-item">
                                <span class="label">Municipio/Departamento:</span>
                                Campoalegre/Huila
                            </div>
                            <div class="info-item">
                                <span class="label">ID de la Cotización:</span>
                                {{ $quote->quote_id }}
                            </div>
                        </td>
                        <td>
                            <div class="info-item">
                                <span class="label">Teléfono:</span>
                                +57 123 456 7890
                            </div>
                            <div class="info-item">
                                <span class="label">NIT:</span>
                                899.999.034-1
                            </div>
                            <div class="info-item">
                                <span class="label">Correo electrónico:</span>
                                st-angostura@sena.edu.co
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Servicios en formato de tabla -->
        <div class="section" style="transform: translate(0px, -29px);">
            <table class="services-table">
                <thead>
                    <tr>
                        <th>Ítem</th>
                        <th>Descripción del Servicio</th>
                        <th>Cantidad</th>
                        <th>Valor Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quote->quoteServices as $index => $quoteService)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if ($quoteService->service)
                                    {{ $quoteService->service->descripcion }}
                                @elseif ($quoteService->servicePackage)
                                    {{ $quoteService->servicePackage->nombre }}
                                @endif
                            </td>
                            <td>{{ $quoteService->cantidad }}</td>
                            <td>
                                @if (isset($quoteService->subtotal) && $quoteService->cantidad > 0)
                                    {{ number_format($quoteService->subtotal / $quoteService->cantidad, 0) }}
                                @else
                                    NA
                                @endif
                            </td>
                            <td>{{ isset($quoteService->subtotal) ? number_format($quoteService->subtotal, 2) : 'NA' }}</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold;">
                        <td colspan="4" style="text-align: right;">Total:</td>
                        <td>{{ number_format($quote->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Información del cliente -->
        <div class="section info" style="transform: translate(0px, -34px);">
            <div class="title">Información del Cliente</div>
            <p>{{ $clientText }}</p>
        </div>

        <!-- Descripción de los servicios -->
        <div class="section services-text" style="transform: translate(0px, -34px);">
            <div class="title">Descripción de los Servicios</div>
            <p>Los servicios incluidos en esta cotización son:
                @foreach ($quote->quoteServices as $quoteService)
                    @if ($quoteService->service)
                        {{ $quoteService->service->descripcion }} (Cantidad: {{ $quoteService->cantidad }}, Subtotal: {{ number_format($quoteService->subtotal, 2) }})
                    @elseif ($quoteService->servicePackage)
                        {{ $quoteService->servicePackage->nombre }} (Cantidad: {{ $quoteService->cantidad }}, Subtotal: {{ number_format($quoteService->subtotal, 2) }}), que incluye:
                        @foreach ($quoteService->servicePackage->included_services as $service)
                            {{ $service->descripcion }}@if (!$loop->last), @endif
                        @endforeach
                    @endif
                    @if (!$loop->last), @endif
                @endforeach.
            </p>
        </div>

        <!-- Condiciones Generales -->
        <div class="section filler" style="transform: translate(0px, -34px);">
            <div class="title">Condiciones Generales</div>
            <p>Esta cotización tiene una vigencia de 30 días calendario. La aceptación de la oferta implica que el cliente está de acuerdo con todas las condiciones aquí descritas, incluyendo que sus muestras se analicen por los métodos indicados. En caso de tener cualquier inconformidad la debe manifestar al laboratorio para elaborar una nueva cotización.</p>
            <p>La cantidad de muestra requerida es de aproximadamente 1 kg, la cual debe ser empacada en bolsa limpia, seca, bien sellada y rotulada. La recepción de las muestras para análisis fisicoquímico se hará de lunes a viernes de 08:00 h a 15:00 h. La entrega de resultados se hará en aproximadamente (15 días hábiles contados a partir del día siguiente de la recepción de la muestra) y será acordada previamente con el cliente; una vez emitidos, dicha entrega se realizará en las instalaciones del laboratorio en los horarios de lunes a viernes de 08:00 h a 12:00 h y de 13:30 h a 16:00 h o vía correo electrónico si así lo desea el cliente. En caso de que el cliente requiera devolución del ítem de ensayo, deberá acercarse al laboratorio a partir de la fecha de elaboración del informe de resultados sin exceder un 30 días, de lo contrario el laboratorio queda autorizado para hacer la disposición final.</p>
        </div>

        <!-- Condiciones de Confidencialidad -->
        <div class="section extra-info" style="transform: translate(0px, -30px);">
            <div class="title">Condiciones de Confidencialidad</div>
            <p>Toda la información recibida del cliente por cualquiera de los canales disponibles (presencial, telefónico, correo electrónico, etc) o la generada en el laboratorio a partir de su solicitud, se considera confidencial. Sin embargo, pretende poner información del cliente al alcance del público, podrá hacerlo si se cumplen las siguientes condiciones:</p>
            <p>1. Se debe contar con el consentimiento del cliente para que su información pueda ser compartida, o que el mismo, luego de recibirla la publique a su conveniencia. Cuando el laboratorio necesite utilizar información del cliente y ponerla al alcance del público se debe tener con antelación su aprobación por medio de un correo electrónico con la aceptación respectiva.</p>
            <p>2. Si por disposición de un juez de la república, el LCB tiene que hacer pública la información correspondiente a los resultados de los ensayos de un cliente. Se deberá hacer dicha publicación y posteriormente notificar al cliente de la situación, a menos que en el mismo requerimiento legal, no sea posible informarle.</p>
            <p>Nota: Una vez se llegue a un acuerdo entre las partes y se realice la prestación del servicio, la información sobre el cliente obtenida de fuentes distintas del cliente (por ejemplo, la que proviene de un denunciante o los organismos reglamentarios) se mantendrá confidencial entre el cliente y el laboratorio. El proveedor (fuente) de esta información es confidencial para el laboratorio y no se comparte con el cliente, a menos que la fuente lo autorice.</p>
        </div>

        <!-- Otras Consideraciones -->
        <div class="section filler" style="transform: translate(0px, -30px);">
            <div class="title">Otras Consideraciones</div>
            <p>1. El laboratorio de Ciencias Básicas sección fisicoquímica, no proporciona información sobre declaraciones de conformidad respecto a una especificación, norma o partes de ésta (requisito 7.8.6 NTC ISO/IEC 17025:2017) y no emite información sobre opiniones e interpretaciones (requisito 7.8.7 NTC ISO/IEC 17025:2017).</p>
            <p>2. No realiza muestreo, por lo tanto es responsabilidad del cliente realizar o subcontratar esta actividad y suministrar toda la información necesaria de la muestra (Fecha y hora de muestreo, metodología empleada para la recolección de la muestra). Si el cliente no suministra dicha información u otra que pueda influir en la validez de los resultados, la muestra se recibirá bajo su responsabilidad y se dejará constancia del caso.</p>
            <p>3. En SENA-SERVICIO NACIONAL DE APRENDIZAJE con sede en el laboratorio de ciencias básicas en el centro de formación AGROINDUSTRIAL regional Huila, contamos con acreditación ONAC, vigente a la fecha, con código de acreditación.</p>
            <p>4. En ninguna circunstancia el cliente está autorizado para el uso del símbolo de.</p>
        </div>

        <!-- Responsables -->
        <div class="section info" style="transform: translate(0px, -30px);">
            <div class="title">Responsables</div>
            <p><strong>Elaborado por:</strong> {{ $quote->user->name }} - Cargo: Analista de Cotizaciones</p>
            <p><strong>Aprobado por:</strong> {{ $admin->name }} - Gerente General</p>
        </div>

        <!-- Firmas -->
        <div class="section signature-section" style="transform: translate(0px, -30px);">
            <div class="title">Firmas</div>
            <table class="signature-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>C.C.</th>
                        <th>Fecha</th>
                        <th>Firma</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="height: 30px;"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Información del Cotizante -->
        <div class="section" style="margin-top: 20px; text-align: center;">
            <p><strong>Cotizante:</strong> {{ $quote->customer->razon_social ?? 'No disponible' }}</p>
            <p><strong>Cédula/NIT:</strong> {{ $quote->customer->nit ?? 'No disponible' }}</p>
        </div>
    </div>
</body>
</html>