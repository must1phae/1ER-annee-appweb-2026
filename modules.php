<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once 'config/db.php';

$userId   = (int) $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name']);
$initial  = strtoupper(mb_substr($_SESSION['user_name'], 0, 1));

$error      = '';
$editError  = '';
$openModal  = false;
$openEdit   = false;

// ── Handle ADD ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'add') {
    $module_name         = trim($_POST['module_name']         ?? '');
    $teacher             = trim($_POST['teacher']             ?? '');
    $difficulty          =      $_POST['difficulty']          ?? '';
    $career_importance   =      $_POST['career_importance']   ?? '';
    $progress            = (int)($_POST['progress']           ?? 0);
    $understanding_level =      $_POST['understanding_level'] ?? '';
    $exam_date           = trim($_POST['exam_date']           ?? '');

    if (empty($module_name))                                   $error = 'Le nom du module est obligatoire.';
    elseif (!in_array($difficulty,['Easy','Medium','Hard']))   $error = 'Difficulté invalide.';
    elseif (!in_array($career_importance,['Low','Medium','High'])) $error = 'Importance invalide.';
    elseif ($progress < 0 || $progress > 100)                  $error = 'La progression doit être entre 0 et 100.';
    elseif (!in_array($understanding_level,['Low','Medium','High'])) $error = 'Niveau de compréhension invalide.';
    elseif (empty($exam_date) || !strtotime($exam_date))       $error = "Date d'examen invalide.";
    else {
        try {
            $pdo  = getPDO();
            $stmt = $pdo->prepare('INSERT INTO modules (user_id,module_name,teacher,difficulty,career_importance,progress,understanding_level,exam_date) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$userId,$module_name,$teacher,$difficulty,$career_importance,$progress,$understanding_level,$exam_date]);
            header('Location: modules.php?added=1'); exit;
        } catch (Exception $e) { $error = 'Erreur serveur.'; }
    }
    $openModal = true;
}

// ── Handle EDIT ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'edit') {
    $editId              = (int)($_POST['edit_id']            ?? 0);
    $module_name         = trim($_POST['module_name']         ?? '');
    $teacher             = trim($_POST['teacher']             ?? '');
    $difficulty          =      $_POST['difficulty']          ?? '';
    $career_importance   =      $_POST['career_importance']   ?? '';
    $progress            = (int)($_POST['progress']           ?? 0);
    $understanding_level =      $_POST['understanding_level'] ?? '';
    $exam_date           = trim($_POST['exam_date']           ?? '');

    if (empty($module_name))                                      $editError = 'Le nom du module est obligatoire.';
    elseif (!in_array($difficulty,['Easy','Medium','Hard']))      $editError = 'Difficulté invalide.';
    elseif (!in_array($career_importance,['Low','Medium','High'])) $editError = 'Importance invalide.';
    elseif ($progress < 0 || $progress > 100)                     $editError = 'La progression doit être entre 0 et 100.';
    elseif (!in_array($understanding_level,['Low','Medium','High'])) $editError = 'Niveau de compréhension invalide.';
    elseif (empty($exam_date) || !strtotime($exam_date))          $editError = "Date d'examen invalide.";
    else {
        try {
            $pdo  = getPDO();
            $stmt = $pdo->prepare('UPDATE modules SET module_name=?,teacher=?,difficulty=?,career_importance=?,progress=?,understanding_level=?,exam_date=? WHERE id=? AND user_id=?');
            $stmt->execute([$module_name,$teacher,$difficulty,$career_importance,$progress,$understanding_level,$exam_date,$editId,$userId]);
            header('Location: modules.php?updated=1'); exit;
        } catch (Exception $e) { $editError = 'Erreur serveur.'; }
    }
    $openEdit = true;
}

// ── Fetch modules ─────────────────────────────────────────────
try {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM modules WHERE user_id = ? ORDER BY exam_date ASC');
    $stmt->execute([$userId]);
    $modules = $stmt->fetchAll();
} catch (Exception $e) { $modules = []; }

$totalModules = count($modules);

