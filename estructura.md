PROMPT COMPLETO — SISTEMA WEB PLÁSTICOS FÉNIX 
🎯 CONTEXTO DEL PROYECTO
Quiero que me ayudes a construir un sistema web completo para la empresa PLÁSTICOS FÉNIX, orientado a la gestión de cotizaciones y pedidos.
Stack tecnológico:

Backend: PHP con Laravel
Base de datos: MySQL
Frontend: Blade + Tailwind CSS
Autenticación y roles: Laravel Breeze + Spatie Laravel Permission
PDF: paquete barryvdh/laravel-dompdf
Interactividad en formularios: Alpine.js


👥 ROLES DEL SISTEMA
Usa Spatie Laravel Permission para gestionar estos 4 roles:
1. Vendedora

Ver y gestionar solo sus propias cotizaciones y pedidos
Seleccionar plantilla antes de crear cotización
Confirmar cotización para convertirla en pedido
Añadir clientes y productos

2. Supervisor

Ver todas las cotizaciones y pedidos de todas las vendedoras
Añadir clientes y productos
Dashboard con métricas: vendedora con más pedidos, producto más vendido, etc.

3. Logístico

Ver solo pedidos (no cotizaciones)
Ver estado de cada pedido y tipo de plantilla asociada

4. Administrador

Acceso total
Gestión de usuarios: crear vendedoras, logísticos y supervisores


🔄 FLUJO DEL NEGOCIO
Vendedora selecciona plantilla (Tratadas / PPS / PETS / Universal)
        ↓
Crea cotización (datos del cliente + tabla de productos)
        ↓
Cliente aprueba → Vendedora presiona "Confirmar Cotización"
        ↓
La cotización se convierte en PEDIDO
        ↓
Logístico visualiza el pedido y gestiona su estado
Estados de cotización: Borrador → Enviada → Aprobada → Convertida a Pedido
Estados de pedido: Pendiente → En Proceso → Despachado → Entregado

🗄️ BASE DE DATOS — MIGRACIONES
Crea las migraciones para estas tablas:
users              → id, name, email, password, role
agencias           → id, nombre, direccion
clientes           → id, nombre, ruc, direccion, condicion_pago, provincia
productos          → id, codigo, nombre, unidad_medida, precio_base
cotizaciones       → id, numero(autogenerado), vendedora_id, cliente_id, plantilla_id,
                     moneda(soles/dolares), estado, fecha_emision, subtotal, igv, total, observaciones
cotizacion_items   → id, cotizacion_id, producto_id, campos_json (para guardar
                     los campos específicos de cada plantilla), precio_unitario, precio_total
pedidos            → id, cotizacion_id, estado, fecha_pedido, fecha_entrega_estimada
plantillas         → id, nombre (Tratadas, PPS, PETS, Universal)

📋 PLANTILLAS DE COTIZACIÓN — TABLAS DE DETALLE
Cada plantilla tiene su propio Blade Component con columnas distintas. Crear en:
resources/views/components/cotizacion/
├── tabla-tratadas.blade.php
├── tabla-pps.blade.php
├── tabla-pets.blade.php
└── tabla-universal.blade.php
PLANTILLA TRATADAS
CampoTipoLógicaItemAutomáticoNúmero de fila autoincrementalCódigo del ProductoAutomáticoSe autocompleta al seleccionar productoProductoSelectorVendedora busca y seleccionaCantidad por MillarManualIngreso libreFardoManualIngreso libreTotal de MillaresCalculadoCantidad por Millar × FardoPrecio UnitarioManualIngreso librePrecio TotalCalculadoTotal de Millares × Precio Unitario
PLANTILLA PPS
CampoTipoLógicaItemAutomáticoNúmero de fila autoincrementalCódigo del ProductoAutomáticoSe autocompleta al seleccionar productoProductoSelectorVendedora busca y seleccionaCantidadManualIngreso libreFardoManualIngreso libreTotal de KilosCalculadoCantidad × FardoPrecio UnitarioManualIngreso librePrecio TotalCalculadoTotal de Kilos × Precio Unitario
PLANTILLA PETS
CampoTipoLógicaItemAutomáticoNúmero de fila autoincrementalCódigo del ProductoAutomáticoSe autocompleta al seleccionar productoProductoSelectorVendedora busca y seleccionaCantidad por MillarManualIngreso libreTotal Caja/Sacos/BolsasManualIngreso libreTotal MillaresCalculadoCantidad por Millar × Total Caja/Sacos/BolsasPrecio UnitarioManualIngreso librePrecio TotalCalculadoTotal Millares × Precio Unitario
PLANTILLA UNIVERSAL
CampoTipoLógicaItemAutomáticoNúmero de fila autoincrementalCódigo del ProductoAutomáticoSe autocompleta al seleccionar productoProductoSelectorVendedora busca y seleccionaCantidadManualIngreso libreUnidad de MedidaAutomáticoSe jala del registro del productoPrecio UnitarioManualIngreso librePrecio TotalCalculadoCantidad × Precio Unitario

