<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';

$lang = 'fr';
$aboutMediaUrl = getSiteSetting('about_media_url');
$aboutMediaUrl = $aboutMediaUrl !== null && $aboutMediaUrl !== '' ? $aboutMediaUrl : '/assets/images/placeholder.jpg';

include __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">À propos de Créations JY</h1>
        <p>
            Créations JY est l'univers de Yasemin Jemmely, artisane installée en Gruyère qui transforme des matériaux
            de seconde main, antiques et oubliés en objets décoratifs uniques. Chaque pièce raconte une histoire,
            pensée pour des intérieurs éclectiques, durables et chaleureux.
        </p>
        <?php if ($aboutMediaUrl): ?>
            <div class="about-media-block">
                <figure>
                    <img src="<?php echo e($aboutMediaUrl); ?>" alt="Atelier Créations JY" loading="lazy">
                </figure>
                <p class="about-media-caption">
                    Atelier Créations JY · Gruyère — portrait ou logo géré depuis l'administration.
                </p>
            </div>
        <?php endif; ?>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin: 1.5rem 0;">
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 12px 32px rgba(0,0,0,0.06);">
                <h3 style="margin-top: 0;">Atelier en Gruyère</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Les créations prennent vie dans un atelier niché entre montagnes et lacs. Sur rendez-vous, vous pouvez
                    découvrir une sélection de pièces ou imaginer un projet sur mesure.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 12px 32px rgba(0,0,0,0.06);">
                <h3 style="margin-top: 0;">Signature visuelle</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    La direction artistique s'appuie sur la palette shabby chic du projet&nbsp;: rose poudré, crème lumineuse
                    et gris chaleureux pour sublimer chaque shooting et chaque fiche produit.
                </p>
            </article>
        </div>
        <h2 style="margin-top: 1.5rem;">Manifeste durable</h2>
        <ul style="color: #8B7F7F; padding-left: 1.25rem;">
            <li>Réutiliser et magnifier l'existant au lieu de produire de nouvelles matières.</li>
            <li>Documenter chaque pièce (disponible, réservée, vendue) afin d'assurer transparence et confiance.</li>
            <li>Faciliter l'échange direct via WhatsApp Business pour garder une relation humaine.</li>
        </ul>
        <h2 style="margin-top: 1.5rem;">Processus créatif</h2>
        <p>
            De la chine à la livraison, Yasemin suit un protocole précis inspiré du développement roadmap&nbsp;:
            sourcing local, assemblage artisanal, finitions photographiques prêtes à être partagées en ligne.
        </p>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin: 1rem 0 1.5rem;">
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">Chine &amp; sélection</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Objets chinés, matériaux anciens et textiles oubliés sont réunis en lots cohérents.</p>
            </article>
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">Assemblage artisanal</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">L'atelier utilise des techniques mixtes pour préserver la patine tout en garantissant la solidité.</p>
            </article>
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">Finitions shabby chic</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Textures douces, dorures légères et palette vintage assurent la cohérence de la collection.</p>
            </article>
        </div>
        <h2 style="margin-top: 1.5rem;">Services proposés</h2>
        <ul style="color: #8B7F7F; padding-left: 1.25rem;">
            <li>Créations prêtes à adopter, présentées avec photos détaillées et dimensions.</li>
            <li>Commandes personnalisées (restaurations, décorations d'événements, cadeaux d'entreprise).</li>
            <li>Conseil décoration pour intégrer l'upcycling dans des intérieurs contemporains.</li>
        </ul>
        <h2 style="margin-top: 1.5rem;">Expérience digitale</h2>
        <p>
            Le site s'appuie sur HTML5, Tailwind CSS, PHP&nbsp;8 et MySQL pour offrir une vitrine performante.
            L'intégration WhatsApp Business permet de gérer les commandes, tandis que l'approche mobile-first
            répond aux 70&nbsp;% de visiteurs sur smartphone.
        </p>
        <p>
            Que vous soyez en Suisse ou ailleurs en Europe, vous rejoignez directement Yasemin pour imaginer
            la prochaine création qui racontera votre histoire.
        </p>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
