# Migración de Almacenamiento al Módulo LSCEFA

## Cambios Realizados

### 1. Estructura de Directorios
Se creó la siguiente estructura dentro del módulo:
```
Modules/LSCEFA/storage/
├── comprobantes/          # Comprobantes de pago
│   └── {quote_id}/       # Organizados por cotización
├── comunicaciones/        # Archivos de comunicación
└── README.md             # Documentación
```

### 2. Configuración
- Se creó `Config/storage.php` con las rutas de almacenamiento
- Se registró la configuración en `LSCEFAServiceProvider.php`

### 3. Controladores Modificados

#### QuoteController.php
- **Método `upload()`**: Ahora guarda en `Modules/LSCEFA/storage/comprobantes/`
- **Método `startProcess()`**: Guarda comprobantes y comunicaciones dentro del módulo
- **Método `downloadComprobante()`**: Descarga desde la ubicación del módulo
- **Método `downloadCommunicationFile()`**: Descarga desde la ubicación del módulo

#### QuoteFileController.php
- **Método `store()`**: Guarda archivos dentro del módulo
- **Método `destroy()`**: Elimina archivos usando rutas del módulo

### 4. Comando de Limpieza
Se creó `CleanupStorageCommand.php` para limpiar archivos antiguos:
```bash
php artisan lscefa:cleanup-storage --days=30
```

### 5. Archivos de Configuración
- `.gitignore`: Ignora archivos de storage
- `README.md`: Documentación del storage
- `STORAGE_MIGRATION.md`: Esta documentación

## Beneficios

1. **Modularidad**: Todo el almacenamiento está contenido dentro del módulo
2. **Portabilidad**: El módulo es completamente independiente
3. **Organización**: Archivos organizados por tipo y cotización
4. **Mantenimiento**: Comando para limpiar archivos antiguos
5. **Seguridad**: Rutas protegidas y validaciones

## Uso

### Subir Comprobante
```php
// Los archivos se guardan automáticamente en:
Modules/LSCEFA/storage/comprobantes/{quote_id}/
```

### Descargar Comprobante
```php
// Se descargan desde:
Modules/LSCEFA/storage/comprobantes/{quote_id}/{filename}
```

### Limpiar Archivos Antiguos
```bash
# Eliminar archivos más antiguos de 30 días
php artisan lscefa:cleanup-storage --days=30
```

## Configuración

Las rutas se configuran en `Config/storage.php`:
```php
'comprobantes_path' => base_path('Modules/LSCEFA/storage/comprobantes'),
'comunicaciones_path' => base_path('Modules/LSCEFA/storage/comunicaciones'),
```

## Notas Importantes

1. Los directorios se crean automáticamente si no existen
2. Los permisos se establecen en 777 para permitir escritura
3. Los archivos se validan por tipo y tamaño antes de guardar
4. Las rutas de descarga están protegidas por permisos de usuario 