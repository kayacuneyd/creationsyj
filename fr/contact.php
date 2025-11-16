<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';

$lang = 'fr';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $token = $_POST['csrf_token'] ?? null;

    if (!verifyCSRFToken($token)) {
        $errors[] = 'Erreur de sécurité, veuillez réessayer.';
    }

    if ($name === '') {
        $errors[] = 'Veuillez indiquer votre nom.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Veuillez indiquer une adresse e-mail valide.';
    }

    if ($message === '') {
        $errors[] = 'Veuillez écrire un message.';
    }

    if (!$errors) {
        $to = 'contact@creationsjy.com';
        $subject = 'Nouveau message via le site Créations JY';
        $body = "Nom: $name\nEmail: $email\n\n$message";
        $headers = 'From: ' . $email . "\r\n" .
                   'Reply-To: ' . $email . "\r\n";

        // In production, consider using a proper SMTP library.
        @mail($to, $subject, $body, $headers);
        $success = true;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Contact</h1>
        <p>
            Vous avez une question sur une création, un projet sur mesure ou une collaboration ?
            Laissez un message à Yasemin via ce formulaire ou contactez-la directement par WhatsApp.
        </p>

        <?php if ($success): ?>
            <p style="margin-top: 1rem; padding: 0.75rem 1rem; background-color: #E8F5E9; border-radius: 0.5rem;">
                Merci pour votre message, Yasemin vous répondra dès que possible.
            </p>
        <?php endif; ?>

        <?php if ($errors): ?>
            <ul style="margin-top: 1rem; padding: 0.75rem 1rem; background-color: #FFEBEE; border-radius: 0.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" style="margin-top: 1.5rem; max-width: 560px;">
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">

            <div style="margin-bottom: 1rem;">
                <label for="name">Nom</label>
                <input id="name" name="name" type="text" class="input" required value="<?php echo e($_POST['name'] ?? ''); ?>">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" class="input" required value="<?php echo e($_POST['email'] ?? ''); ?>">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" class="textarea" required><?php echo e($_POST['message'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn-primary">
                Envoyer
            </button>
        </form>

        <div style="margin-top: 2rem;">
            <p><strong>E-mail</strong>: <a href="mailto:contact@creationsjy.com">contact@creationsjy.com</a></p>
            <p><strong>WhatsApp</strong>: <a href="https://wa.me/41XXXXXXXXX" target="_blank" rel="noopener">+41&nbsp;XX&nbsp;XXX&nbsp;XX&nbsp;XX</a></p>
            <p><strong>Instagram</strong>: <a href="https://instagram.com/creationsjy" target="_blank" rel="noopener">@creationsjy</a></p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>


