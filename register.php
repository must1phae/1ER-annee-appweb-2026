<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'config/db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name']             ?? '');
    $email    = trim($_POST['email']            ?? '');
    $password =      $_POST['password']         ?? '';
    $confirm  =      $_POST['confirm_password'] ?? '';

    // Validation serveur
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Tous les champs sont obligatoires.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';

    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';

    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';

    } else {
        try {
            $pdo = getPDO();

            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $error = 'Cet email est déjà utilisé.';
            } else {
                // Hacher le mot de passe et insérer l'utilisateur
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    'INSERT INTO users (name, email, passwords) VALUES (?, ?, ?)'
                );
                $stmt->execute([$name, $email, $hash]);

                $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
            }

        } catch (Exception $e) {
            $error = 'Erreur serveur. Veuillez réessayer.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription — Exam Planner</title>
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

<div class="auth-page">

  <!-- ── Panneau gauche ── -->
  <div class="auth-panel">
    <div class="grid-bg"></div>

    <div class="panel-brand">
      <div class="panel-logo">
        <div class="logo-mark">🎓</div>
        <span>EXAM PLANNER</span>
      </div>
      <h1 class="panel-title">
        Prépare tes examens<br>avec <em>l'IA</em>
      </h1>
      <p class="panel-desc">
        Organise tes modules, suis ta progression et génère des plans d'étude personnalisés grâce à l'intelligence artificielle.
      </p>
    </div>

    <div class="panel-features">
      <div class="feature-item">
        <div class="feature-icon">📚</div>
        <span>Gestion de tous tes modules en un seul endroit</span>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🤖</div>
        <span>Plans d'étude générés par IA selon ta progression</span>
      </div>
      <div class="feature-item">
        <div class="feature-icon">📅</div>
        <span>Compte à rebours automatique avant chaque examen</span>
      </div>
    </div>
  </div>

  <!-- ── Formulaire ── -->
  <div class="auth-form-side">
    <div class="auth-box">

      <div class="auth-box-header">
        <h2>Créer un compte</h2>
        <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          ✓ <?= htmlspecialchars($success) ?>
          <a href="login.php" style="color:inherit;margin-left:.5rem;font-weight:600;">→ Connexion</a>
        </div>
      <?php endif; ?>

      <form id="registerForm" method="POST" action="register.php" novalidate>

        <!-- Nom -->
        <div class="form-group">
          <label class="form-label" for="name">Nom complet</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input
              type="text"
              id="name"
              name="name"
              class="form-control"
              placeholder="Ahmed Benali"
              value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
              autocomplete="name"
            >
          </div>
          <span class="field-error"></span>
        </div>

        <!-- Email -->
        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <div class="input-wrap">
            <span class="input-icon">✉</span>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control"
              placeholder="ahmed@email.com"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              autocomplete="email"
            >
          </div>
          <span class="field-error"></span>
        </div>

        <!-- Mot de passe -->
        <div class="form-group">
          <label class="form-label" for="password">Mot de passe</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input
              type="password"
              id="password"
              name="password"
              class="form-control"
              placeholder="Minimum 8 caractères"
              autocomplete="new-password"
            >
            <button type="button" class="btn-toggle-pw">👁️</button>
          </div>
          <div class="strength-bar-wrap">
            <div class="strength-bar"></div>
          </div>
          <span class="field-error"></span>
        </div>

        <!-- Confirmation -->
        <div class="form-group">
          <label class="form-label" for="confirm_password">Confirmer le mot de passe</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input
              type="password"
              id="confirm_password"
              name="confirm_password"
              class="form-control"
              placeholder="Répéter le mot de passe"
              autocomplete="new-password"
            >
            <button type="button" class="btn-toggle-pw">👁️</button>
          </div>
          <span class="field-error"></span>
        </div>

        <button type="submit" class="btn-submit">
          <span class="btn-text">Créer mon compte →</span>
          <span class="spinner"></span>
        </button>

      </form>
    </div>
  </div>

</div>

<script src="assets/js/auth.js"></script>
</body>
</html>