<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';

$lang = 'en';
$aboutMediaUrl = getSiteSetting('about_media_url');
$aboutMediaUrl = $aboutMediaUrl !== null && $aboutMediaUrl !== '' ? $aboutMediaUrl : '/assets/images/placeholder.jpg';

include __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">About Creations JY</h1>
        <p>
            Creations JY is a boutique upcycling studio founded by Yasemin Jemmely in Gruyère. We turn archival fabrics, antique furniture and forgotten décor into contemporary statements that elevate interiors with a conscience.
            The collection bridges editorial aesthetics and sustainable production so every piece feels rare, tactile and intentional.
        </p>
        <?php if ($aboutMediaUrl): ?>
            <div class="about-media-block">
                <figure>
                    <img src="<?php echo e($aboutMediaUrl); ?>" alt="Creations JY studio" loading="lazy">
                </figure>
                <p class="about-media-caption">
                    Inside the Gruyère studio where heritage materials meet modern styling.
                </p>
            </div>
        <?php endif; ?>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin: 1.5rem 0;">
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 12px 32px rgba(0,0,0,0.06);">
                <h3 style="margin-top: 0;">Our ethos</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Preserve character, minimise waste and craft décor that sparks conversation in homes, hotels and retail spaces.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 12px 32px rgba(0,0,0,0.06);">
                <h3 style="margin-top: 0;">Experience</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Editorial photography, transparent descriptions and instant WhatsApp conversations create a high-touch digital journey.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 12px 32px rgba(0,0,0,0.06);">
                <h3 style="margin-top: 0;">Service</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    From sourcing to delivery we remain your single point of contact, ensuring projects stay on-brand and on-time.
                </p>
            </article>
        </div>
        <h2 style="margin-top: 1.5rem;">How we build each piece</h2>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin: 1rem 0 1.5rem;">
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">1. Curated sourcing</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Antique markets and collectors supply raw materials catalogued like a fashion archive.</p>
            </article>
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">2. Artisanal transformation</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Mixed media restoration blends carpentry, textile work and gilding to protect the original charm.</p>
            </article>
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">3. Digital storytelling</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Every item is measured, styled and published so you can source remotely with showroom-level detail.</p>
            </article>
        </div>
        <h2 style="margin-top: 1.5rem;">What we offer</h2>
        <ul style="color: #8B7F7F; padding-left: 1.25rem;">
            <li>Weekly drops of ready-to-adopt décor</li>
            <li>Bespoke commissions for hospitality, retail and editorial sets</li>
            <li>Short-term rentals for photo shoots and pop-ups</li>
            <li>Consulting to integrate upcycled statement pieces into brand environments</li>
        </ul>
        <h2 style="margin-top: 1.5rem;">Let’s collaborate</h2>
        <p>
            We operate bilingually in French and English and deliver throughout Switzerland and across Europe. Message us on WhatsApp or via the contact page—together we will craft the next storyworthy object for your project.
        </p>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
