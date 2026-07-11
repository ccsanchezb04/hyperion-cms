<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Datos iniciales del sitio público (theme JuanFer Seguros).
 *
 * Idempotente: usa firstOrCreate/updateOrCreate, se puede ejecutar varias veces.
 *
 * Estructura de categorías:
 *   soluciones (umbrella)
 *   ├── salud
 *   ├── vida
 *   ├── movilidad
 *   ├── hogar
 *   ├── empresas
 *   └── rentas
 *
 * Cada Content (solución/seguro publicado) pertenece a UNA categoría hija;
 * la categoría determina su OG image y meta description SEO por categoría.
 */
class SiteSeeder extends Seeder
{
    protected function solutionCategories(): array
    {
        return [
            [
                'slug'          => 'salud',
                'name'          => 'Salud',
                'description'   => 'Cuidamos tu salud y el bienestar de los tuyos.',
                'og_file'       => 'JF_Salud.png',
                'content_slug'  => 'seguro-de-salud',
                'content_title' => 'Seguro de Salud',
                'content_body'  => <<<HTML
<div class="jf-portfolio">
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Sura</h2>
    <ul class="jf-portfolio__products">
      <li>Pólizas de salud</li>
      <li>Salud modular</li>
      <li>Salud para todos integral</li>
      <li>Salud para dos</li>
      <li>Pac 60+</li>
    </ul>
  </div>
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Seguros Bolívar</h2>
    <ul class="jf-portfolio__products">
      <li>Seguro médico internacional</li>
      <li>Salud para disfrutar</li>
      <li>Salud Integral</li>
      <li>Salud a su medida</li>
    </ul>
  </div>
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Axa Colpatria</h2>
    <ul class="jf-portfolio__products">
      <li>Planes de salud premium</li>
      <li>Planes de salud livianos</li>
      <li>Plan international care</li>
    </ul>
  </div>
</div>
HTML,
            ],
            [
                'slug'          => 'vida',
                'name'          => 'Vida',
                'description'   => 'Protegemos tu autonomía, tus ingresos y el futuro de tu familia.',
                'og_file'       => 'JF_Vida.png',
                'content_slug'  => 'seguro-de-vida',
                'content_title' => 'Seguro de Vida',
                'content_body'  => <<<HTML
<div class="jf-portfolio">
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Sura</h2>
    <ul class="jf-portfolio__products">
      <li>Plan vive</li>
      <li>Plan crédito protegido</li>
      <li>Póliza de enfermedades graves</li>
    </ul>
  </div>
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Seguros Bolívar</h2>
    <ul class="jf-portfolio__products">
      <li>Seguro vida individual y familiar</li>
      <li>Seguro vida deudor</li>
      <li>Seguro de accidentes personales</li>
      <li>Seguro para mascotas</li>
    </ul>
  </div>
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Axa Colpatria</h2>
    <ul class="jf-portfolio__products">
      <li>Vida a mi medida</li>
      <li>Vida a mi medida deudor</li>
    </ul>
  </div>
</div>
HTML,
            ],
            [
                'slug'          => 'movilidad',
                'name'          => 'Movilidad',
                'description'   => 'Te acompañamos en cada camino, seguro y sin preocupaciones.',
                'og_file'       => 'JF_Movilidad.png',
                'content_slug'  => 'seguro-de-movilidad',
                'content_title' => 'Seguro de Movilidad',
                'content_body'  => <<<HTML
<div class="jf-portfolio">
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Sura</h2>
    <ul class="jf-portfolio__products">
      <li>Seguro autos</li>
      <li>Seguro utilitarios y pesados</li>
      <li>Seguro motos</li>
      <li>Seguro bicicletas y patinetas</li>
      <li>Seguro de viaje</li>
    </ul>
  </div>
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Seguros Bolívar</h2>
    <ul class="jf-portfolio__products">
      <li>Seguro de autos</li>
      <li>Seguro para híbridos y eléctricos</li>
    </ul>
  </div>
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Axa Colpatria</h2>
    <ul class="jf-portfolio__products">
      <li>Autos livianos</li>
      <li>Autos eléctricos</li>
      <li>Autos pesados</li>
      <li>Motos</li>
      <li>Taxis</li>
    </ul>
  </div>
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Travelkit</h2>
    <ul class="jf-portfolio__products">
      <li>Asistencia en viaje</li>
    </ul>
  </div>
</div>
HTML,
            ],
            [
                'slug'          => 'hogar',
                'name'          => 'Hogar',
                'description'   => 'Protegemos tu lugar seguro, cuidamos tus bienes y patrimonio.',
                'og_file'       => 'JF_Hogar.png',
                'content_slug'  => 'seguro-de-hogar',
                'content_title' => 'Seguro de Hogar',
                'content_body'  => <<<HTML
<div class="jf-portfolio">
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Sura</h2>
    <ul class="jf-portfolio__products">
      <li>Seguro de hogar</li>
      <li>Seguro de arrendamiento</li>
      <li>Responsabilidad Civil Familiar</li>
      <li>Cobertura de ciberseguridad</li>
      <li>Seguro de mascotas</li>
    </ul>
  </div>
</div>
HTML,
            ],
            [
                'slug'          => 'empresas',
                'name'          => 'Empresas',
                'description'   => 'Impulsamos tu crecimiento, protegemos tu inversión, cuidamos tu equipo humano.',
                'og_file'       => 'JF_Empresas.png',
                'content_slug'  => 'seguros-empresariales',
                'content_title' => 'Seguros Empresariales',
                'content_body'  => <<<HTML
<div class="jf-portfolio">
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Sura</h2>
    <ul class="jf-portfolio__products jf-portfolio__products--cols">
      <li>Todo riesgo empresarial</li>
      <li>Seguro de cumplimiento</li>
      <li>Seguro de transporte</li>
      <li>Protección legal empresarial</li>
      <li>Fraude de empleados</li>
      <li>Protección digital</li>
      <li>Vida grupo</li>
      <li>Salud colectiva</li>
      <li>Hogar colectiva</li>
      <li>Autos colectiva</li>
      <li>Responsabilidad Civil</li>
      <li>RC Clínicas y hospitales</li>
      <li>RC Hoteles</li>
      <li>RC Talleres y parqueaderos</li>
      <li>RC Profesionales de la salud</li>
      <li>RC Veterinarios</li>
    </ul>
  </div>
</div>
HTML,
            ],
            [
                'slug'          => 'rentas',
                'name'          => 'Rentas',
                'description'   => 'Brindamos tranquilidad financiera para asegurar tu futuro.',
                'og_file'       => 'JF_Rentas.png',
                'content_slug'  => 'plan-de-rentas',
                'content_title' => 'Plan de Rentas',
                'content_body'  => <<<HTML
<div class="jf-portfolio">
  <div class="jf-portfolio__insurer">
    <h2 class="jf-portfolio__insurer-name">Productos disponibles</h2>
    <ul class="jf-portfolio__products">
      <li>Seguro de educación</li>
      <li>Seguro de pensión (brecha pensional)</li>
    </ul>
  </div>
</div>
HTML,
            ],
        ];
    }

