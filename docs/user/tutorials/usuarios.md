# Usuarios y Permisos

## Objetivo
Aprender a crear usuarios, asignar roles y gestionar permisos en el sistema.

---

## Requisitos
- Permiso: `rol leer`, `rol crear`

---

## 1. Matriz de Permisos

1. Andá a **Administración → Usuarios y Roles → Ver / Asignar Permisos**.
2. Se muestra una tabla con todos los usuarios y una matriz de permisos.
3. Cada columna representa un permiso (`leer`, `crear`, `editar`, `borrar`) para cada módulo.

![Matriz de permisos][screenshot-permisos]

### Asignar permisos a un usuario

1. Buscá el usuario en la tabla.
2. Marcá los checkboxes de los permisos que querés asignar.
3. Hacé clic en **Guardar**.

> 📝 Los cambios de permisos se aplican inmediatamente.

---

## 2. Crear un Nuevo Usuario

1. Andá a **Administración → Usuarios y Roles → Crear Usuario**.
2. Completá:

| Campo | Obligatorio |
|-------|:-----------:|
| **Nombre** | ✅ |
| **Email** | ✅ |
| **Contraseña** | ✅ |
| **Confirmar contraseña** | ✅ |

3. Hacé clic en **Guardar**.
4. Luego asignale los permisos desde la matriz de permisos.

---

## 3. Permisos Disponibles

Cada módulo tiene las siguientes acciones:

| Módulo | Permisos |
|--------|----------|
| **Productos** | leer, crear, editar, borrar |
| **Clientes** | leer, crear, editar, borrar |
| **Proveedores** | leer, crear, editar, borrar |
| **Compras** | leer, crear, editar, borrar |
| **Ventas** | leer, crear, editar, borrar |
| **Caja** | leer, crear, editar, borrar |
| **Gastos** | leer, crear, editar, borrar |
| **Cheques** | leer, crear, editar, borrar |
| **Reportes** | leer, crear, editar, borrar |
| **Usuarios/Roles** | leer, crear, editar, borrar |
| **% por Cuota** | leer, modificar |
| **Configuración** | modificar |

### Ejemplos de perfiles típicos

| Perfil | Permisos sugeridos |
|--------|-------------------|
| **Cajero** | ventas (crear), caja (leer), clientes (leer) |
| **Depósito** | productos (leer, crear), compras (crear), proveedores (leer) |
| **Administrador** | todos los permisos |
| **Gerente** | todos (leer), reportes (leer), sin permisos de borrar |

---

## 4. Buenas Prácticas

- ✅ Asigná solo los permisos necesarios para cada rol.
- ✅ Revisá periódicamente los permisos de los usuarios.
- ✅ No compartas cuentas de usuario; cada persona debe tener la suya.
- ⚠️ El permiso `borrar` es destructivo: asignalo solo a usuarios de confianza.

---

[screenshot-permisos]: https://placehold.co/800x500/1e293b/f1f5f9?text=Matriz+de+Permisos