🎨 DISEÑO VISUAL DE LA COTIZACIÓN
El formato de cotización debe replicar exactamente este diseño corporativo:
PALETA DE COLORES
Verde oscuro corporativo:  #1a472a  (cabecera, tabla header, totales label)
Dorado/amarillo:           #FFD700  (acentos, borde inferior logo, fila TOTAL)
Blanco:                    #FFFFFF  (fondo general, texto sobre verde)
Gris claro:                #f5f5f5  (fondo de secciones de datos)
Negro/gris oscuro:         #333333  (texto general)
ESTRUCTURA DEL DOCUMENTO (layout A4)
┌─────────────────────────────────────────────────────┐
│  [LOGO FÉNIX - izquierda]       COTIZACIÓN          │
│   Grupo Fénix                   N° - 000000000001   │
├──────────────────────────┬──────────────────────────┤
│  RUC: 20522086704        │  [ícono calendario]      │
│  Dirección empresa       │  FECHA DE EMISION        │
│  (fondo verde oscuro,    │  16 de Abril de 2026     │
│   texto blanco)          │                          │
├──────────────────────────────────────────────────────┤
│ [ícono] CLIENTE: NOMBRE DEL CLIENTE                  │
│ [ícono] RUC: _______     [ícono camión] AGENCIA:    │
│         Número RUC                      Nombre      │
│ [ícono] DIRECCIÓN:       DIRECCIÓN AGENCIA:         │
│         Dirección                       Dirección   │
│ [ícono] CONDICIÓN PAGO:  [ícono] PROVINCIA:         │
│         CONTADO                         LIMA        │
│                          [ícono] MONEDA:            │
│                                   SOLES / DÓLARES   │
│                          [ícono] ATENDIDO POR:      │
│                                   Nombre vendedora  │
├─────────────────────────────────────────────────────┤
│  ITEM │ PRODUCTO │ [cols según plantilla] │  TOTAL  │
│  (cabecera en verde oscuro, texto blanco)           │
├─────────────────────────────────────────────────────┤
│  filas de productos con fondo blanco/gris alternado │
│  (espacio suficiente para ~10 filas vacías)         │
├─────────────────────────────────────────────────────┤
│                              SUB TOTAL  │           │
│                              IGV 18%   │           │
│                              TOTAL     │ (fondo     │
│                                        │  amarillo) │
├─────────────────────────────────────────────────────┤
│ [CUENTAS BANCARIAS BCP/BBVA]  │  [Imagen productos] │
│  Soles y Dólares con números  │  "TU MARCA SIEMPRE  │
│  de cuenta                    │   RELEVANTE"        │
├─────────────────────────────────────────────────────┤
│ SÍGUENOS: [iconos redes]   comercial@plasticosfenix │
└─────────────────────────────────────────────────────┘
DETALLES VISUALES IMPORTANTES
- El logo va en la esquina superior izquierda con el texto "Grupo" arriba
- "COTIZACIÓN" en la esquina superior derecha en verde oscuro, grande y en negrita
- El número de cotización se autogenera con formato: 000000000001
- La sección del RUC/dirección de la empresa va en banda verde oscuro con texto blanco
- La fecha de emisión tiene un ícono de calendario a la izquierda
- Los íconos de cada campo del cliente son pequeños (persona, documento, ubicación, camión, bandera, dinero, persona)
- La cabecera de la tabla de productos es verde oscuro con texto blanco
- Las filas de la tabla alternan entre blanco y gris muy claro
- La fila TOTAL tiene fondo amarillo dorado
- El pie tiene dos columnas: cuentas bancarias a la izquierda, imagen promocional a la derecha
- El footer final tiene redes sociales a la izquierda y email a la derecha

⚙️ INSTRUCCIONES TÉCNICAS
Comportamiento del formulario (Alpine.js)
1. Los campos calculados se actualizan en tiempo real sin recargar página
2. Al seleccionar un producto se autocompletan: código y unidad de medida
3. Cada fila tiene botón para eliminarla (ícono de basura)
4. Botón "Agregar producto" añade nuevas filas dinámicamente
5. El ítem se renumera automáticamente al eliminar filas
6. TOTAL GENERAL = suma de todos los "Precio Total" + IGV 18%
7. El selector de moneda (Soles/Dólares) afecta el símbolo en toda la tabla
Exportación PDF (DomPDF)
- Ruta: GET /cotizaciones/{id}/pdf
- Vista PDF en: resources/views/pdf/cotizacion-{plantilla}.blade.php
- CSS completamente inline (DomPDF no soporta CSS externo)
- Tamaño de papel: A4 vertical
- Los valores calculados vienen resueltos desde el backend (no JS)
- Incluir logo en base64 para que aparezca en el PDF
- Replicar exactamente el diseño visual descrito arriba
Estructura de archivos de vistas
resources/views/
├── cotizaciones/
│   ├── index.blade.php         (listado)
│   ├── create.blade.php        (selección de plantilla)
│   ├── show.blade.php          (ver cotización con botón PDF)
│   └── edit.blade.php
├── components/cotizacion/
│   ├── header.blade.php        (cabecera común)
│   ├── cliente-info.blade.php  (sección de datos del cliente)
│   ├── footer-banco.blade.php  (cuentas bancarias y pie)
│   ├── tabla-tratadas.blade.php
│   ├── tabla-pps.blade.php
│   ├── tabla-pets.blade.php
│   └── tabla-universal.blade.php
└── pdf/
    ├── cotizacion-tratadas.blade.php
    ├── cotizacion-pps.blade.php
    ├── cotizacion-pets.blade.php
    └── cotizacion-universal.blade.php

📦 MÓDULOS — ORDEN DE DESARROLLO
Guíame en este orden exacto:

Configuración inicial Laravel + MySQL + dependencias (Spatie, DomPDF, Alpine.js)
Migraciones y seeders (roles, plantillas, usuario admin por defecto)
Autenticación con roles (Breeze + Spatie)
CRUD de Clientes
CRUD de Productos (con código, nombre, unidad de medida, precio base)
Módulo de Cotizaciones con las 4 plantillas y sus tablas dinámicas
Lógica de confirmar cotización → convertir en pedido
Módulo de Pedidos (estados, filtros por plantilla)
Exportación PDF replicando el diseño de PLÁSTICOS FÉNIX
Dashboard del Supervisor (Chart.js)
Panel del Administrador (gestión de usuarios)

Importante: Pregúntame antes de asumir cualquier decisión de diseño o lógica de negocio que no esté especificada aquí.