    public function run(): void
    {
        $this->seedCategories();
        $this->seedSettings();
        $this->seedMenu();
        $this->seedCarousel();
        $this->seedSolutions();
        $this->seedTestimonials();
        $this->seedSeoAssets();

        $this->command->info('✅ Site (JuanFer) seeded successfully');
    }

    protected function seedCategories(): void
    {
        // Umbrella
        $parent = Category::firstOrCreate(
            ['cate_cdslug' => 'soluciones'],
            ['cate_nmname' => 'Soluciones', 'cate_idpare' => null]
        );

        // 6 categorías hijas
        foreach ($this->solutionCategories() as $cat) {
            Category::updateOrCreate(
                ['cate_cdslug' => $cat['slug']],
                ['cate_nmname' => $cat['name'], 'cate_idpare' => $parent->cate_idcate]
            );
        }

        Category::firstOrCreate(
            ['cate_cdslug' => 'testimonios'],
            ['cate_nmname' => 'Testimonios', 'cate_idpare' => null]
        );

        Category::firstOrCreate(
            ['cate_cdslug' => 'carousel'],
            ['cate_nmname' => 'Carrusel', 'cate_idpare' => null]
        );

        Category::firstOrCreate(
            ['cate_cdslug' => 'aliados'],
            ['cate_nmname' => 'Aliados', 'cate_idpare' => null]
        );
    }

