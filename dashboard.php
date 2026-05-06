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


try {
    $pdo  = getPDO();
    $stmt = $pdo->prepare(
        'SELECT * FROM modules WHERE user_id = ? ORDER BY exam_date ASC'
    );
    $stmt->execute([$userId]);
    $modules = $stmt->fetchAll();
} catch (Exception $e) {
    $modules = [];
}

    // ── Calcul des statistiques ─────────────────────────────────
$totalModules  = count($modules);
$avgProgress   = $totalModules
    ? (int) round(array_sum(array_column($modules, 'progress')) / $totalModules)
    : 0;

$hardCount = count(array_filter($modules, fn($m) => $m['difficulty'] === 'Hard'));

$today     = new DateTime();
$nextExam  = null;
$daysLeft  = null;

foreach ($modules as $m) {
    $examDate = new DateTime($m['exam_date']);
    if ($examDate >= $today) {
        $nextExam = $m['module_name'];
        $daysLeft = (int) $today->diff($examDate)->days;
        break;
    }
}

// ── Helper : badge difficulty ────────────────────────────────
function diffBadge(string $d): string {
    $map = [
        'Easy'   => 'badge-easy',
        'Medium' => 'badge-medium',
        'Hard'   => 'badge-hard',
    ];
    return '<span class="badge ' . ($map[$d] ?? '') . '">' . htmlspecialchars($d) . '</span>';
}

// ── Helper : badge importance ────────────────────────────────
function impBadge(string $i): string {
    $map = [
        'Low'    => 'badge-low',
        'Medium' => 'badge-medium',
        'High'   => 'badge-high',
    ];
    return '<span class="badge ' . ($map[$i] ?? '') . '">' . htmlspecialchars($i) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Exam Planner</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="app-layout">

  <!-- ════════════════ SIDEBAR ════════════════ -->
  <aside class="sidebar">

    <div class="sidebar-logo">
      <div class="logo-mark">🎓</div>
      <div class="logo-text">
        <strong>EXAM PLANNER</strong>
        Cadi Ayyad University
      </div>
    </div>

    <p class="nav-label">Navigation</p>

    <a href="dashboard.php" class="nav-item active">
      <span class="nav-icon">📊</span> Dashboard
    </a>
    <a href="modules.php" class="nav-item">
      <span class="nav-icon">📚</span> Mes modules
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

  <!-- ════════════════ MAIN ════════════════ -->
  <main class="main-content">

    <!-- ── Top bar ── -->
    <div class="topbar">
      <div class="topbar-title">
        <h1>Bonjour, <?= $userName ?> 👋</h1>
        <p>Voici un aperçu de ta préparation aux examens</p>
      </div>
      <div class="topbar-actions">
        <span class="badge-date">
          <?= (new DateTime())->format('d M Y') ?>
        </span>
        <a href="logout.php" class="btn btn-ghost" style="display:none">Déconnexion</a>
      </div>
    </div>

    <!-- ── Stats ── -->
    <div class="stats-grid">

      <div class="stat-card c-amber">
        <div class="stat-icon">📚</div>
        <div class="stat-value"><?= $totalModules ?></div>
        <div class="stat-label">Modules total</div>
      </div>

      <div class="stat-card c-green">
        <div class="stat-icon">📈</div>
        <div class="stat-value"><?= $avgProgress ?>%</div>
        <div class="stat-label">Progression moy.</div>
      </div>

      <div class="stat-card c-red">
        <div class="stat-icon">🔥</div>
        <div class="stat-value"><?= $hardCount ?></div>
        <div class="stat-label">Modules difficiles</div>
      </div>

      <div class="stat-card c-blue">
        <div class="stat-icon">⏳</div>
        <div class="stat-value">
          <?= $daysLeft !== null ? $daysLeft : '—' ?>
          <?php if ($daysLeft !== null): ?><span style="font-size:1rem;font-family:var(--body)">j</span><?php endif; ?>
        </div>
        <div class="stat-label">
          <?= $nextExam ? htmlspecialchars($nextExam) : 'Prochain examen' ?>
        </div>
      </div>

    </div>

    <!-- ── Modules table ── -->
    <div class="section-header">
      <h2 class="section-title">Mes modules</h2>
      <a href="add_module.php" class="btn btn-primary">+ Ajouter un module</a>
    </div>

    <div class="table-wrap">

      <?php if (empty($modules)): ?>
        <div class="empty-state">
          <div class="empty-icon">📭</div>
          <p>Aucun module pour l'instant.<br>Commence par en ajouter un !</p>
          <a href="add_module.php" class="btn btn-primary">+ Ajouter mon premier module</a>
        </div>

      <?php else: ?>
        <table class="modules-table">
          <thead>
            <tr>
              <th>Module</th>
              <th>Difficulté</th>
              <th>Importance</th>
              <th>Compréhension</th>
              <th>Progression</th>
              <th>Examen</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($modules as $m): ?>
            <tr>
              <td class="cell-module">
                <strong><?= htmlspecialchars($m['module_name']) ?></strong>
                <?php if ($m['teacher']): ?>
                  <span><?= htmlspecialchars($m['teacher']) ?></span>
                <?php endif; ?>
              </td>
              <td><?= diffBadge($m['difficulty']) ?></td>
              <td><?= impBadge($m['career_importance']) ?></td>
              <td><?= impBadge($m['understanding_level']) ?></td>
              <td>
                <div class="progress-wrap">
                  <div class="progress-bar-bg">
                    <div
                      class="progress-bar-fill"
                      style="width:0"
                      data-progress="<?= (int) $m['progress'] ?>"
                    ></div>
                  </div>
                  <span class="progress-pct"><?= (int) $m['progress'] ?>%</span>
                </div>
              </td>
              <td>
                <div
                  class="exam-date"
                  data-date="<?= htmlspecialchars($m['exam_date']) ?>"
                >
                  <?= (new DateTime($m['exam_date']))->format('d/m/Y') ?>
                  <span class="days-left"></span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

    </div><!-- /.table-wrap -->

  </main><!-- /.main-content -->

</div><!-- /.app-layout -->

<script src="assets/js/dashboard.js"></script>
</body>
</html>