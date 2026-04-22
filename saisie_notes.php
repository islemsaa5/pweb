<?php
/**
 * Projet: Gestion de Scolarité USTHB
 * Équipe:
 * - SAADI Islem (232331698506)
 * - KHELLAS Maria (242431486807)
 * - ABDELLATIF Sara (242431676416)
 * - DAHMANI Anais (242431679715)
 */
require_once 'config.php';
requireLogin();

if ($_SESSION['role'] !== 'enseignant') {
    header('Location: index.php');
    exit;
}

$page_title = 'Saisie des Notes';
$user_id = $_SESSION['user_id'];
$message = ''; $error = '';

$stmt_modules = $pdo->prepare("SELECT * FROM modules WHERE enseignant_id = ? ORDER BY semestre, intitule");
$stmt_modules->execute([$user_id]);
$modules = $stmt_modules->fetchAll();

$etudiants_raw = $pdo->query("
    SELECT id, matricule, nom, prenom, section, groupe_td
    FROM etudiants
    ORDER BY section ASC, nom ASC
")->fetchAll();

$etudiants_par_section = [];
foreach ($etudiants_raw as $e) {
    $sec = $e['section'] ?? 'A';
    $etudiants_par_section[$sec][] = $e;
}

$selected_module_id = (int)($_GET['module_id'] ?? 0);
$notes_existantes = [];
if ($selected_module_id) {
    $stmt_notes = $pdo->prepare("
        SELECT n.etudiant_id, n.note, e.nom, e.prenom, e.matricule, e.section, e.groupe_td
        FROM notes n
        JOIN etudiants e ON n.etudiant_id = e.id
        WHERE n.module_id = ?
        ORDER BY e.section, e.nom
    ");
    $stmt_notes->execute([$selected_module_id]);
    foreach ($stmt_notes->fetchAll() as $n) {
        $notes_existantes[$n['etudiant_id']] = $n;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etudiant_id = (int)$_POST['etudiant_id'];
    $module_id   = (int)$_POST['module_id'];
    $note        = (float)str_replace(',', '.', $_POST['note']);

    $check = $pdo->prepare("SELECT id FROM modules WHERE id = ? AND enseignant_id = ?");
    $check->execute([$module_id, $user_id]);

    if (!$check->fetch()) {
        $error = 'Vous n\'êtes pas autorisé à saisir des notes pour ce module.';
    } elseif (empty($etudiant_id) || empty($module_id)) {
        $error = 'Veuillez sélectionner un étudiant et un module.';
    } elseif ($note < 0 || $note > 20) {
        $error = 'La note doit être entre 0 et 20.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO notes (etudiant_id, module_id, note) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE note = VALUES(note)
        ");
        $stmt->execute([$etudiant_id, $module_id, $note]);
        $message = 'Note enregistrée / mise à jour avec succès.';

        header("Location: saisie_notes.php?module_id=$module_id");
        exit;
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Saisie des Notes</h1>
        <p>Enregistrez les résultats de vos étudiants par module</p>
    </div>

    <?php if ($message): ?>
        <div class="msg-success animate-pop"><i class="fa-solid fa-circle-check"></i> <?= $message ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="msg-error animate-pop"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="notes-layout" style="display:grid; grid-template-columns: 340px 1fr; gap:20px; align-items:start;">

        <!-- Formulaire de saisie -->
        <div class="note-form-box glass-effect">
            <h3><i class="fa-solid fa-pen-to-square" style="color:#2c3e80;"></i> Saisie d'une note</h3>
            <form method="POST">

                <div class="form-group">
                    <label>Semestre / Module</label>
                    <select name="module_id" id="moduleSelect" required onchange="this.form.method='get'; this.form.action='saisie_notes.php'; this.form.submit();">
                        <option value="">-- Sélectionner un module --</option>
                        <?php
                        $current_sem = null;
                        foreach ($modules as $m):
                            $sem = $m['semestre'] ?? '';
                            if ($sem !== $current_sem) {
                                if ($current_sem !== null) echo '</optgroup>';
                                echo '<optgroup label="Semestre ' . htmlspecialchars($sem) . '">';
                                $current_sem = $sem;
                            }
                        ?>
                        <option value="<?= $m['id'] ?>" <?= ($selected_module_id === $m['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['code_module'] . ' - ' . $m['intitule']) ?>
                        </option>
                        <?php endforeach; if ($current_sem !== null) echo '</optgroup>'; ?>
                    </select>
                    <input type="hidden" name="module_id" id="module_id_hidden" value="<?= $selected_module_id ?>">
                </div>

                <div class="form-group">
                    <label>Étudiant (groupé par section)</label>
                    <select name="etudiant_id" required>
                        <option value="">-- Sélectionner un étudiant --</option>
                        <?php foreach ($etudiants_par_section as $sec => $liste): ?>
                        <optgroup label="Section <?= htmlspecialchars($sec) ?>">
                            <?php foreach ($liste as $e): ?>
                            <option value="<?= $e['id'] ?>">
                                <?= htmlspecialchars($e['matricule'] . ' - ' . $e['nom'] . ' ' . $e['prenom']) ?>
                                <?= isset($notes_existantes[$e['id']]) ? ' âœ” ' . $notes_existantes[$e['id']]['note'] . '/20' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Note (/20)</label>
                    <input type="number" name="note" min="0" max="20" step="0.25" placeholder="ex: 14.50" required>
                </div>

                <?php if ($selected_module_id): ?>
                    <input type="hidden" name="module_id" value="<?= $selected_module_id ?>">
                <?php endif; ?>

                <button type="submit" class="btn-submit" style="width:100%; margin-top:5px;">
                    <i class="fa-solid fa-floppy-disk"></i> Valider
                </button>
            </form>
        </div>

        <!-- Tableau des notes existantes pour le module sélectionné -->
        <div>
            <?php if ($selected_module_id && !empty($modules)): ?>
            <?php
                $mod_info = null;
                foreach ($modules as $m) { if ($m['id'] === $selected_module_id) { $mod_info = $m; break; } }
            ?>
            <div class="table-container">
                <div class="table-header">
                    <h3>
                        <i class="fa-solid fa-list-check"></i>
                        Notes saisies
                        <?php if ($mod_info): ?>
                            â€” <?= htmlspecialchars($mod_info['code_module'] . ' : ' . $mod_info['intitule']) ?>
                        <?php endif; ?>
                    </h3>
                    <span class="badge" style="background:#2c3e80;color:white;">
                        <?= count($notes_existantes) ?> / <?= count($etudiants_raw) ?> étudiants notés
                    </span>
                </div>
                <?php if (!empty($notes_existantes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom & Prénom</th>
                            <th>Section</th>
                            <th>G.TD</th>
                            <th>Note /20</th>
                            <th>Mention</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notes_existantes as $n):
                            $note = (float)$n['note'];
                            if ($note >= 16)      { $mention = 'Très Bien'; $mcls = 'badge-admis'; }
                            elseif ($note >= 14)  { $mention = 'Bien'; $mcls = 'badge-admis'; }
                            elseif ($note >= 12)  { $mention = 'Assez Bien'; $mcls = 'badge-admis'; }
                            elseif ($note >= 10)  { $mention = 'Passable'; $mcls = 'badge-admis'; }
                            else                  { $mention = 'Insuffisant'; $mcls = 'badge-ajourne'; }
                            $sec = strtolower($n['section'] ?? 'a');
                        ?>
                        <tr>
                            <td><span class="badge-code"><?= htmlspecialchars($n['matricule']) ?></span></td>
                            <td><strong><?= htmlspecialchars($n['nom'] . ' ' . $n['prenom']) ?></strong></td>
                            <td style="text-align:center;"><span class="section-badge sec-<?= $sec ?>"><?= strtoupper($n['section'] ?? '') ?></span></td>
                            <td style="text-align:center;"><?= $n['groupe_td'] ?? 1 ?></td>
                            <td style="text-align:center; font-weight:bold; font-size:15px;"><?= number_format($note, 2) ?></td>
                            <td><span class="badge <?= $mcls ?>"><?= $mention ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="padding:30px; text-align:center; color:#888;">
                    <i class="fa-solid fa-inbox" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                    Aucune note saisie pour ce module.
                </div>
                <?php endif; ?>
            </div>

            <?php else: ?>
            <div class="table-container" style="padding:30px; text-align:center; color:#888;">
                <i class="fa-solid fa-arrow-left" style="font-size:24px; margin-bottom:10px; display:block; color:#2c3e80;"></i>
                <p>Sélectionnez un module pour voir les notes déjà saisies.</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>

document.getElementById('moduleSelect').addEventListener('change', function() {
    const id = this.value;
    window.location.href = 'saisie_notes.php' + (id ? '?module_id=' + id : '');
});
</script>

<?php include 'includes/footer.php'; ?>