    protected function seedSettings(): void
    {
        $siteValues = [
            'site.heading' => 'JuanFer Seguros',
            'site.tagline' => 'Protección al instante, duradera y humana.',

            'site.about.heading' => 'Somos JuanFer Seguros',
            'site.about.intro' => 'Nuestra propuesta va más allá del servicio: buscamos fidelizar, '
                . 'acompañar y agregar valor constantemente. Recordamos a nuestros clientes lo que han ganado al '
                . 'protegerse, los celebramos en fechas especiales y respondemos con agilidad. Con Juanfer Seguros, '
                . 'vivirás una experiencia cercana, rápida y confiable. Protección al instante y duradera, con un '
                . 'equipo que siempre está pensando en ti.',
            'site.about.vision_title' => 'Visión',
            'site.about.vision_lead' => 'Impactar vidas, transformar el sector.',
            'site.about.vision_body' => 'Nuestra visión es consolidar una empresa con valores sólidos, enfocada '
                . 'en el servicio al cliente y en la transformación digital. Para el año 2030, queremos superar los '
                . '3.000 clientes bajo el código 29992 y construir una cartera superior a los $10.000.000.000. Para '
                . 'lograrlo, nos estamos preparando comercialmente, formarémos un equipo de asesores comprometidos '
                . 'y enfocaremos nuestros esfuerzos en pólizas empresariales y de vida individual. Queremos ser '
                . 'referente en el sector por resultados, pero sobre todo por impacto humano.',
            'site.about.mission_title' => 'Misión',
            'site.about.mission_body' => 'Asegurar con empatía, servir con propósito. Mi misión es ofrecer '
                . 'soluciones de seguros tanto de forma presencial como digital, dirigidas a personas, familias y '
                . 'empresarios en todo el país. Lo hago con empatía, claridad y un servicio al cliente cercano, '
                . 'oportuno y honesto. Cada cliente merece sentirse acompañado y comprendido.',

            'site.solutions.heading' => 'Nuestras Soluciones',
            'site.solutions.cta_all' => 'Ver todas las soluciones',

            'site.testimonials.heading' => 'Nuestros clientes han confiado en nosotros',

            'site.contact.heading' => 'Estamos más cerca de ti',
            'site.contact.social_placeholder' => 'Aquí van las redes sociales',

            'site.footer.copy' => '© 2025 Juanfer Seguros. Todos los derechos reservados.',
            'site.footer.address' => 'CC Viva Envigado - Torre de oficinas, Piso 10, Envigado, Antioquia.',

            'site.contact.notify_email' => 'admin@hyperion.local',
        ];

        foreach ($siteValues as $key => $value) {
            Setting::setValue($key, $value, 'site');
        }

        // ── SEO (público, consumido por SiteSeoService + SiteHead.vue) ─────
        $seoValues = [
            'site.seo.title_template'    => '{page} | JuanFer Seguros',
            'site.seo.description'       => 'Protección familiar, patrimonial y empresarial. '
                . 'Asesoría experta, cercana y confiable',
            'site.seo.keywords'          => 'Seguros, póliza, vida, auto, moto, hogar, empresarial, todo riesgo, '
                . 'JuanFer, asesor de seguros, salud, protección financiera, financiación, crédito, '
                . 'responsabilidad civil, planes complementarios',
            'site.seo.og_description'    => 'Expertos en seguros y protección financiera. '
                . 'Tu tranquilidad, nuestra prioridad.',
            'site.seo.og_image'          => '/storage/site/seo/og-default.jpg',
            'site.seo.twitter_handle'    => '',
            'site.seo.locale'            => 'es_CO',
            'site.seo.robots'            => 'index,follow',
            'site.seo.canonical_host'    => 'https://juanferseguros.com',
            'site.seo.schema_type'       => 'InsuranceAgency',
            'site.seo.logo'              => '/storage/site/seo/logo.png',
        ];

        foreach ($seoValues as $key => $value) {
            Setting::setValue($key, $value, 'seo');
        }

        // OG image + meta description por categoría (6 cliente-provistas)
        foreach ($this->solutionCategories() as $cat) {
            Setting::setValue(
                "site.seo.category.{$cat['slug']}.description",
                $cat['description'],
                'seo'
            );
            Setting::setValue(
                "site.seo.category.{$cat['slug']}.og_image",
                "/storage/site/seo/og-{$cat['slug']}.jpg",
                'seo'
            );
        }

        // ── Organización (schema.org Organization) ─────────────────────────
        $orgValues = [
            'site.organization.name'                  => 'JUANFER SEGUROS LTDA.',
            'site.organization.legal_code'            => '902025311-6',
            'site.organization.phone'                 => '3105955581',
            'site.organization.email'                 => 'jfcomunicaciones@juanferseguros.com',
            'site.organization.opening_hours'         => 'Mo-Fr 08:00-17:00',
            'site.organization.address.street'        => 'CC Viva Envigado - Torre de oficinas, Piso 10',
            'site.organization.address.locality'      => 'Envigado',
            'site.organization.address.region'        => 'Antioquia',
            'site.organization.address.country'       => 'CO',
            'site.organization.address.postal_code'   => '',
            'site.organization.social.facebook'       => 'https://www.facebook.com/juanfersegurossura',
            'site.organization.social.instagram'      => 'https://www.instagram.com/juanferseguros',
            'site.organization.social.linkedin'       => 'https://www.linkedin.com/company/juanfer-seguros',
            'site.organization.social.tiktok'         => 'https://www.tiktok.com/@juanferseguros',
        ];

        foreach ($orgValues as $key => $value) {
            Setting::setValue($key, $value, 'organization');
        }

        // ── Integraciones (vacías; cliente las pega desde admin) ───────────
        $integrationValues = [
            'site.integrations.gsc_verification'   => '',
            'site.integrations.gtm_id'             => '',
            'site.integrations.ga4_measurement_id' => '',
            'site.integrations.fb_pixel_id'        => '',
        ];

        foreach ($integrationValues as $key => $value) {
            Setting::setValue($key, $value, 'integrations');
        }
    }

