<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $quote->quote_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 15px; color: #333; font-size: 8pt; }
        .container { max-width: 1000px; margin: 0 auto; padding: 15px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .header img { max-width: 60px; height: auto; }
        .center-text { text-align: center; font-weight: bold; font-size: 10pt; color: #000; }
        .version-info { font-size: 8pt; text-align: right; color: #000; }
        .title { font-size: 10pt; font-weight: bold; color: #000; margin: 6px 0; text-align: center; }
        .section { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 8pt; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background: #f0f0f0; }
        .info-table td { border: none; text-align: left; }
        .total { font-weight: bold; font-size: 10pt; color: #000; text-align: right; margin: 6px 0; }
        .signature-table th, .signature-table td { border: 1px solid #a5d6a7; }
        .signature-table th { background: #c8e6c9; }
    </style>
</head>
<body>
<div class="container">
    <!-- Encabezado -->
    <div class="header">
        <img src="{{ public_path('img/logo.jpg') }}" alt="Logo">
        <div class="center-text">
            <div>LABORATORIO DE CIENCIAS BÁSICAS</div>
            <div>PROCEDIMIENTO DE REVISIÓN DE SOLICITUDES, OFERTAS Y CONTRATOS</div>
            <div>COTIZACIÓN</div>
        </div>
        <div class="version-info">
            <div><strong>Versión:</strong> 2</div>
            <div><strong>Código:</strong> #{{ $quote->quote_id }}</div>
            <div><strong>Páginas:</strong> 1 de 1</div>
        </div>
    </div>
    <!-- Datos del solicitante -->
    <div class="section">
        <div class="title">Datos del Solicitante</div>
        <table class="info-table">
            <tr>
                <td><strong>NIT/CC:</strong> {{ $quote->customer->tax_id ?? 'No disponible' }}</td>
                <td><strong>Solicitante:</strong> {{ $quote->customer->applicant ?? 'No disponible' }}</td>
                <td><strong>Fecha:</strong> {{ $quote->created_at }}</td>
            </tr>
            <tr>
                <td><strong>Correo electrónico:</strong> {{ $quote->customer->email ?? 'No Reportado' }}</td>
                <td><strong>Contacto:</strong> {{ $quote->customer->contact ?? 'No disponible' }}</td>
                <td><strong>Teléfono:</strong> {{ $quote->customer->phone ?? 'No disponible' }}</td>
            </tr>
        </table>
    </div>
    <!-- Información del laboratorio -->
    <div class="section">
        <div class="title">Información del Laboratorio</div>
        <table class="info-table">
            <tr>
                <td><strong>Nombre:</strong> Laboratorio de Ciencias Básicas - Centro de Formación Agroindustrial La Angostura - SENA</td>
                <td><strong>Dirección:</strong> km 38 vía al sur de Neiva</td>
            </tr>
            <tr>
                <td><strong>Municipio/Departamento:</strong> Campoalegre/Huila</td>
                <td><strong>ID de la Cotización:</strong> {{ $quote->quote_id }}</td>
            </tr>
            <tr>
                <td><strong>Teléfono:</strong> +57 123 456 7890</td>
                <td><strong>NIT:</strong> 899.999.034-1</td>
            </tr>
            <tr>
                <td><strong>Correo electrónico:</strong> st-angostura@sena.edu.co</td>
                <td></td>
            </tr>
        </table>
    </div>
    <!-- Servicios -->
    <div class="section">
        <table>
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
                @foreach ($quote->quoteServices as $index => $qs)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if ($qs->service)
                                {{ $qs->service->name ?? $qs->service->descripcion ?? 'No disponible' }}
                            @elseif ($qs->servicePackage)
                                {{ $qs->servicePackage->name ?? $qs->servicePackage->nombre ?? 'No disponible' }}
                            @endif
                        </td>
                        <td>{{ $qs->quantity ?? $qs->cantidad }}</td>
                        <td>
                            @if (isset($qs->subtotal) && ($qs->quantity ?? $qs->cantidad) > 0)
                                {{ number_format($qs->subtotal / ($qs->quantity ?? $qs->cantidad), 2) }}
                            @else
                                NA
                            @endif
                        </td>
                        <td>{{ isset($qs->subtotal) ? number_format($qs->subtotal, 2) : 'NA' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="total">Total: ${{ number_format($quote->total, 2) }}</p>
    </div>
    <!-- Descripción de los servicios -->
    <div class="section">
        <div class="title">Descripción de los Servicios</div>
        <p>Los servicios incluidos en esta cotización son:
            @foreach ($quote->quoteServices as $qs)
                @if ($qs->service)
                    {{ $qs->service->name ?? $qs->service->descripcion ?? 'No disponible' }} (Cantidad: {{ $qs->quantity ?? $qs->cantidad }}, Subtotal: {{ number_format($qs->subtotal, 2) }})
                @elseif ($qs->servicePackage)
                    {{ $qs->servicePackage->name ?? $qs->servicePackage->nombre ?? 'No disponible' }} (Cantidad: {{ $qs->quantity ?? $qs->cantidad }}, Subtotal: {{ number_format($qs->subtotal, 2) }}), que incluye:
                    @php
                        $included = $qs->servicePackage->included_services;
                        if (is_string($included)) {
                            $included = json_decode($included, true);
                        }
                    @endphp
                    @if (is_array($included))
                        @foreach ($included as $includedService)
                            @if (is_numeric($includedService))
                                {{ $serviceNameMap[$includedService] ?? 'Servicio no disponible' }}@if (!$loop->last), @endif
                            @elseif (is_array($includedService))
                                {{ $includedService['description'] ?? $includedService['descripcion'] ?? 'Descripción no disponible' }}@if (!$loop->last), @endif
                            @else
                                {{ $includedService }}@if (!$loop->last), @endif
                            @endif
                        @endforeach
                    @endif
                @endif
                @if (!$loop->last), @endif
            @endforeach.
        </p>
    </div>
    <!-- Información del cliente -->
    <div class="section">
        <div class="title">Información del Cliente</div>
        <p>{{ $clientText }}</p>
    </div>
    <!-- Condiciones Generales -->
    <div class="section">
        <div class="title">Condiciones Generales</div>
        <p>Esta cotización tiene una vigencia de 30 días calendario. La aceptación de la oferta implica que el cliente está de acuerdo con todas las condiciones aquí descritas, incluyendo que sus muestras se analicen por los métodos indicados. En caso de tener cualquier inconformidad la debe manifestar al laboratorio para elaborar una nueva cotización.</p>
        <p>La cantidad de muestra requerida es de aproximadamente 1 kg, la cual debe ser empacada en bolsa limpia, seca, bien sellada y rotulada. La recepción de las muestras para análisis fisicoquímico se hará de lunes a viernes de 08:00 h a 15:00 h. La entrega de resultados se hará en aproximadamente (15 días hábiles contados a partir del día siguiente de la recepción de la muestra) y será acordada previamente con el cliente; una vez emitidos, dicha entrega se realizará en las instalaciones del laboratorio en los horarios de lunes a viernes de 08:00 h a 12:00 h y de 13:30 h a 16:00 h o vía correo electrónico si así lo desea el cliente. En caso de que el cliente requiera devolución del ítem de ensayo, deberá acercarse al laboratorio a partir de la fecha de elaboración del informe de resultados sin exceder un 30 días, de lo contrario el laboratorio queda autorizado para hacer la disposición final.</p>
    </div>
    <!-- Condiciones de Confidencialidad -->
    <div class="section">
        <div class="title">Condiciones de Confidencialidad</div>
        <p>Toda la información recibida del cliente por cualquiera de los canales disponibles (presencial, telefónico, correo electrónico, etc) o la generada en el laboratorio a partir de su solicitud, se considera confidencial. Sin embargo, pretende poner información del cliente al alcance del público, podrá hacerlo si se cumplen las siguientes condiciones:</p>
        <p>1. Se debe contar con el consentimiento del cliente para que su información pueda ser compartida, o que el mismo, luego de recibirla la publique a su conveniencia. Cuando el laboratorio necesite utilizar información del cliente y ponerla al alcance del público se debe tener con antelación su aprobación por medio de un correo electrónico con la aceptación respectiva.</p>
        <p>2. Si por disposición de un juez de la república, el LCB tiene que hacer pública la información correspondiente a los resultados de los ensayos de un cliente. Se deberá hacer dicha publicación y posteriormente notificar al cliente de la situación, a menos que en el mismo requerimiento legal, no sea posible informarle.</p>
        <p>Nota: Una vez se llegue a un acuerdo entre las partes y se realice la prestación del servicio, la información sobre el cliente obtenida de fuentes distintas del cliente (por ejemplo, la que proviene de un denunciante o los organismos reglamentarios) se mantendrá confidencial entre el cliente y el laboratorio. El proveedor (fuente) de esta información es confidencial para el laboratorio y no se comparte con el cliente, a menos que la fuente lo autorice.</p>
    </div>
    <!-- Otras Consideraciones -->
    <div class="section">
        <div class="title">Otras Consideraciones</div>
        <p>1. El laboratorio de Ciencias Básicas sección fisicoquímica, no proporciona información sobre declaraciones de conformidad respecto a una especificación, norma o partes de ésta (requisito 7.8.6 NTC ISO/IEC 17025:2017) y no emite información sobre opiniones e interpretaciones (requisito 7.8.7 NTC ISO/IEC 17025:2017).</p>
        <p>2. No realiza muestreo, por lo tanto es responsabilidad del cliente realizar o subcontratar esta actividad y suministrar toda la información necesaria de la muestra (Fecha y hora de muestreo, metodología empleada para la recolección de la muestra). Si el cliente no suministra dicha información u otra que pueda influir en la validez de los resultados, la muestra se recibirá bajo su responsabilidad y se dejará constancia del caso.</p>
        <p>3. En SENA-SERVICIO NACIONAL DE APRENDIZAJE con sede en el laboratorio de ciencias básicas en el centro de formación AGROINDUSTRIAL regional Huila, contamos con acreditación ONAC, vigente a la fecha, con código de acreditación.</p>
        <p>4. En ninguna circunstancia el cliente está autorizado para el uso del símbolo de.</p>
    </div>
    <!-- Responsables -->
    <div class="section">
        <div class="title">Responsables</div>
        <p><strong>Elaborado por:</strong> {{ $quote->user->name }} - Cargo: Analista de Cotizaciones</p>
        <p><strong>Aprobado por:</strong> {{ $admin->name }} - Gerente General</p>
    </div>
    <!-- Firmas -->
    <div class="section">
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
</div>
</body>
</html> 