function diffBadge(string $d): string {
    $map = ['Easy'=>['badge-easy','🟢 Facile'],'Medium'=>['badge-medium','🟡 Moyen'],'Hard'=>['badge-hard','🔴 Difficile']];
    [$cls,$label] = $map[$d] ?? ['',''];
    return "<span class='badge $cls'>".htmlspecialchars($label)."</span>";
}
function levelBadge(string $l): string {
    $map = ['Low'=>['badge-low','📉 Faible'],'Medium'=>['badge-medium','📊 Moyen'],'High'=>['badge-high','📈 Élevé']];
    [$cls,$label] = $map[$l] ?? ['',''];
    return "<span class='badge $cls'>".htmlspecialchars($label)."</span>";
}
// $openModal / $openEdit already set above
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes modules — Exam Planner</title>
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

    <div class="page-topbar">
      <div>
        <h1>📚 Mes modules</h1>
        <p>Tous tes modules enregistrés pour cette année académique</p>
      </div>
      <button class="btn btn-primary" id="openModal">+ Ajouter un module</button>
    </div>

    <?php if (isset($_GET['added'])): ?>
      <div class="flash flash-success">✅ Module ajouté avec succès !</div>
    <?php elseif (isset($_GET['updated'])): ?>
      <div class="flash flash-success">✏️ Module mis à jour avec succès !</div>
    <?php elseif (isset($_GET['deleted'])): ?>
      <div class="flash flash-success">🗑️ Module supprimé avec succès !</div>
    <?php endif; ?>

    <div class="modules-wrap">
      <div class="modules-toolbar">
        <span class="modules-count"><strong><?= $totalModules ?></strong> module<?= $totalModules>1?'s':'' ?> trouvé<?= $totalModules>1?'s':'' ?></span>
        <button class="btn btn-primary" id="openModal2" style="font-size:.8rem;padding:.4rem .9rem">+ Nouveau</button>
      </div>

      <?php if (empty($modules)): ?>
        <div class="empty-state">
          <div class="empty-icon">📭</div>
          <p>Aucun module pour l'instant.<br>Commence par en ajouter un !</p>
          <button class="btn btn-primary" id="openModal3">+ Ajouter mon premier module</button>
        </div>
      <?php else: ?>
        <table class="modules-table">
          <thead>
            <tr>
              <th>Module</th><th>Difficulté</th><th>Importance</th>
              <th>Compréhension</th><th>Progression</th><th>Examen</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($modules as $m):
            $examDate  = new DateTime($m['exam_date']);
            $today     = new DateTime();
            $diff      = (int)$today->diff($examDate)->days;
            $isPast    = $examDate < $today;
            $daysClass = $isPast ? 'days-past' : ($diff<=7 ? 'days-urgent' : ($diff<=21 ? 'days-soon' : 'days-ok'));
            $daysLabel = $isPast ? 'Passé' : ($diff===0 ? "Aujourd'hui !" : "J-{$diff}");
          ?>
          <tr>
            <td class="cell-module">
              <strong><?= htmlspecialchars($m['module_name']) ?></strong>
              <?php if ($m['teacher']): ?><span>👨‍🏫 <?= htmlspecialchars($m['teacher']) ?></span><?php endif; ?>
            </td>
            <td><?= diffBadge($m['difficulty']) ?></td>
            <td><?= levelBadge($m['career_importance']) ?></td>
            <td><?= levelBadge($m['understanding_level']) ?></td>
            <td>
              <div class="prog-bar-bg">
                <div class="prog-bar-fill" style="width:0" data-progress="<?= (int)$m['progress'] ?>"></div>
              </div>
              <span class="prog-pct"><?= (int)$m['progress'] ?>%</span>
            </td>
            <td>
              <div class="exam-info">
                <span class="exam-date-str"><?= $examDate->format('d/m/Y') ?></span>
                <span class="days-left-badge <?= $daysClass ?>"><?= $daysLabel ?></span>
              </div>
            </td>
            <td class="actions-cell">
              <div style="display:flex; gap:0.4rem;">
                <button class="btn-edit btn-edit-modal"
                  data-module="<?= htmlspecialchars(json_encode([
                    'id'                  => (int)$m['id'],
                    'module_name'         => $m['module_name'],
                    'teacher'             => $m['teacher'] ?? '',
                    'difficulty'          => $m['difficulty'],
                    'career_importance'   => $m['career_importance'],
                    'progress'            => (int)$m['progress'],
                    'understanding_level' => $m['understanding_level'],
                    'exam_date'           => $m['exam_date'],
                  ]), ENT_QUOTES, 'UTF-8') ?>"
                >✏️ Modifier</button>
                <button type="button" class="btn-delete"
                   onclick="openDeleteModal(<?= (int)$m['id'] ?>, '<?= htmlspecialchars(addslashes($m['module_name'])) ?>')">🗑️ Supprimer</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  </main>
