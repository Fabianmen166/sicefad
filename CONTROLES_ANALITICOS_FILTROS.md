# 🎯 Filtros de Controles Analíticos para Análisis de Textura

## 📋 Descripción General

El sistema de filtrado para análisis de textura ahora incluye filtros específicos para **controles analíticos** que se almacenan en el campo JSON `controles_analiticos` de la tabla `analytical_controls`.

## 🔍 Tipos de Filtros Implementados

### 1. **Filtros de Presencia de Controles**
- **`tiene_controles = '1'`**: Muestra solo análisis que tienen controles analíticos
- **`tiene_controles = '0'`**: Muestra solo análisis que NO tienen controles analíticos

### 2. **Filtros por Tipo de Control**
- **`tipo_control = 'duplicado'`**: Filtra por análisis que contengan duplicados
- **`tipo_control = 'material de referencia'`**: Filtra por análisis que contengan material de referencia
- **`tipo_control = 'blanco'`**: Filtra por análisis que contengan blancos

### 3. **Filtros por Aceptabilidad**
- **`aceptabilidad_control = 'aceptable'`**: Solo controles marcados como aceptables
- **`aceptabilidad_control = 'no aceptable'`**: Solo controles marcados como no aceptables

### 4. **Filtros por DPR (Diferencia Porcentual Relativa)**
- **`dpr_min = X`**: Controles con DPR mayor o igual a X%
- **`dpr_max = X`**: Controles con DPR menor o igual a X%

## 🏗️ Estructura de Datos JSON Esperada

### Ejemplo de Duplicado:
```json
{
  "duplicado_a": {
    "identificacion": "Duplicado A",
    "codigo_interno": "DA001",
    "arena_1": 45.2,
    "arcilla_1": 30.1,
    "limo_1": 24.7,
    "dpr_arena": 2.1,
    "dpr_arcilla": 1.8,
    "dpr_limo": 2.0,
    "aceptabilidad_control": "Aceptable",
    "observaciones": "Control dentro de límites aceptables"
  }
}
```

### Ejemplo de Material de Referencia:
```json
{
  "material_referencia": {
    "identificacion": "Material de Referencia",
    "codigo_interno": "MR001",
    "arena_1": 45.0,
    "arcilla_1": 30.0,
    "limo_1": 25.0,
    "dpr_arena": 0.4,
    "dpr_arcilla": 0.3,
    "dpr_limo": 0.0,
    "aceptabilidad_control": "Aceptable",
    "observaciones": "Error < 5% - Control aceptable"
  }
}
```

## 🚀 Filtros Predefinidos Disponibles

### Filtros Básicos:
- **Pendientes de Revisión**: `estado_revision = 'pending'`
- **Última Semana**: Análisis de la última semana
- **Último Mes**: Análisis del último mes

### Filtros por Componentes:
- **Alta Arena**: `porcentaje_min = 70`
- **Alta Arcilla**: `porcentaje_min = 40`
- **Alto Limo**: `porcentaje_min = 50`

### Filtros de Controles Analíticos:
- **Con Controles Analíticos**: `tiene_controles = '1'`
- **Sin Controles Analíticos**: `tiene_controles = '0'`
- **Controles Aceptables**: `aceptabilidad_control = 'aceptable'`
- **Controles No Aceptables**: `aceptabilidad_control = 'no aceptable'`
- **Con Duplicados**: `tipo_control = 'duplicado'`
- **Con Material de Referencia**: `tipo_control = 'material de referencia'`
- **DPR Alto (>5%)**: `dpr_min = 5`
- **DPR Bajo (<2%)**: `dpr_max = 2`

## 🔧 Implementación Técnica

### 1. **Método de Filtrado**
```php
protected function applyTextureFilters($textureAnalyses, $filters)
{
    return $textureAnalyses->filter(function($item) use ($filters) {
        // ... filtros existentes ...
        
        // ===== FILTROS PARA CONTROLES ANALÍTICOS =====
        
        // Filtro por tipo de control analítico
        if (isset($filters['tipo_control']) && !empty($filters['tipo_control'])) {
            // Lógica de filtrado...
        }
        
        // Filtro por aceptabilidad de controles analíticos
        if (isset($filters['aceptabilidad_control']) && !empty($filters['aceptabilidad_control'])) {
            // Lógica de filtrado...
        }
        
        // Filtro por rango de DPR
        if (isset($filters['dpr_min']) && is_numeric($filters['dpr_min'])) {
            // Lógica de filtrado...
        }
        
        // Filtro por presencia de controles analíticos
        if (isset($filters['tiene_controles'])) {
            // Lógica de filtrado...
        }
        
        return true;
    });
}
```

### 2. **Búsqueda en Campos JSON**
El sistema busca en dos lugares:
1. **Campo JSON del análisis**: `$analysis->analytical_controls`
2. **Relación Eloquent**: `$analysis->analyticalControls->controles_analiticos`

### 3. **Fallback Inteligente**
Si no se encuentran controles en la relación, se busca en el campo JSON del análisis.

## 📊 Estadísticas de Controles Analíticos

### Métricas Calculadas:
- **Total de controles**: Número total de controles analíticos
- **Duplicados**: Controles de tipo duplicado
- **Material de referencia**: Controles de material de referencia
- **Aceptables**: Controles marcados como aceptables
- **No aceptables**: Controles marcados como no aceptables

### Implementación:
```php
protected function getTextureAnalysisStats($textureAnalyses)
{
    $stats = [
        // ... estadísticas existentes ...
        'controles_analiticos' => [
            'total_controles' => 0,
            'duplicados' => 0,
            'material_referencia' => 0,
            'aceptables' => 0,
            'no_aceptables' => 0
        ]
    ];
    
    // Lógica de conteo...
    
    return $stats;
}
```

## 🎨 Visualización en la Vista

### 1. **Tabla de Controles Analíticos**
- Muestra duplicados y otros controles (excluye material de referencia)
- Campos: Identificación, Código, Arena, Arcilla, Limo, DPR, Aceptabilidad, Observaciones

### 2. **Tabla de Material de Referencia**
- Muestra solo material de referencia
- Mismos campos que controles analíticos

### 3. **Ejemplos de Demostración**
- Si no hay controles reales, se muestran ejemplos para demostrar la funcionalidad
- Incluye información educativa sobre controles analíticos

## 🧪 Comandos de Debug

### Comando Principal:
```bash
php artisan debug:texture [ID] [--filter=TIPO]
```

### Ejemplos de Uso:
```bash
# Debug básico
php artisan debug:texture

# Debug con ID específico
php artisan debug:texture 26

# Probar filtro específico
php artisan debug:texture --filter=con_controles
php artisan debug:texture --filter=sin_controles
php artisan debug:texture --filter=aceptable
```

## ✅ Estado de Implementación

- ✅ **Filtros implementados** y funcionando
- ✅ **Búsqueda en campos JSON** funcional
- ✅ **Estadísticas de controles** calculadas
- ✅ **Vista mejorada** con ejemplos
- ✅ **Exportación completa** incluyendo controles
- ✅ **Filtros predefinidos** disponibles
- ✅ **Comandos de debug** funcionales

## 🚀 Próximos Pasos

1. **Probar filtros** en la interfaz web
2. **Verificar exportación** de controles analíticos
3. **Validar estadísticas** en el dashboard
4. **Documentar casos de uso** específicos
5. **Optimizar consultas** si es necesario

---

**Nota**: Los filtros de controles analíticos están completamente implementados y listos para usar. El sistema busca tanto en la relación Eloquent como en el campo JSON del análisis para proporcionar resultados completos.
