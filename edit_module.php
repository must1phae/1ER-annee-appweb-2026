<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once 'config/db.php';

$userId   = (int) $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name']);
$initial  = strtoupper(mb_substr($_SESSION['user_name'], 0, 1));
$id       = (int) ($_GET['id'] ?? 0);

// ── Fetch module (must belong to this user) ───────────────────
try {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM modules WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $module = $stmt->fetch();
} catch (Exception $e) { $module = null; }

if (!$module) {
    header('Location: modules.php');
    exit;
}

$error = '';

// ── Handle UPDATE ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $module_name         = trim($_POST['module_name']         ?? '');
    $teacher             = trim($_POST['teacher']             ?? '');
    $difficulty          =      $_POST['difficulty']          ?? '';
    $career_importance   =      $_POST['career_importance']   ?? '';
    $progress            = (int)($_POST['progress']           ?? 0);
    $understanding_level =      $_POST['understanding_level'] ?? '';
    $exam_date           = trim($_POST['exam_date']           ?? '');

    if (empty($module_name))
        $error = 'Le nom du module est obligatoire.';
    elseif (!in_array($difficulty, ['Easy','Medium','Hard']))
        $error = 'Difficulté invalide.';
    elseif (!in_array($career_importance, ['Low','Medium','High']))
        $error = 'Importance invalide.';
    elseif ($progress < 0 || $progress > 100)
        $error = 'La progression doit être entre 0 et 100.';
    elseif (!in_array($understanding_level, ['Low','Medium','High']))
        $error = 'Niveau de compréhension invalide.';
    elseif (empty($exam_date) || !strtotime($exam_date))
        $error = "Date d'examen invalide.";
    else {
        try {
            $stmt = $pdo->prepare(
                'UPDATE modules
                 SET module_name=?, teacher=?, difficulty=?, career_importance=?,
                     progress=?, understanding_level=?, exam_date=?
                 WHERE id=? AND user_id=?'
            );
            $stmt->execute([
                $module_name, $teacher, $difficulty, $career_importance,
                $progress, $understanding_level, $exam_date,
                $id, $userId
            ]);
            header('Location: modules.php?updated=1');
            exit;
        } catch (Exception $e) {
            $error = 'Erreur serveur. Veuillez réessayer.';
        }
    }
    // On error keep posted values
    $module = array_merge($module, $_POST, ['progress' => $progress]);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier le module — Exam Planner</title>
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
      <div class="logo-text"><strong>EXAM PLANNER</strong> Cadi Ayyad University</div>
    </div>
    <p class="nav-label">Navigation</p>
    <a href="dashboard.php" class="nav-item"><span class="nav-icon">📊</span> Dashboard</a>
    <a href="modules.php"   class="nav-item active"><span class="nav-icon">📚</span> Mes modules</a>
    <a href="#" class="nav-item"><span class="nav-icon">🤖</span> Plan IA</a>
    <a href="#" class="nav-item"><span class="nav-icon">📅</span> Calendrier</a>
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
        Modifier le module
      </h1>
    </div>

    <div class="form-card">
      <h2>✏️ <?= htmlspecialchars($module['module_name']) ?></h2>
      <p class="subtitle">Modifie les informations ci-dessous puis enregistre les changements.</p>

      <div class="module-pill">
        🆔 Module #<?= $id ?>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="edit_module.php?id=<?= $id ?>" novalidate>
        <div class="form-grid">

          <div class="field-group span-2">
            <label for="module_name">Nom du module <span class="req">*</span></label>
            <input type="text" id="module_name" name="module_name"
              placeholder="ex : Mathématiques, Techniques Web..."
              value="<?= htmlspecialchars($module['module_name']) ?>" required>
          </div>

          <div class="field-group span-2">
            <label for="teacher">Nom du professeur</label>
            <input type="text" id="teacher" name="teacher"
              placeholder="ex : Prof. Hourri"
              value="<?= htmlspecialchars($module['teacher'] ?? '') ?>">
          </div>

          <div class="field-group">
            <label for="difficulty">Difficulté <span class="req">*</span></label>
            <select id="difficulty" name="difficulty" required>
              <option value="Easy"   <?= $module['difficulty']==='Easy'  ?'selected':'' ?>>🟢 Facile</option>
              <option value="Medium" <?= $module['difficulty']==='Medium'?'selected':'' ?>>🟡 Moyen</option>
              <option value="Hard"   <?= $module['difficulty']==='Hard'  ?'selected':'' ?>>🔴 Difficile</option>
            </select>
          </div>

          <div class="field-group">
            <label for="career_importance">Importance carrière <span class="req">*</span></label>
            <select id="career_importance" name="career_importance" required>
              <option value="Low"    <?= $module['career_importance']==='Low'   ?'selected':'' ?>>📉 Faible</option>
              <option value="Medium" <?= $module['career_importance']==='Medium'?'selected':'' ?>>📊 Moyenne</option>
              <option value="High"   <?= $module['career_importance']==='High'  ?'selected':'' ?>>📈 Haute</option>
            </select>
          </div>

          <div class="field-group">
            <label for="understanding_level">Compréhension <span class="req">*</span></label>
            <select id="understanding_level" name="understanding_level" required>
              <option value="Low"    <?= $module['understanding_level']==='Low'   ?'selected':'' ?>>😕 Faible</option>
              <option value="Medium" <?= $module['understanding_level']==='Medium'?'selected':'' ?>>🙂 Moyen</option>
              <option value="High"   <?= $module['understanding_level']==='High'  ?'selected':'' ?>>😎 Élevé</option>
            </select>
          </div>

          <div class="field-group">
            <label for="exam_date">Date de l'examen <span class="req">*</span></label>
            <input type="date" id="exam_date" name="exam_date"
              value="<?= htmlspecialchars($module['exam_date']) ?>" required>
          </div>

          <div class="field-group span-2">
            <label for="progress">Progression actuelle (0 – 100 %)</label>
            <div class="range-wrap">
              <input type="range" id="progress" name="progress" min="0" max="100" step="5"
                value="<?= (int)$module['progress'] ?>">
              <span class="range-value" id="progressVal"><?= (int)$module['progress'] ?>%</span>
            </div>
          </div>

        </div>

        <div class="form-actions">
          <a href="modules.php" class="btn-cancel">Annuler</a>
          <button type="submit" class="btn-save">💾 Enregistrer les modifications</button>
        </div>
      </form>
    </div>

  </main>
</div>

<script src="assets/js/edit_module.js"></script>
</body>
</html>
