<?php
require_once 'config.php';
requireLogin();

if ($_SESSION['role'] !== 'enseignant') {
    header('Location: index.php');
    exit;
}

$page_title = 'Saisie des Notes';
$user_id = $_SESSION['user_id'];
$message = ''; $error = '';

$modules = $pdo->prepare("SELECT * FROM modules WHERE enseignant_id = ? ORDER BY intitule");
$modules->execute([$user_id]);
$modules = $modules->fetchAll();

$etudiants = $pdo->query("SELECT id, matricule, nom, prenom FROM etudiants ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etudiant_id = (int)$_POST['etudiant_id'];
    $module_id   = (int)$_POST['module_id'];
    $note        = (float)$_POST['note'];

    $check = $pdo->prepare("SELECT id FROM modules WHERE id = ? AND enseignant_id = ?");
    $check->execute([$module_id, $user_id]);

    if (!$check->fetch()) {
        $error = 'Vous n\'etes pas autorise a saisir des notes pour ce module.';
    } elseif (empty($etudiant_id) || empty($module_id)) {
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
        $message = 'Note enregistree / mise a jour avec succes.';
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Saisie des Notes</h1>
        <p>Enregistrez les resultats de vos etudiants</p>
    </div>

    <?php if ($message): ?><div class="msg-success"><?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>

    <div class="notes-layout">
        <!-- Formulaire de saisie -->
        <div class="note-form-box">
            <h3>Nouvelle saisie</h3>
            <form method="POST">
                
                <div class="form-group">
                    <label>Votre Module</label>
                    <select name="module_id" required>
                        <option value="">-- Selectionner --</option>
                        <?php foreach ($modules as $m): ?>
                        <option value="<?= $m['id'] ?>">
                            <?= htmlspecialchars($m['code_module'] . ' - ' . $m['intitule']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Etudiant</label>
                    <select name="etudiant_id" required>
                        <option value="">-- Selectionner --</option>
                        <?php foreach ($etudiants as $e): ?>
                        <option value="<?= $e['id'] ?>">
                            <?= htmlspecialchars($e['matricule'] . ' - ' . $e['nom'] . ' ' . $e['prenom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Note (/20)</label>
                    <input type="number" name="note" min="0" max="20" step="0.25" placeholder="ex: 14.50" required>
                </div>
                
                <button type="submit" class="btn-submit">Valider</button>
            </form>
        </div>

        <!-- Informations -->
        <div class="table-container" style="padding: 20px;">
            <h3>Instructions</h3>
            <p style="margin-top: 10px; color: #555; line-height: 1.5;">
                Selectionnez votre module et un etudiant pour lui attribuer une note.
                Si une note existe deja pour ce module et cet etudiant, elle sera remplacee.
            </p>
            <br>
            <p style="color: #888; font-size: 13px;">
                Ceci est une version simplifiee du gestionnaire de notes.
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