    protected function seedMenu(): void
    {
        $menu = Menu::firstOrCreate(
            ['menu_cdslug' => 'site-main'],
            ['menu_nmname' => 'Site Main Menu']
        );

        $items = [
            ['Inicio',      '#',             1],
            ['Soluciones',  '#soluciones',   2],
            ['Nosotros',    '#nosotros',     3],
            ['Testimonios', '#testimonios',  4],
            ['Aliados',     '#aliados',      5],
            ['Contacto',    '#contacto',     6],
        ];

        foreach ($items as [$label, $url, $order]) {
            MenuItem::updateOrCreate(
                [
                    'mnit_idmenu' => $menu->menu_idmenu,
                    'mnit_nmlabe' => $label,
                ],
                [
                    'mnit_cdtype' => MenuItem::TYPE_URL,
                    'mnit_dsurll' => $url,
                    'mnit_idpare' => null,
                    'mnit_nrorde' => $order,
                ]
            );
        }
    }

    protected function seedCarousel(): void
    {
        $source = base_path('resources/themes/juanfer/assets/images/carousel');
        $relativeDir = 'site/carousel';
        $absDir = storage_path('app/public/' . $relativeDir);

        if (! File::isDirectory($absDir)) {
            File::makeDirectory($absDir, 0755, true);
        }

        $admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
        $category = Category::where('cate_cdslug', 'carousel')->first();

        $slides = [
            ['slug' => 'carousel-slide-1', 'title' => 'Slide 1', 'file' => 'JF_banner1.png'],
            ['slug' => 'carousel-slide-2', 'title' => 'Slide 2', 'file' => 'JF_banner2.png'],
            ['slug' => 'carousel-slide-3', 'title' => 'Slide 3', 'file' => 'JF_banner3.png'],
            ['slug' => 'carousel-slide-4', 'title' => 'Slide 4', 'file' => 'JF_banner4.png'],
        ];

        foreach ($slides as $idx => $slide) {
            $src = $source . DIRECTORY_SEPARATOR . $slide['file'];
            $relPath = $relativeDir . '/' . $slide['file'];
            $absPath = storage_path('app/public/' . $relPath);

            if (File::exists($src)) {
                File::copy($src, $absPath);
            }

            $content = Content::updateOrCreate(
                ['cont_cdslug' => $slide['slug']],
                [
                    'cont_nmtitl' => $slide['title'],
                    'cont_cdtype' => Content::TYPE_CUSTOM,
                    'cont_cdstat' => Content::STATUS_PUBLISHED,
                    'cont_idauth' => $admin?->user_iduser,
                    'cont_dtpubl' => now()->addSeconds($idx),
                ]
            );

            if ($category) {
                $content->categories()->syncWithoutDetaching([$category->cate_idcate]);
            }

            Media::updateOrCreate(
                [
                    'medi_nmmdbl' => Content::class,
                    'medi_idmdbl' => $content->cont_idcont,
                ],
                [
                    'medi_dspath' => $relPath,
                    'medi_cdtype' => 'image/png',
                    'medi_idusby' => $admin?->user_iduser,
                ]
            );
        }
    }

