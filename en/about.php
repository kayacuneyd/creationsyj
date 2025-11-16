<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';

$lang = 'en';

include __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">About Creations JY</h1>
        <p>
            Creations JY is the world of Yasemin Jemmely, a maker from Gruyère who transforms forgotten, antique
            and second-hand materials into soulful decor. The roadmap guiding the studio puts sustainability,
            craftsmanship and digital storytelling at the heart of every decision.
        </p>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin: 1.5rem 0;">
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 12px 32px rgba(0,0,0,0.06);">
                <h3 style="margin-top: 0;">Gruyère workshop</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Between mountains and lakes, Yasemin hand-builds limited series and custom orders.
                    Visits are possible on request to see pieces in real life or co-design a brief.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 12px 32px rgba(0,0,0,0.06);">
                <h3 style="margin-top: 0;">Visual signature</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    A dusty-rose, cream and warm-grey palette frames each shoot and reinforces the
                    vintage yet elevated positioning described in the roadmap.
                </p>
            </article>
        </div>
        <h2 style="margin-top: 1.5rem;">Sustainable manifesto</h2>
        <ul style="color: #8B7F7F; padding-left: 1.25rem;">
            <li>Re-use and elevate existing materials instead of producing new stock.</li>
            <li>Document every piece with transparent statuses (available, reserved, sold).</li>
            <li>Keep conversations human through the WhatsApp Business flow.</li>
        </ul>
        <h2 style="margin-top: 1.5rem;">Creative process</h2>
        <p>
            Following the roadmap, each object goes through sourcing, artisan assembly and thoughtful styling
            before it reaches the online gallery or a collector's home.
        </p>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin: 1rem 0 1.5rem;">
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">Sourcing</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Second-hand, antique or unused pieces are grouped into curated sets.</p>
            </article>
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">Artisan build</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Mixed techniques respect the patina while ensuring durability.</p>
            </article>
            <article style="background-color: var(--off-white); border-radius: 0.75rem; padding: 1.25rem;">
                <h3 style="margin-top: 0;">Story-driven styling</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">Soft textures and shabby-chic accents tie each collection together.</p>
            </article>
        </div>
        <h2 style="margin-top: 1.5rem;">Services</h2>
        <ul style="color: #8B7F7F; padding-left: 1.25rem;">
            <li>Ready-to-adopt creations with photos, materials and measurements.</li>
            <li>Custom commissions for homes, boutiques, events or corporate gifting.</li>
            <li>Styling advice for blending upcycled pieces into contemporary spaces.</li>
        </ul>
        <h2 style="margin-top: 1.5rem;">Digital experience</h2>
        <p>
            The platform is crafted with HTML5, Tailwind CSS, vanilla JS, PHP&nbsp;8 and MySQL to remain lightweight and secure.
            WhatsApp Business integration supports messaging-based sales, while a mobile-first layout serves the 70%+
            smartphone audience outlined in the roadmap.
        </p>
        <p>
            Wherever you are in Switzerland or Europe, you can reach Yasemin in a few taps and co-create your next signature piece.
        </p>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

