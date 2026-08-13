<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Manual de usuario', href: '/admin/ayuda' },
];

function descargarPDF() {
    window.print();
}
</script>

<template>
    <Head title="Manual de usuario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">

            <!-- Encabezado -->
            <div class="d-flex align-items-start justify-content-between mb-4 no-print-hide">
                <div>
                    <h1 class="h2 fw-bold mb-1">Manual de usuario</h1>
                    <p class="text-body-secondary mb-0">Guía completa para administrar el contenido del sitio.</p>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2 no-print" @click="descargarPDF">
                    <i class="bi bi-download"></i>
                    Descargar PDF
                </button>
            </div>

            <!-- Portada (solo visible al imprimir) -->
            <div class="print-cover d-none">
                <h1>Manual de usuario</h1>
                <h2>Hyperion CMS</h2>
                <p>Guía para administradores y editores de contenido</p>
            </div>

            <!-- Índice -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3"><i class="bi bi-list-ul me-2 text-primary"></i>Contenido</h2>
                    <ol class="mb-0 ps-3">
                        <li><a href="#seccion-1" class="text-decoration-none">Inicio de sesión y navegación</a></li>
                        <li><a href="#seccion-2" class="text-decoration-none">Gestión de contenidos</a></li>
                        <li><a href="#seccion-3" class="text-decoration-none">Medios, categorías y menús</a></li>
                        <li><a href="#seccion-4" class="text-decoration-none">Usuarios y roles</a></li>
                    </ol>
                </div>
            </div>

            <!-- ── SECCIÓN 1: Inicio de sesión ── -->
            <div id="seccion-1" class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="h5 fw-bold mb-0"><span class="badge bg-white text-primary me-2">1</span>Inicio de sesión y navegación</h2>
                </div>
                <div class="card-body">

                    <h3 class="h6 fw-bold mt-2 mb-2"><i class="bi bi-box-arrow-in-right me-2 text-primary"></i>Cómo ingresar al sistema</h3>
                    <ol class="mb-3">
                        <li>Abra su navegador y escriba la dirección del panel de administración (ej.: <code>www.sudominio.com/admin</code>).</li>
                        <li>Ingrese su <strong>correo electrónico</strong> y <strong>contraseña</strong> en los campos correspondientes.</li>
                        <li>Haga clic en el botón <strong>Iniciar sesión</strong>.</li>
                        <li>Si olvidó su contraseña, haga clic en <strong>"¿Olvidó su contraseña?"</strong> e ingrese su correo para recibir un enlace de recuperación.</li>
                    </ol>

                    <div class="alert alert-info py-2 mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Por seguridad, la sesión se cierra automáticamente después de un periodo de inactividad. Si ve la pantalla de inicio de sesión de nuevo, simplemente vuelva a ingresar sus datos.
                    </div>

                    <h3 class="h6 fw-bold mt-3 mb-2"><i class="bi bi-speedometer2 me-2 text-primary"></i>El tablero principal (Dashboard)</h3>
                    <p>Al ingresar verá el <strong>Dashboard</strong>, que muestra:</p>
                    <ul class="mb-3">
                        <li><strong>Contadores</strong>: total de contenidos, archivos, categorías y usuarios.</li>
                        <li><strong>Módulos</strong>: accesos rápidos a las secciones más usadas.</li>
                        <li><strong>Estado del sistema</strong>: indica si la base de datos, el almacenamiento y el caché funcionan correctamente.</li>
                    </ul>

                    <h3 class="h6 fw-bold mt-3 mb-2"><i class="bi bi-layout-sidebar me-2 text-primary"></i>Navegación del panel</h3>
                    <p>En la parte izquierda (o superior en móvil) encontrará el menú principal con acceso a:</p>
                    <ul class="mb-0">
                        <li><strong>Contenidos</strong> — artículos y páginas del sitio.</li>
                        <li><strong>Categorías</strong> — clasificación del contenido.</li>
                        <li><strong>Medios</strong> — imágenes y archivos subidos.</li>
                        <li><strong>Menús</strong> — estructura de navegación del sitio.</li>
                        <li><strong>Usuarios</strong> — personas con acceso al sistema.</li>
                        <li><strong>Configuración</strong> — ajustes generales del sitio.</li>
                        <li><strong>Manual de usuario</strong> — esta guía.</li>
                    </ul>
                </div>
            </div>

            <!-- ── SECCIÓN 2: Contenidos ── -->
            <div id="seccion-2" class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="h5 fw-bold mb-0"><span class="badge bg-white text-primary me-2">2</span>Gestión de contenidos</h2>
                </div>
                <div class="card-body">

                    <h3 class="h6 fw-bold mt-2 mb-2"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Crear un nuevo contenido</h3>
                    <ol class="mb-3">
                        <li>En el menú lateral, haga clic en <strong>Contenidos</strong>.</li>
                        <li>Haga clic en el botón <strong>Nuevo contenido</strong> (esquina superior derecha).</li>
                        <li>Complete los campos del formulario:
                            <ul class="mt-1">
                                <li><strong>Título</strong>: nombre del artículo o página.</li>
                                <li><strong>Tipo</strong>: elija entre <em>Post</em> (artículo de blog), <em>Página</em> (contenido estático) o <em>Personalizado</em>.</li>
                                <li><strong>Categoría</strong>: clasifique el contenido (opcional).</li>
                                <li><strong>Cuerpo</strong>: escriba o pegue el contenido usando el editor de texto enriquecido.</li>
                                <li><strong>Imagen principal</strong>: suba una imagen representativa.</li>
                            </ul>
                        </li>
                        <li>Cuando termine, elija una de las opciones:
                            <ul class="mt-1">
                                <li><strong>Guardar borrador</strong>: guarda el contenido pero no lo muestra en el sitio.</li>
                                <li><strong>Publicar</strong>: lo hace visible en el sitio web inmediatamente.</li>
                            </ul>
                        </li>
                    </ol>

                    <h3 class="h6 fw-bold mt-3 mb-2"><i class="bi bi-pencil-square me-2 text-primary"></i>Editar un contenido existente</h3>
                    <ol class="mb-3">
                        <li>Vaya a <strong>Contenidos</strong> en el menú lateral.</li>
                        <li>Busque el contenido en la lista (puede usar el buscador o filtrar por estado).</li>
                        <li>Haga clic en el botón <strong>Editar</strong> (ícono de lápiz) en la fila correspondiente.</li>
                        <li>Modifique los campos que necesite y haga clic en <strong>Actualizar</strong>.</li>
                    </ol>

                    <h3 class="h6 fw-bold mt-3 mb-2"><i class="bi bi-send me-2 text-primary"></i>Publicar o archivar contenido</h3>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Acción</th>
                                    <th>Qué hace</th>
                                    <th>Cómo hacerlo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-success">Publicar</span></td>
                                    <td>Muestra el contenido en el sitio web.</td>
                                    <td>Botón <strong>Publicar</strong> en la lista o dentro del editor.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">Borrador</span></td>
                                    <td>Guarda el contenido sin mostrarlo en el sitio.</td>
                                    <td>Botón <strong>Guardar borrador</strong> al crear o editar.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">Archivar</span></td>
                                    <td>Oculta el contenido sin eliminarlo. Se puede restaurar.</td>
                                    <td>Menú de opciones (⋮) → <strong>Archivar</strong>.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h3 class="h6 fw-bold mt-3 mb-2"><i class="bi bi-translate me-2 text-primary"></i>Traducción automática</h3>
                    <p class="mb-0">Al crear o editar un contenido, puede usar el botón <strong>Traducir con IA</strong> para generar automáticamente una versión en inglés del título y el cuerpo. Revise siempre el resultado antes de publicar.</p>
                </div>
            </div>

            <!-- ── SECCIÓN 3: Medios, categorías y menús ── -->
            <div id="seccion-3" class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="h5 fw-bold mb-0"><span class="badge bg-white text-primary me-2">3</span>Medios, categorías y menús</h2>
                </div>
                <div class="card-body">

                    <h3 class="h6 fw-bold mt-2 mb-2"><i class="bi bi-images me-2 text-primary"></i>Biblioteca de medios</h3>
                    <p>Aquí se almacenan todas las imágenes y archivos del sitio.</p>
                    <ul class="mb-3">
                        <li><strong>Subir archivo</strong>: haga clic en <strong>Subir</strong>, seleccione el archivo desde su computador y espere a que termine de cargarse.</li>
                        <li><strong>Formatos aceptados</strong>: JPG, PNG, GIF, WebP, PDF, entre otros.</li>
                        <li><strong>Eliminar</strong>: haga clic en el ícono de papelera sobre la imagen. Esta acción no se puede deshacer.</li>
                    </ul>
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Importante:</strong> no elimine imágenes que estén siendo usadas en contenidos publicados, ya que quedarán rotas en el sitio.
                    </div>

                    <h3 class="h6 fw-bold mt-3 mb-2"><i class="bi bi-tags me-2 text-primary"></i>Categorías</h3>
                    <p>Las categorías organizan el contenido en grupos temáticos.</p>
                    <ul class="mb-3">
                        <li><strong>Crear categoría</strong>: vaya a <strong>Categorías → Nueva categoría</strong>, ingrese el nombre y haga clic en <strong>Guardar</strong>.</li>
                        <li><strong>Subcategorías</strong>: al crear una categoría puede asignarle una categoría "padre" para crear jerarquías.</li>
                        <li><strong>Editar / Eliminar</strong>: use los íconos de lápiz o papelera en la lista. Solo se puede eliminar una categoría si no tiene contenidos asociados.</li>
                    </ul>

                    <h3 class="h6 fw-bold mt-3 mb-2"><i class="bi bi-list-nested me-2 text-primary"></i>Menús de navegación</h3>
                    <p>Los menús controlan la navegación visible en el sitio web.</p>
                    <ol class="mb-0">
                        <li>Vaya a <strong>Menús</strong> en el panel lateral.</li>
                        <li>Seleccione el menú que desea editar o cree uno nuevo.</li>
                        <li>Para <strong>agregar un ítem</strong>: complete el campo de texto con la etiqueta y la URL, luego haga clic en <strong>Agregar</strong>.</li>
                        <li>Para <strong>reordenar</strong>: arrastre los ítems con el ícono de puntos (⠿) hacia arriba o abajo.</li>
                        <li>Para <strong>eliminar un ítem</strong>: haga clic en el ícono de papelera junto al ítem.</li>
                        <li>Los cambios se guardan automáticamente.</li>
                    </ol>
                </div>
            </div>

            <!-- ── SECCIÓN 4: Usuarios y roles ── -->
            <div id="seccion-4" class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="h5 fw-bold mb-0"><span class="badge bg-white text-primary me-2">4</span>Usuarios y roles</h2>
                </div>
                <div class="card-body">

                    <div class="alert alert-secondary py-2 mb-3">
                        <i class="bi bi-shield-lock me-2"></i>
                        Esta sección solo es visible para usuarios con rol <strong>Administrador</strong>.
                    </div>

                    <h3 class="h6 fw-bold mt-2 mb-2"><i class="bi bi-person-plus me-2 text-primary"></i>Crear un nuevo usuario</h3>
                    <ol class="mb-3">
                        <li>Vaya a <strong>Usuarios → Nuevo usuario</strong>.</li>
                        <li>Complete el formulario:
                            <ul class="mt-1">
                                <li><strong>Nombre</strong>: nombre completo de la persona.</li>
                                <li><strong>Correo electrónico</strong>: se usará para iniciar sesión.</li>
                                <li><strong>Contraseña</strong>: mínimo 8 caracteres. El usuario puede cambiarla después.</li>
                                <li><strong>Rol</strong>: define qué puede hacer en el sistema (ver tabla abajo).</li>
                            </ul>
                        </li>
                        <li>Haga clic en <strong>Crear usuario</strong>.</li>
                    </ol>

                    <h3 class="h6 fw-bold mt-3 mb-2"><i class="bi bi-person-badge me-2 text-primary"></i>Roles disponibles</h3>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Rol</th>
                                    <th>Qué puede hacer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-danger">Administrador</span></td>
                                    <td>Acceso total: contenidos, usuarios, configuración, roles y SEO.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-primary">Editor</span></td>
                                    <td>Crear, editar y publicar contenidos, subir medios y gestionar categorías.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">Visualizador</span></td>
                                    <td>Solo lectura: puede ver el contenido pero no modificarlo.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h3 class="h6 fw-bold mt-3 mb-2"><i class="bi bi-person-check me-2 text-primary"></i>Activar o desactivar usuarios</h3>
                    <ul class="mb-3">
                        <li><strong>Desactivar</strong>: el usuario no podrá iniciar sesión pero sus datos se conservan. Útil cuando alguien deja la organización temporalmente.</li>
                        <li><strong>Activar</strong>: restaura el acceso de un usuario desactivado.</li>
                        <li>Ambas opciones están disponibles en el menú de opciones (⋮) de cada usuario en la lista.</li>
                    </ul>

                    <h3 class="h6 fw-bold mt-3 mb-2"><i class="bi bi-key me-2 text-primary"></i>Cambiar contraseña</h3>
                    <p class="mb-0">Cada usuario puede cambiar su propia contraseña desde el menú de su perfil (esquina superior derecha) → <strong>Seguridad</strong>. Los administradores pueden restablecerla al editar el usuario.</p>
                </div>
            </div>

            <!-- Pie de página del manual -->
            <div class="text-center text-body-secondary small py-3 border-top">
                Hyperion CMS &mdash; Manual de usuario &mdash; Versión 1.0
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    /* Oculta el layout del admin y deja solo el contenido del manual */
    :deep(header),
    :deep(nav),
    :deep(aside),
    :deep(.sidebar),
    :deep([class*="sidebar"]),
    :deep([class*="navbar"]),
    :deep([class*="breadcrumb"]),
    :deep(footer) {
        display: none !important;
    }

    :deep(main),
    :deep(.main-content),
    :deep([class*="main"]) {
        padding: 0 !important;
        margin: 0 !important;
    }

    .no-print {
        display: none !important;
    }

    .print-cover {
        display: flex !important;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
        text-align: center;
        page-break-after: always;
    }

    .card {
        break-inside: avoid;
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
        margin-bottom: 1.5rem !important;
    }

    .card-header {
        background-color: #0d6efd !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    a {
        color: inherit !important;
        text-decoration: none !important;
    }
}
</style>