</div>

<!-- ════════ ADD MODAL ════════ -->
<div class="modal-overlay" id="modalOverlay" <?= $openModal ? 'style="opacity:1;pointer-events:all;"' : '' ?>>
  <div class="modal" <?= $openModal ? 'style="transform:none;"' : '' ?>>
    <div class="modal-header">
      <div>
        <h2>📚 Nouveau module</h2>
        <p>Ces données seront utilisées pour générer ton plan de révision IA.</p>
      </div>
      <button class="modal-close" id="closeModal" type="button">✕</button>
    </div>

    <?php if ($error): ?>
      <div class="modal-alert err">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="modules.php" novalidate>
      <input type="hidden" name="_action" value="add">
      <div class="form-grid">

        <div class="field-group span-2">
          <label for="module_name">Nom du module <span class="req">*</span></label>
          <input type="text" id="module_name" name="module_name"
            placeholder="ex : Mathématiques, Techniques Web..."
            value="<?= htmlspecialchars($_POST['module_name'] ?? '') ?>" required>
        </div>

        <div class="field-group span-2">
          <label for="teacher">Nom du professeur</label>
          <input type="text" id="teacher" name="teacher"
            placeholder="ex : Prof. Hourri"
            value="<?= htmlspecialchars($_POST['teacher'] ?? '') ?>">
        </div>

        <div class="field-group">
          <label for="difficulty">Difficulté <span class="req">*</span></label>
          <select id="difficulty" name="difficulty" required>
            <option value="" disabled <?= empty($_POST['difficulty'])?'selected':'' ?>>Choisir…</option>
            <option value="Easy"   <?= ($_POST['difficulty']??'')==='Easy'  ?'selected':'' ?>>🟢 Facile</option>
            <option value="Medium" <?= ($_POST['difficulty']??'')==='Medium'?'selected':'' ?>>🟡 Moyen</option>
            <option value="Hard"   <?= ($_POST['difficulty']??'')==='Hard'  ?'selected':'' ?>>🔴 Difficile</option>
          </select>
        </div>

        <div class="field-group">
          <label for="career_importance">Importance carrière <span class="req">*</span></label>
          <select id="career_importance" name="career_importance" required>
            <option value="" disabled <?= empty($_POST['career_importance'])?'selected':'' ?>>Choisir…</option>
            <option value="Low"    <?= ($_POST['career_importance']??'')==='Low'   ?'selected':'' ?>>📉 Faible</option>
            <option value="Medium" <?= ($_POST['career_importance']??'')==='Medium'?'selected':'' ?>>📊 Moyenne</option>
            <option value="High"   <?= ($_POST['career_importance']??'')==='High'  ?'selected':'' ?>>📈 Haute</option>
          </select>
        </div>

        <div class="field-group">
          <label for="understanding_level">Compréhension <span class="req">*</span></label>
          <select id="understanding_level" name="understanding_level" required>
            <option value="" disabled <?= empty($_POST['understanding_level'])?'selected':'' ?>>Choisir…</option>
            <option value="Low"    <?= ($_POST['understanding_level']??'')==='Low'   ?'selected':'' ?>>😕 Faible</option>
            <option value="Medium" <?= ($_POST['understanding_level']??'')==='Medium'?'selected':'' ?>>🙂 Moyen</option>
            <option value="High"   <?= ($_POST['understanding_level']??'')==='High'  ?'selected':'' ?>>😎 Élevé</option>
          </select>
        </div>

        <div class="field-group">
          <label for="exam_date">Date de l'examen <span class="req">*</span></label>
          <input type="date" id="exam_date" name="exam_date"
            value="<?= htmlspecialchars($_POST['exam_date'] ?? '') ?>" required>
        </div>

        <div class="field-group span-2">
          <label for="progress">Progression actuelle (0 – 100 %)</label>
          <div class="range-wrap">
            <input type="range" id="progress" name="progress" min="0" max="100" step="5"
              value="<?= (int)($_POST['progress'] ?? 0) ?>">
            <span class="range-value" id="progressVal"><?= (int)($_POST['progress'] ?? 0) ?>%</span>
          </div>
        </div>

      </div>

      <div class="form-actions">
        <button type="button" class="btn-cancel-modal" id="closeModal2">Annuler</button>
        <button type="submit" class="btn-save-modal">💾 Enregistrer le module</button>
      </div>
    </form>
  </div>
