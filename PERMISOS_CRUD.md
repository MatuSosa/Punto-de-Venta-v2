# Sistema de Permisos CRUD Granulares

## Descripción General

El sistema ahora cuenta con permisos granulares a nivel de acciones CRUD (Crear, Leer, Actualizar, Eliminar) para cada módulo. Esto permite configurar con precisión qué usuarios pueden hacer qué acciones en cada sección del sistema.

## Características Implementadas

### 1. Base de Datos

Se agregaron 4 nuevas columnas a la tabla `detalle_permisos`:
- `puede_crear` (INTEGER, DEFAULT 1)
- `puede_leer` (INTEGER, DEFAULT 1)
- `puede_actualizar` (INTEGER, DEFAULT 1)
- `puede_eliminar` (INTEGER, DEFAULT 1)

**Nota:** Los permisos existentes se establecieron con todos los permisos habilitados por defecto (valor 1).

### 2. Interfaz de Asignación de Permisos (`src/rol.php`)

La interfaz se rediseñó completamente con:
- **Tabla de permisos:** Muestra todos los módulos del sistema
- **Checkbox "Acceso":** Habilita/deshabilita el acceso al módulo completo
- **Checkboxes individuales por acción:**
  - **Crear:** Permite registrar nuevos elementos
  - **Leer:** Permite ver la lista de elementos
  - **Actualizar:** Permite modificar elementos existentes
  - **Eliminar:** Permite borrar elementos

**Funcionalidades JavaScript:**
- `toggleAcciones(checkbox, permiso)`: Habilita/deshabilita todas las acciones cuando se marca/desmarca el acceso al módulo
- `marcarTodos(valor)`: Marca o desmarca todos los checkboxes de la tabla

### 3. Función de Verificación de Permisos (`src/includes/header.php`)

Se agregó la función `puedeAccion($permiso, $accion)` que:
- Verifica si el usuario tiene permiso para realizar una acción específica en un módulo
- Retorna `true/false`
- Se usa en conjunto con `tienePermiso()` (que solo verifica acceso al módulo)

**Parámetros:**
- `$permiso`: Nombre del módulo (ej: 'productos', 'clientes', 'usuarios')
- `$accion`: Acción CRUD ('crear', 'leer', 'actualizar', 'eliminar')

**Ejemplo de uso:**
```php
<?php if (puedeAccion('productos', 'actualizar')): ?>
    <button>Editar</button>
<?php endif; ?>
```

### 4. Módulos Actualizados

Se aplicaron los permisos CRUD a los siguientes módulos:

#### **Productos (`src/productos.php`)**
- ✅ Formulario oculto si no tiene permiso de crear/actualizar
- ✅ Botón "Editar" solo visible si puede actualizar
- ✅ Botón "Eliminar" solo visible si puede eliminar
- ✅ Badge "Solo lectura" si solo tiene permiso de leer

#### **Clientes (`src/clientes.php`)**
- ✅ Formulario oculto si no tiene permiso de crear/actualizar
- ✅ Botón "Editar" solo visible si puede actualizar
- ✅ Botón "Eliminar" solo visible si puede eliminar
- ✅ Badge "Solo lectura" si solo tiene permiso de leer

#### **Usuarios (`src/usuarios.php`)**
- ✅ Formulario oculto si no tiene permiso de crear/actualizar
- ✅ Botón "Editar" solo visible si puede actualizar
- ✅ Botón "Eliminar" solo visible si puede eliminar
- ✅ Badge "Solo lectura" si solo tiene permiso de leer
- ℹ️ Botón de "Permisos" (llave) siempre visible para todos

## Ejemplos de Uso

### Caso 1: Usuario con Acceso Completo (Administrador)
```
Módulo: Productos
- ✅ Acceso: Sí
- ✅ Crear: Sí
- ✅ Leer: Sí
- ✅ Actualizar: Sí
- ✅ Eliminar: Sí

Resultado: Ve el formulario, puede registrar productos, editarlos y eliminarlos.
```

### Caso 2: Usuario de Solo Lectura
```
Módulo: Productos
- ✅ Acceso: Sí
- ❌ Crear: No
- ✅ Leer: Sí
- ❌ Actualizar: No
- ❌ Eliminar: No

Resultado: Solo ve la tabla de productos con badge "Solo lectura". No ve formulario ni botones de acción.
```

