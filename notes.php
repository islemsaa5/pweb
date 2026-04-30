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

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$page_title = 'Gestion des Notes';
$message = ''; $error = '';

$etudiants = $pdo->query("SELECT id, matricule, nom, prenom FROM etudiants ORDER BY nom")->fetchAll();
$modules = $pdo->query("SELECT id, code_module, intitule FROM modules ORDER BY intitule")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etudiant_id = (int)$_POST['etudiant_id'];
    $module_id   = (int)$_POST['module_id'];
    $note        = (float)$_POST['note'];

    if (empty($etudiant_id) || empty($module_id)) {
        $error = 'Veuillez selectionner un etudiant et un module.';
    } elseif ($note < 0 || $note > 20) {
        $error = 'La note doit etre entre 0 et 20.';
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO notes (etudiant_id, module_id, note) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE note = VALUES(note)
        ");
        $stmt->execute([$etudiant_id, $module_id, $note]);
        $message = 'Note enregistree avec succes.';
    }
}

$dernieres_notes = $pdo->query("
    SELECT n.id, n.note, e.nom, e.prenom, e.matricule, m.code_module 
    FROM notes n
    JOIN etudiants e ON n.etudiant_id = e.id
    JOIN modules m ON n.module_id = m.id
    ORDER BY n.id DESC LIMIT 10
")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Custom styling for Select2 to match the theme */
.select2-container .select2-selection--single {
    height: 35px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 35px;
    font-size: 13px;
    color: #555;
    padding-left: 10px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 33px;
}
</style>

<div class="main-content">
    <div class="page-header">
        <h1>Saisie des Notes</h1>
        <p>Administration generale des notes</p>
    </div>

    <?php if ($message): ?><div class="msg-success"><?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>

    <div class="notes-layout">
        <!-- Formulaire de saisie -->
        <div class="note-form-box">
            <h3>Saisir une note</h3>
            <form method="POST">
                
                <div class="form-group">
                    <label>Etudiant</label>
                    <select name="etudiant_id" id="etudiantSelectAdmin" required>
                        <option value="">-- Selectionner --</option>
                        <?php foreach ($etudiants as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['matricule'] . ' - ' . $e['nom'] . ' ' . $e['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Module</label>
                    <select name="module_id" id="moduleSelectAdmin" required>
                        <option value="">-- Selectionner --</option>
                        <?php foreach ($modules as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['code_module'] . ' - ' . $m['intitule']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Note (/20)</label>
                    <input type="number" name="note" min="0" max="20" step="0.25" required>
                </div>
                
                <button type="submit" class="btn-submit">Enregistrer la note</button>
            </form>
        </div>

        <!-- Tableau des dernieres notes -->
        <div class="table-container">
            <div class="table-header">
                <h3>Dernieres notes enregistrees</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Etudiant</th>
                        <th>Module</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dernieres_notes as $n): ?>
                    <tr>
                        <td><?= htmlspecialchars($n['matricule'] . ' - ' . $n['nom'] . ' ' . $n['prenom']) ?></td>
                        <td><span class="badge badge-code"><?= htmlspecialchars($n['code_module']) ?></span></td>
                        <td>
                            <?php if ($n['note'] >= 10): ?>
                                <strong style="color: var(--valid-green);"><?= $n['note'] ?></strong>
                            <?php else: ?>
                                <strong style="color: var(--invalid-red);"><?= $n['note'] ?></strong>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($dernieres_notes)): ?>
                    <tr><td colspan="3" class="empty-row">Aucune note enregistree.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#etudiantSelectAdmin').select2({
        placeholder: "-- Selectionner --",
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Aucun étudiant trouvé";
            }
        }
    });
    
    $('#moduleSelectAdmin').select2({
        placeholder: "-- Selectionner --",
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Aucun module trouvé";
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