</div>

<!-- ════════ EDIT MODAL ════════ -->
<div class="modal-overlay" id="editOverlay">
  <div class="modal">
    <div class="modal-header">
      <div>
        <h2 id="editModalTitle">✏️ Modifier le module</h2>
        <p>Modifie les champs puis enregistre les changements.</p>
      </div>
      <button class="modal-close" onclick="closeEditModal()" type="button">✕</button>
    </div>

    <?php if ($editError): ?>
      <div class="modal-alert err">⚠ <?= htmlspecialchars($editError) ?></div>
    <?php endif; ?>

    <form method="POST" action="modules.php" novalidate>
      <input type="hidden" name="_action" value="edit">
      <input type="hidden" name="edit_id" id="edit_id">
      <div class="form-grid">

        <div class="field-group span-2">
          <label>Nom du module <span class="req">*</span></label>
          <input type="text" name="module_name" id="edit_module_name" placeholder="ex : Mathématiques..." required>
        </div>
        <div class="field-group span-2">
          <label>Nom du professeur</label>
          <input type="text" name="teacher" id="edit_teacher" placeholder="ex : Prof. Hourri">
        </div>
        <div class="field-group">
          <label>Difficulté <span class="req">*</span></label>
          <select name="difficulty" id="edit_difficulty" required>
            <option value="" disabled>Choisir…</option>
            <option value="Easy">🟢 Facile</option>
            <option value="Medium">🟡 Moyen</option>
            <option value="Hard">🔴 Difficile</option>
          </select>
        </div>
        <div class="field-group">
          <label>Importance carrière <span class="req">*</span></label>
          <select name="career_importance" id="edit_career_importance" required>
            <option value="" disabled>Choisir…</option>
            <option value="Low">📉 Faible</option>
            <option value="Medium">📊 Moyenne</option>
            <option value="High">📈 Haute</option>
          </select>
        </div>
        <div class="field-group">
          <label>Compréhension <span class="req">*</span></label>
          <select name="understanding_level" id="edit_understanding_level" required>
            <option value="" disabled>Choisir…</option>
            <option value="Low">😕 Faible</option>
            <option value="Medium">🙂 Moyen</option>
            <option value="High">😎 Élevé</option>
          </select>
        </div>
        <div class="field-group">
          <label>Date de l'examen <span class="req">*</span></label>
          <input type="date" name="exam_date" id="edit_exam_date" required>
        </div>
        <div class="field-group span-2">
          <label>Progression (0 – 100 %)</label>
          <div class="range-wrap">
            <input type="range" name="progress" id="edit_progress" min="0" max="100" step="5">
            <span class="range-value" id="edit_progressVal">0%</span>
          </div>
        </div>

      </div>
      <div class="form-actions">
        <button type="button" class="btn-cancel-modal" onclick="closeEditModal()">Annuler</button>
        <button type="submit" class="btn-save-modal">💾 Enregistrer les modifications</button>
      </div>
    </form>
  </div>
</div>

<!-- ════════ DELETE MODAL ════════ -->
<div class="modal-overlay" id="deleteOverlay">
  <div class="modal" style="max-width: 450px; text-align: center;">
    <div class="modal-header" style="justify-content: center; border-bottom: none; margin-bottom: 0;">
      <div>
        <div style="font-size: 3rem; margin-bottom: 1rem;">🗑️</div>
        <h2>Supprimer ce module ?</h2>
        <p>Es-tu sûr de vouloir supprimer le module <strong id="deleteModuleName" style="color:var(--text);"></strong> ?<br>Cette action est irréversible.</p>
      </div>
    </div>
    <div class="form-actions" style="margin-top: 1.5rem; justify-content: center;">
      <button type="button" class="btn-cancel-modal" onclick="closeDeleteModal()">Annuler</button>
      <a href="#" id="confirmDeleteBtn" class="btn-save-modal" style="background: rgba(248, 113, 113, 0.15); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.3);">
        Oui, supprimer
      </a>
    </div>
  </div>