    /**
     * Seed mock de 1 solución por categoría (6 total). El cliente las
     * reemplazará/ampliará desde el admin.
     *
     * Imagen del content = OG image de la categoría (reusable).
     */
    protected function seedSolutions(): void
    {
        $admin = User::where('user_dsemai', 'admin@hyperion.local')->first();

        $solutionsDir = storage_path('app/public/site/solutions');
        if (! File::isDirectory($solutionsDir)) {
            File::makeDirectory($solutionsDir, 0755, true);
        }

        foreach ($this->solutionCategories() as $idx => $cat) {
            $category = Category::where('cate_cdslug', $cat['slug'])->first();
            if (! $category) {
                continue;
            }

            $imageRel = "site/solutions/{$cat['slug']}.jpg";
            $imageAbs = storage_path("app/public/{$imageRel}");

            // Si existe la imagen del cliente en ImgOK la usamos; si no,
            // dejamos el path vacío (no crítico para SEO base).
            $clientSrc = $this->clientAssetPath($cat['og_file']);
            if ($clientSrc && File::exists($clientSrc) && ! File::exists($imageAbs)) {
                File::copy($clientSrc, $imageAbs);
            }

            $content = Content::updateOrCreate(
                ['cont_cdslug' => $cat['content_slug']],
                [
                    'cont_nmtitl' => $cat['content_title'],
                    'cont_cdtype' => Content::TYPE_CUSTOM,
                    'cont_cdstat' => Content::STATUS_PUBLISHED,
                    'cont_idauth' => $admin?->user_iduser,
                    'cont_dtpubl' => now()->addSeconds($idx),
                ]
            );

            $latestVersion = ContentVersion::where('cove_idcont', $content->cont_idcont)
                ->orderByDesc('cove_nrvers')
                ->first();

            if ($latestVersion) {
                $latestVersion->update(['cove_dsbody' => $cat['content_body']]);
            } else {
                ContentVersion::create([
                    'cove_idcont' => $content->cont_idcont,
                    'cove_nrvers' => 1,
                    'cove_dsbody' => $cat['content_body'],
                ]);
            }

            $content->categories()->syncWithoutDetaching([$category->cate_idcate]);

            if (File::exists($imageAbs)) {
                Media::updateOrCreate(
                    [
                        'medi_nmmdbl' => Content::class,
                        'medi_idmdbl' => $content->cont_idcont,
                    ],
                    [
                        'medi_dspath' => $imageRel,
                        'medi_cdtype' => 'image/jpeg',
                        'medi_idusby' => $admin?->user_iduser,
                    ]
                );
            }
        }
    }

