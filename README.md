# AgroLink™ — Prototipo funcional (Sprint 1 + Sprint 2)

Prototipo en PHP + MySQL del proyecto AgroLink™ (SC-505 Administración de Proyectos).
Cubre las historias de usuario de **Sprint 1** (identidad y publicación) y **Sprint 2**
(catálogo, búsqueda y pedidos) definidas en el backlog del Avance 3.

## Requisitos
- PHP 8.x con extensión `pdo_mysql` (o `mysqli`)
- MySQL / MariaDB (puede ser el que trae XAMPP/Laragon, o una instancia local)
- VS Code con la extensión **PHP Server** (o cualquier servidor PHP embebido)

## Instalación

1. **Crear la base de datos**
   Importa `database/schema.sql` en tu MySQL (incluye tablas + datos de prueba):
   ```
   mysql -u root -p < database/schema.sql
   ```
   O ábrelo en phpMyAdmin / MySQL Workbench y ejecútalo completo.

2. **Configurar la conexión**
   Edita `config/db.php` si tu usuario/contraseña de MySQL son distintos a
   `root` / (vacío):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'agrolink');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

3. **Levantar el servidor**
   - Con la extensión **PHP Server** de VS Code: clic derecho sobre
     `public/index.php` → "PHP Server: Serve project".
   - O por línea de comandos:
     ```
     cd public
     php -S localhost:8000
     ```
   Luego abre `http://localhost:8000` en el navegador.

## Cuentas de demostración
Contraseña para todas: `agrolink123`

| Rol         | Correo                  |
|-------------|--------------------------|
| Agricultor  | hector@agrolink.test     |
| Agricultor  | diomer@agrolink.test     |
| Consumidor  | fabian@agrolink.test     |

## Estructura del proyecto

```
agrolink/
├── config/db.php          # Conexión PDO a MySQL
├── includes/               # auth.php (sesión/helpers), header.php, footer.php
├── database/schema.sql     # Esquema + datos de prueba
└── public/                 # Front-end servible (raíz del servidor)
    ├── index.php                  Landing
    ├── registro_agricultor.php    HU-01
    ├── registro_consumidor.php    HU-02
    ├── login.php                  HU-03
    ├── recuperar.php              HU-17
    ├── perfil.php                 HU-04
    ├── productos_publicar.php     HU-05 / HU-07
    ├── productos_mios.php         HU-06
    ├── catalogo.php               HU-08 / HU-15
    ├── producto.php               HU-09 (detalle)
    ├── carrito.php                HU-09 (carrito)
    ├── checkout.php               HU-12
    ├── pedidos_agricultor.php     HU-10 / HU-14
    ├── pedidos_consumidor.php     HU-11 / HU-13
    ├── perfil_publico.php         HU-16
    └── notificaciones.php         HU-14
```

## Historias de usuario cubiertas

**Sprint 1 — Identidad y Publicación**
HU-01, HU-02, HU-03, HU-04, HU-05, HU-06, HU-07, HU-17

**Sprint 2 — Catálogo, Búsqueda y Pedidos**
HU-08, HU-09, HU-10, HU-11, HU-12, HU-13, HU-14, HU-15, HU-16

## Notas del prototipo (simplificaciones a mencionar en el documento)
- **HU-15 (Geolocalización):** se aproxima comparando el texto de ubicación
  del comprador y del agricultor (no usa GPS real ni coordenadas). Para producción
  se integraría un API de geocodificación (Google Maps / OpenStreetMap).
- **HU-12 (Pago digital):** el pago es simulado (no se conecta a una pasarela
  real de SINPE/tarjeta); el flujo de custodia (escrow) y liberación de fondos
  sí está implementado a nivel de estados del pedido.
- **HU-17 (OTP):** el código de recuperación se muestra en pantalla en vez de
  enviarse por correo/SMS, para poder probarlo sin un servicio de mensajería.
- **HU-21 (Modo offline)** y **HU-22 (Cifrado/2FA)** no están implementadas en
  este prototipo; quedan documentadas como historias pendientes para el
  siguiente ciclo de desarrollo.

## No implementado (fuera del alcance de Sprint 1 y 2)
HU-18 (Dashboard financiero), HU-19 (zona de cobertura — el campo existe en BD
pero no se valida aún al hacer un pedido), HU-20 (FAQ), HU-21, HU-22.
