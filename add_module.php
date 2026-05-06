<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$userId   = (int) $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name']);
$initial  = strtoupper(mb_substr($_SESSION['user_name'], 0, 1));

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $module_name       = trim($_POST['module_name']       ?? '');
    $teacher           = trim($_POST['teacher']           ?? '');
    $difficulty        =      $_POST['difficulty']        ?? '';
    $career_importance =      $_POST['career_importance'] ?? '';
    $progress          = (int)($_POST['progress']         ?? 0);
    $understanding_level =    $_POST['understanding_level'] ?? '';
    $exam_date         = trim($_POST['exam_date']         ?? '');

    // ── Validation ──────────────────────────────────────────────
    if (empty($module_name)) {
        $error = 'Le nom du module est obligatoire.';
    } elseif (!in_array($difficulty, ['Easy', 'Medium', 'Hard'])) {
        $error = 'Difficulté invalide.';
    } elseif (!in_array($career_importance, ['Low', 'Medium', 'High'])) {
        $error = 'Importance invalide.';
    } elseif ($progress < 0 || $progress > 100) {
        $error = 'La progression doit être entre 0 et 100.';
    } elseif (!in_array($understanding_level, ['Low', 'Medium', 'High'])) {
        $error = 'Niveau de compréhension invalide.';
    } elseif (empty($exam_date) || !strtotime($exam_date)) {
        $error = "Date d'examen invalide.";
    } else {
        try {
            $pdo  = getPDO();
            $stmt = $pdo->prepare(
                'INSERT INTO modules
                    (user_id, module_name, teacher, difficulty, career_importance, progress, understanding_level, exam_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $module_name,
                $teacher,
                $difficulty,
                $career_importance,
                $progress,
                $understanding_level,
                $exam_date,
            ]);

            header('Location: modules.php?added=1');
            exit;

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
  <title>Ajouter un module — Exam Planner</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/modules.css">
</head>
<body>

<div class="app-layout">

  <!-- ════════ SIDEBAR ════════ -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-mark">🎓</div>
      <div class="logo-text">
        <strong>EXAM PLANNER</strong>
        Cadi Ayyad University
      </div>
    </div>

    <p class="nav-label">Navigation</p>

    <a href="dashboard.php" class="nav-item">
      <span class="nav-icon">📊</span> Dashboard
    </a>
    <a href="modules.php" class="nav-item">
      <span class="nav-icon">📚</span> Mes modules
    </a>
    <a href="add_module.php" class="nav-item active">
      <span class="nav-icon">➕</span> Ajouter un module
    </a>
    <a href="#" class="nav-item">
      <span class="nav-icon">🤖</span> Plan IA
    </a>
    <a href="#" class="nav-item">
      <span class="nav-icon">📅</span> Calendrier
    </a>

    <div class="sidebar-footer">
      <div class="user-chip">
        <div class="user-avatar"><?= $initial ?></div>
        <div class="user-info">
          <div class="user-name"><?= $userName ?></div>
          <div class="user-role">Étudiant</div>
        </div>
        <a href="logout.php" class="btn-logout" title="Déconnexion">⏻</a>
      </div>
    </div>
  </aside>

  <!-- ════════ MAIN ════════ -->
  <main class="main-content">

    <div class="page-header">
      <a href="modules.php" class="back-btn">← Retour</a>
      <h1 style="font-family:'Space Grotesk',sans-serif;font-size:1.5rem;margin:0;color:var(--text)">
        Ajouter un module
      </h1>
    </div>

    <div class="form-card">
      <h2>📚 Nouveau module</h2>
      <p class="subtitle">Remplis les informations ci-dessous. Ces données seront utilisées pour générer ton plan de révision IA.</p>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form id="addModuleForm" method="POST" action="add_module.php" novalidate>

        <div class="form-grid">

          <!-- Module name -->
          <div class="field-group span-2">
            <label for="module_name">Nom du module <span class="req">*</span></label>
            <input
              type="text"
              id="module_name"
              name="module_name"
              placeholder="ex : Mathématiques, Techniques Web..."
              value="<?= htmlspecialchars($_POST['module_name'] ?? '') ?>"
              required
            >
          </div>

          <!-- Teacher -->
          <div class="field-group span-2">
            <label for="teacher">Nom du professeur</label>
            <input
              type="text"
              id="teacher"
              name="teacher"
              placeholder="ex : Prof. Hourri"
              value="<?= htmlspecialchars($_POST['teacher'] ?? '') ?>"
            >
          </div>

          <!-- Difficulty -->
          <div class="field-group">
            <label for="difficulty">Difficulté <span class="req">*</span></label>
            <select id="difficulty" name="difficulty" required>
              <option value="" disabled <?= empty($_POST['difficulty']) ? 'selected' : '' ?>>Choisir…</option>
              <option value="Easy"   <?= ($_POST['difficulty'] ?? '') === 'Easy'   ? 'selected' : '' ?>>🟢 Facile</option>
              <option value="Medium" <?= ($_POST['difficulty'] ?? '') === 'Medium' ? 'selected' : '' ?>>🟡 Moyen</option>
              <option value="Hard"   <?= ($_POST['difficulty'] ?? '') === 'Hard'   ? 'selected' : '' ?>>🔴 Difficile</option>
            </select>
          </div>

          <!-- Career importance -->
          <div class="field-group">
            <label for="career_importance">Importance carrière <span class="req">*</span></label>
            <select id="career_importance" name="career_importance" required>
              <option value="" disabled <?= empty($_POST['career_importance']) ? 'selected' : '' ?>>Choisir…</option>
              <option value="Low"    <?= ($_POST['career_importance'] ?? '') === 'Low'    ? 'selected' : '' ?>>📉 Faible</option>
              <option value="Medium" <?= ($_POST['career_importance'] ?? '') === 'Medium' ? 'selected' : '' ?>>📊 Moyenne</option>
              <option value="High"   <?= ($_POST['career_importance'] ?? '') === 'High'   ? 'selected' : '' ?>>📈 Haute</option>
            </select>
          </div>

          <!-- Understanding level -->
          <div class="field-group">
            <label for="understanding_level">Niveau de compréhension <span class="req">*</span></label>
            <select id="understanding_level" name="understanding_level" required>
              <option value="" disabled <?= empty($_POST['understanding_level']) ? 'selected' : '' ?>>Choisir…</option>
              <option value="Low"    <?= ($_POST['understanding_level'] ?? '') === 'Low'    ? 'selected' : '' ?>>😕 Faible</option>
              <option value="Medium" <?= ($_POST['understanding_level'] ?? '') === 'Medium' ? 'selected' : '' ?>>🙂 Moyen</option>
              <option value="High"   <?= ($_POST['understanding_level'] ?? '') === 'High'   ? 'selected' : '' ?>>😎 Élevé</option>
            </select>
          </div>

          <!-- Exam date -->
          <div class="field-group">
            <label for="exam_date">Date de l'examen <span class="req">*</span></label>
            <input
              type="date"
              id="exam_date"
              name="exam_date"
              value="<?= htmlspecialchars($_POST['exam_date'] ?? '') ?>"
              required
            >
          </div>

          <!-- Progress -->
          <div class="field-group span-2">
            <label for="progress">Progression actuelle (0 – 100 %)</label>
            <div class="range-wrap">
              <input
                type="range"
                id="progress"
                name="progress"
                min="0" max="100" step="5"
                value="<?= (int)($_POST['progress'] ?? 0) ?>"
              >
              <span class="range-value" id="progressVal"><?= (int)($_POST['progress'] ?? 0) ?>%</span>
            </div>
          </div>

        </div><!-- /.form-grid -->

        <div class="form-actions">
          <a href="modules.php" class="btn-cancel">Annuler</a>
          <button type="submit" class="btn-save" id="saveBtn">
            💾 Enregistrer le module
          </button>
        </div>

      </form>
    </div>

  </main>
</div>

<script src="assets/js/add_module.js"></script>
</body>
</html>