    protected function seedTestimonials(): void
    {
        $admin = User::where('user_dsemai', 'admin@hyperion.local')->first();
        $category = Category::where('cate_cdslug', 'testimonios')->first();

        $quote = 'Excelente servicio y atención personalizada. Me sentí muy acompañad@ '
            . 'durante todo el proceso de adquisición de mi seguro de vida.';

        $items = [
            ['testimonio-maria-g',  'María G.'],
            ['testimonio-carlos-m', 'Carlos M.'],
            ['testimonio-ana-r-1',  'Ana R.'],
            ['testimonio-ana-r-2',  'Ana R.'],
            ['testimonio-luis-t',   'Luis T.'],
            ['testimonio-sofia-l',  'Sofía L.'],
        ];

        foreach ($items as $idx => [$slug, $name]) {
            $content = Content::updateOrCreate(
                ['cont_cdslug' => $slug],
                [
                    'cont_nmtitl' => $name,
                    'cont_cdtype' => Content::TYPE_CUSTOM,
                    'cont_cdstat' => Content::STATUS_PUBLISHED,
                    'cont_idauth' => $admin?->user_iduser,
                    'cont_dtpubl' => now(),
                ]
            );

            ContentVersion::updateOrCreate(
                ['cove_idcont' => $content->cont_idcont, 'cove_nrvers' => 1],
                ['cove_dsbody' => $quote]
            );

            if ($category) {
                $content->categories()->syncWithoutDetaching([$category->cate_idcate]);
            }
        }
    }

    /**
     * Copia los assets SEO (OG por categoría + OG default + logo schema.org)
     * desde la carpeta de ImgOK del cliente a storage/app/public/site/seo/.
     * Skip si la fuente no existe (entorno sin assets entregados).
     */
    protected function seedSeoAssets(): void
    {
        $targetDir = storage_path('app/public/site/seo');
        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // OG por categoría
        foreach ($this->solutionCategories() as $cat) {
            $src = $this->clientAssetPath($cat['og_file']);
            $dst = "{$targetDir}/og-{$cat['slug']}.jpg";
            if ($src && File::exists($src) && ! File::exists($dst)) {
                File::copy($src, $dst);
            }
        }

        // OG global default
        $ogDefault = $this->clientAssetPath('JF_opengraph2.png');
        $ogDefaultDst = "{$targetDir}/og-default.jpg";
        if ($ogDefault && File::exists($ogDefault) && ! File::exists($ogDefaultDst)) {
            File::copy($ogDefault, $ogDefaultDst);
        }

        // Logo 600×600 para schema.org (downscale desde isotipo-01 4500×4500)
        $logoSrc = base_path('resources/themes/juanfer/assets/images/logos/logo_isotipo-01.png');
        $logoDst = "{$targetDir}/logo.png";
        if (File::exists($logoSrc) && ! File::exists($logoDst)) {
            // Sin Imagick disponible, hacemos copia directa; el SiteSeoService
            // referencia la URL y el cliente puede sustituir por la versión
            // optimizada cuando se construya el módulo /admin/seo.
            File::copy($logoSrc, $logoDst);
        }
    }

    /**
     * Resuelve la ruta a un asset de la carpeta de entrega del cliente.
     * Configurable vía env HYPERION_CLIENT_ASSETS_PATH para no hardcodear el
     * Escritorio del dev en producción.
     */
    protected function clientAssetPath(string $filename): ?string
    {
        $base = env('HYPERION_CLIENT_ASSETS_PATH', 'C:\\Users\\crist\\OneDrive\\Escritorio\\ImgOK');
        $path = rtrim($base, "\\/") . DIRECTORY_SEPARATOR . $filename;
        return $path;
    }
}