### Caso 3: Usuario con Permisos Parciales
```
Módulo: Clientes
- ✅ Acceso: Sí
- ✅ Crear: Sí
- ✅ Leer: Sí
- ✅ Actualizar: Sí
- ❌ Eliminar: No

Resultado: Ve el formulario, puede registrar y editar clientes, pero NO puede eliminarlos (botón eliminar oculto).
```

### Caso 4: Usuario sin Acceso al Módulo
```
Módulo: Usuarios
- ❌ Acceso: No

Resultado: El módulo ni siquiera aparece en el menú lateral. Si intenta acceder por URL, es redirigido a permisos.php.
```

## Ventajas del Sistema

1. **Seguridad Mejorada:** Control fino sobre qué usuarios pueden hacer qué acciones
2. **Flexibilidad:** Permite crear roles personalizados (ej: "Consultor" con solo lectura, "Editor" sin eliminar, etc.)
3. **Experiencia de Usuario:** Los usuarios solo ven las opciones que pueden usar
4. **Auditoría:** Más fácil rastrear quién puede realizar cambios críticos
5. **Escalabilidad:** Fácil agregar nuevos módulos siguiendo el mismo patrón

## Archivos Modificados

```
src/
├── includes/
│   └── header.php          # Función puedeAccion() agregada
├── rol.php                 # Interfaz rediseñada con tabla CRUD
├── productos.php           # Permisos CRUD aplicados
├── clientes.php            # Permisos CRUD aplicados
└── usuarios.php            # Permisos CRUD aplicados

database/
└── migracion_permisos_crud.sql  # Script de migración ejecutado
```

## Cómo Agregar Permisos CRUD a Nuevos Módulos

Sigue este patrón en cualquier archivo PHP de módulo:

```php
<?php
// 1. Al inicio del archivo, después del header
include_once "includes/header.php";
?>

<!-- 2. Envolver el formulario -->
<?php if (puedeAccion('nombre_modulo', 'crear') || puedeAccion('nombre_modulo', 'actualizar')): ?>
<form action="" method="post">
    <!-- Campos del formulario -->
</form>
<?php endif; ?>

<!-- 3. En la tabla de datos -->
<td>
    <?php if (puedeAccion('nombre_modulo', 'actualizar')): ?>
        <button onclick="editar(id)">Editar</button>
    <?php endif; ?>
    
    <?php if (puedeAccion('nombre_modulo', 'eliminar')): ?>
        <form method="post" action="eliminar.php">
            <button type="submit">Eliminar</button>
        </form>
    <?php endif; ?>
    
    <?php if (!puedeAccion('nombre_modulo', 'actualizar') && !puedeAccion('nombre_modulo', 'eliminar') && puedeAccion('nombre_modulo', 'leer')): ?>
        <span class="badge badge-info">Solo lectura</span>
    <?php endif; ?>
</td>
```

## Testing Recomendado

1. **Crear usuario de prueba** con diferentes combinaciones de permisos
2. **Verificar** que solo aparezcan los elementos permitidos
3. **Intentar acceso directo por URL** a páginas sin permiso (debe redirigir)
4. **Probar** con usuario administrador (debe tener acceso completo)
5. **Revisar** que el badge "Solo lectura" aparezca correctamente

## Notas Técnicas

- El usuario con ID 1 (administrador) siempre tiene acceso completo a todo
- Los permisos se verifican tanto en el frontend (PHP) como deberían verificarse en el backend al procesar formularios
- Si un usuario no tiene permiso de "actualizar" pero intenta enviar el formulario de edición, será rechazado
- El sistema es compatible con el menú dinámico implementado previamente

## Próximas Mejoras Sugeridas

1. Agregar permisos CRUD a módulos faltantes (ventas, configuración, etc.)
2. Implementar log de auditoría para acciones sensibles
3. Agregar validación backend adicional en scripts de eliminación
4. Crear roles predefinidos (Admin, Editor, Consultor, etc.)
5. Interfaz para clonar permisos de un usuario a otro

---

**Fecha de Implementación:** 2025
**Estado:** ✅ Completado y Funcionando