</div>

<script>
// ── Add modal ────────────────────────────────────────────────
const addOverlay  = document.getElementById('modalOverlay');
const editOverlay = document.getElementById('editOverlay');
const deleteOverlay = document.getElementById('deleteOverlay');

['openModal','openModal2','openModal3'].forEach(id => {
  const el = document.getElementById(id);
  if (el) el.addEventListener('click', () => addOverlay.classList.add('open'));
});
document.getElementById('closeModal').addEventListener('click',  () => addOverlay.classList.remove('open'));
document.getElementById('closeModal2').addEventListener('click', () => addOverlay.classList.remove('open'));
addOverlay.addEventListener('click', e => { if (e.target === addOverlay) addOverlay.classList.remove('open'); });

<?php if ($openModal): ?> addOverlay.classList.add('open'); <?php endif; ?>

// ── Edit modal ───────────────────────────────────────────────
document.querySelectorAll('.btn-edit-modal').forEach(btn => {
  btn.addEventListener('click', function() {
    const data = JSON.parse(this.dataset.module);
    openEditModal(data);
  });
});

function openEditModal(d) {
  document.getElementById('edit_id').value                  = d.id;
  document.getElementById('edit_module_name').value         = d.module_name;
  document.getElementById('edit_teacher').value             = d.teacher;
  document.getElementById('edit_difficulty').value          = d.difficulty;
  document.getElementById('edit_career_importance').value   = d.career_importance;
  document.getElementById('edit_understanding_level').value = d.understanding_level;
  document.getElementById('edit_exam_date').value           = d.exam_date;
  const rng = document.getElementById('edit_progress');
  rng.value = d.progress;
  rng.dispatchEvent(new Event('input'));
  document.getElementById('editModalTitle').textContent = '✏️ ' + d.module_name;
  editOverlay.classList.add('open');
}
function closeEditModal() { editOverlay.classList.remove('open'); }
editOverlay.addEventListener('click', e => { if (e.target === editOverlay) closeEditModal(); });

<?php if ($openEdit): ?> openEditModal({
  id:   '<?= (int)($_POST['edit_id'] ?? 0) ?>',
  module_name:         <?= json_encode($_POST['module_name'] ?? '') ?>,
  teacher:             <?= json_encode($_POST['teacher'] ?? '') ?>,
  difficulty:          <?= json_encode($_POST['difficulty'] ?? 'Medium') ?>,
  career_importance:   <?= json_encode($_POST['career_importance'] ?? 'Medium') ?>,
  progress:            <?= (int)($_POST['progress'] ?? 0) ?>,
  understanding_level: <?= json_encode($_POST['understanding_level'] ?? 'Medium') ?>,
  exam_date:           <?= json_encode($_POST['exam_date'] ?? '') ?>
}); <?php endif; ?>

// ── Delete modal ─────────────────────────────────────────────
function openDeleteModal(id, moduleName) {
  document.getElementById('deleteModuleName').textContent = moduleName;
  document.getElementById('confirmDeleteBtn').href = 'delete_module.php?id=' + id;
  deleteOverlay.classList.add('open');
}
function closeDeleteModal() { deleteOverlay.classList.remove('open'); }
deleteOverlay.addEventListener('click', e => { if (e.target === deleteOverlay) closeDeleteModal(); });

// ── Range sliders (both modals) ──────────────────────────────
function initRange(rangeId, valId) {
  const r = document.getElementById(rangeId);
  const v = document.getElementById(valId);
  r.addEventListener('input', () => {
    v.textContent = r.value + '%';
    r.style.background = `linear-gradient(to right,var(--accent) ${r.value}%,var(--border) ${r.value}%)`;
  });
  r.dispatchEvent(new Event('input'));
}
initRange('progress', 'progressVal');
initRange('edit_progress', 'edit_progressVal');

// ── Animate progress bars ────────────────────────────────────
document.querySelectorAll('.prog-bar-fill').forEach(bar => {
  requestAnimationFrame(() => setTimeout(() => { bar.style.width = bar.dataset.progress + '%'; }, 100));
});

// ── Escape key ───────────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { 
    addOverlay.classList.remove('open'); 
    closeEditModal(); 
    closeDeleteModal(); 
  }
});
</script>
</body>
</html>
