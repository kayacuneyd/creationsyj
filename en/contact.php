<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';

$lang = 'en';
$errors = [];
$success = false;

// Load site settings
$settings = [];
$stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
foreach ($stmt as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$contactEmail = $settings['contact_email'] ?? 'contact@creationsjy.com';
$whatsappNumber = $settings['whatsapp_number'] ?? '+41XXXXXXXXX';
$instagramUrl = $settings['instagram_url'] ?? 'https://instagram.com/creationsjy';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $token = $_POST['csrf_token'] ?? null;

    if (!verifyCSRFToken($token)) {
        $errors[] = 'Security error, please try again.';
    }

    if ($name === '') {
        $errors[] = 'Please enter your name.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($message === '') {
        $errors[] = 'Please write a message.';
    }

    if (!$errors) {
        $to = $contactEmail;
        $subject = 'New message from Creations JY website';
        $body = "Name: $name\nEmail: $email\n\n$message";
        $headers = 'From: ' . $email . "\r\n" .
                   'Reply-To: ' . $email . "\r\n";

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
            Have a question about a piece, a custom project or a collaboration?
            Send a message to Yasemin using this form or reach out directly on WhatsApp.
        </p>

        <?php if ($success): ?>
            <p style="margin-top: 1rem; padding: 0.75rem 1rem; background-color: #E8F5E9; border-radius: 0.5rem;">
                Thank you for your message, Yasemin will get back to you as soon as possible.
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
                <label for="name">Name</label>
                <input id="name" name="name" type="text" class="input" required value="<?php echo e($_POST['name'] ?? ''); ?>">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" class="input" required value="<?php echo e($_POST['email'] ?? ''); ?>">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" class="textarea" required><?php echo e($_POST['message'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn-primary">
                Send
            </button>
        </form>

        <div style="margin-top: 2rem;">
            <p><strong>Email</strong>: <a href="mailto:<?php echo e($contactEmail); ?>"><?php echo e($contactEmail); ?></a></p>
            <p><strong>WhatsApp</strong>: <a href="https://wa.me/<?php echo e(str_replace(['+', ' ', '-'], '', $whatsappNumber)); ?>" target="_blank" rel="noopener"><?php echo e($whatsappNumber); ?></a></p>
            <p><strong>Instagram</strong>: <a href="<?php echo e($instagramUrl); ?>" target="_blank" rel="noopener"><?php echo e(parse_url($instagramUrl, PHP_URL_HOST) ?: '@creationsjy'); ?></a></p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>


