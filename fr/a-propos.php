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
            Créations JY est la maison créative de Yasemin Jemmely, une artisane qui transforme les découvertes de brocante en pièces désirables prêtes à scénariser vos espaces.
            L’atelier mélange savoir-faire de restauration, direction artistique et service sur-mesure pour offrir un luxe durable, pensé en Suisse et apprécié à l’international.
        </p>
        <?php if ($aboutMediaUrl): ?>
            <div class="about-media-block">
                <figure>
                    <img src="<?php echo e($aboutMediaUrl); ?>" alt="Atelier Créations JY" loading="lazy">
                </figure>
                <p class="about-media-caption">
                    Atelier Créations JY · Gruyère — le lieu où chaque pièce reprend vie.
                </p>
            </div>
        <?php endif; ?>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin: 1.5rem 0;">
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 12px 32px rgba(0,0,0,0.06);">
                <h3 style="margin-top: 0;">Vision</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Offrir des décors émotionnels en valorisant l’existant, plutôt qu’en produisant à nouveau. Chaque création vise à réduire l’empreinte carbone d’un intérieur haut de gamme.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 12px 32px rgba(0,0,0,0.06);">
                <h3 style="margin-top: 0;">Expérience</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Photos éditoriales, storytelling précis et conversation directe via WhatsApp pour obtenir la bonne pièce sans quitter votre canapé.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 12px 32px rgba(0,0,0,0.06);">
                <h3 style="margin-top: 0;">Engagement</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Transparence sur l’origine, le statut et les délais afin que les architectes, décorateurs et particuliers puissent planifier sereinement.
                </p>
            </article>
        </div>
        <h2 style="margin-top: 1.5rem;">Notre méthode</h2>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin: 1rem 0 1.5rem;">
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">1. Collecte curatée</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Trouvailles, textiles et boiseries sont photographiés, triés et stockés comme dans une garde-robe capsule.</p>
            </article>
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">2. Design &amp; restauration</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Assemblages, patines et détails métalliques sont retravaillés pour garantir durabilité et harmonie.</p>
            </article>
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">3. Mise en scène digitale</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Chaque pièce est shootée, mesurée et publiée pour commander en ligne avec la même précision qu’en showroom.</p>
            </article>
        </div>
        <h2 style="margin-top: 1.5rem;">Services</h2>
        <ul style="color: #8B7F7F; padding-left: 1.25rem;">
            <li>Catalogue prêt-à-adopter mis à jour chaque semaine</li>
            <li>Commandes sur-mesure (comptoirs, têtes de lit, scénographies retail)</li>
            <li>Location courte durée pour shootings, vitrines et événements</li>
            <li>Conseil pour intégrer l’upcycling dans des projets de rénovation ou d’identité de marque</li>
        </ul>
        <h2 style="margin-top: 1.5rem;">Travaillons ensemble</h2>
        <p>
            Nous parlons français et anglais, collaborons avec des clients privés comme avec des studios d’architecture et livrons partout en Suisse et en Europe.
            Écrivez-nous sur WhatsApp ou via le formulaire de contact pour imaginer la prochaine pièce qui fera vibrer votre espace.
        </p>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
