<?php

session_start();

// Déjà connecté → dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email et mot de passe sont requis.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email invalide.';

    } else {
        try {
            $pdo  = getPDO();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['passwords'])) {
                // Démarrer la session
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email']= $user['email'];

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Email ou mot de passe incorrect.';
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
  <title>Connexion — Exam Planner</title>
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
        Bon retour<br>parmi <em>nous</em>
      </h1>
      <p class="panel-desc">
        Connecte-toi pour retrouver tes modules, suivre ta progression et accéder à tes plans d'étude personnalisés.
      </p>
    </div>

    <div class="panel-features">
      <div class="feature-item">
        <div class="feature-icon">🔐</div>
        <span>Tes données sont sécurisées et privées</span>
      </div>
      <div class="feature-item">
        <div class="feature-icon">⚡</div>
        <span>Accès instantané à ton tableau de bord</span>
      </div>
      <div class="feature-item">
        <div class="feature-icon">📊</div>
        <span>Suivi en temps réel de ta progression</span>
      </div>
    </div>
  </div>

  <!-- ── Formulaire ── -->
  <div class="auth-form-side">
    <div class="auth-box">

      <div class="auth-box-header">
        <h2>Se connecter</h2>
        <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success">✓ Compte créé avec succès ! Connectez-vous.</div>
      <?php endif; ?>

      <form id="loginForm" method="POST" action="login.php" novalidate>

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
              placeholder="Votre mot de passe"
              autocomplete="current-password"
            >
            <button type="button" class="btn-toggle-pw">👁️</button>
          </div>
          <span class="field-error"></span>
        </div>

        <button type="submit" class="btn-submit">
          <span class="btn-text">Se connecter →</span>
          <span class="spinner"></span>
        </button>

      </form>

      <div class="divider">ou</div>
      <p style="text-align:center;font-size:.83rem;color:var(--muted)">
        Pas encore de compte ? <a href="register.php" style="color:var(--accent);font-weight:500;">Créer un compte</a>
      </p>

    </div>
  </div>

</div>

<script src="assets/js/auth.js"></script>
</body>
</html>