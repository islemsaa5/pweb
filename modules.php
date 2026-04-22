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

$page_title = 'Gestion des Modules par Semestre';
$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $code = clean($_POST['code_module']);
        $intitule = clean($_POST['intitule']);
        $coeff = (int)$_POST['coefficient'];
        $semestre = (int)$_POST['semestre'];
        $credits = (int)$_POST['credits'];
        $section = clean($_POST['section']);
        $enseignant_id = !empty($_POST['enseignant_id']) ? (int)$_POST['enseignant_id'] : null;

        if (empty($code) || empty($intitule)) {
            $error = 'Le code et l\'intitulé sont obligatoires.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO modules (code_module, intitule, coefficient, credits, semestre, section, enseignant_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $intitule, $coeff, $credits, $semestre, $section, $enseignant_id]);
                $message = "Module pour le Semestre $semestre ajouté.";
            } catch (PDOException $e) { $error = "Erreur : " . $e->getMessage(); }
        }
    }
    elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        $pdo->prepare("DELETE FROM modules WHERE id = ?")->execute([$id]);
        $message = "Module supprimé.";
    }
}

$modules_s1 = $pdo->query("SELECT m.*, e.nom, e.prenom, e.specialite FROM modules m LEFT JOIN enseignants e ON m.enseignant_id = e.id WHERE m.semestre = 1 ORDER BY m.intitule")->fetchAll();
$modules_s2 = $pdo->query("SELECT m.*, e.nom, e.prenom, e.specialite FROM modules m LEFT JOIN enseignants e ON m.enseignant_id = e.id WHERE m.semestre = 2 ORDER BY m.intitule")->fetchAll();
$enseignants = $pdo->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Programmation Pédagogique</h1>
        <p>Organisation des modules par semestre</p>
    </div>

    <?php if ($message): ?><div class="msg-success"><?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>

    <div style="margin-bottom: 20px;">
        <button class="btn-add" onclick="toggleModal('modalAdd')"><i class="fa-solid fa-plus"></i> Ajouter un Module</button>
    </div>

    <?php foreach ([1 => $modules_s1, 2 => $modules_s2] as $num => $list): ?>
        <h2 style="margin: 30px 0 15px; color: #2c3e80; border-left: 4px solid #2c3e80; padding-left: 10px;">Semestre <?= $num ?></h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Intitulé du Module</th>
                        <th>Section</th>
                        <th>Coeff</th>
                        <th>Crédits</th>
                        <th>Enseignant Responsable</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $m): ?>
                    <tr>
                        <td style="text-align: center;"><span class="badge-code"><?= htmlspecialchars($m['code_module']) ?></span></td>
                        <td><strong><?= htmlspecialchars($m['intitule']) ?></strong></td>
                        <td style="text-align: center;">Sec <?= $m['section'] ?></td>
                        <td style="text-align: center;"><?= $m['coefficient'] ?></td>
                        <td style="text-align: center;"><?= $m['credits'] ?></td>
                        <td>
                            <?php if ($m['enseignant_id']): ?>
                                <strong>Prof. <?= htmlspecialchars($m['nom'] . ' ' . $m['prenom']) ?></strong><br>
                                <small style="color: #666; font-style: italic;"><?= htmlspecialchars($m['specialite']) ?></small>
                            <?php else: ?>
                                <span style="color:#d9534f; font-style:italic;">Non affecté</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Supprimer ce module ?');" style="display: flex; justify-content: center;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn-action delete" style="padding: 5px 10px;"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($list)): ?>
                        <tr><td colspan="7" class="empty-row">Aucun module pour le S<?= $num ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal-overlay" id="modalAdd" style="display: none;">
    <div class="modal">
        <div class="modal-header"><h3>Nouveau Module</h3><button class="modal-close" onclick="toggleModal('modalAdd')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-row"><div class="form-group"><label>Code *</label><input type="text" name="code_module" required></div><div class="form-group"><label>Intitulé *</label><input type="text" name="intitule" required></div></div>
                <div class="form-row"><div class="form-group"><label>Semestre</label><select name="semestre"><option value="1">Semestre 1</option><option value="2">Semestre 2</option></select></div><div class="form-group"><label>Section</label><select name="section"><option value="A">Section A</option><option value="B">Section B</option><option value="C" selected>Section C</option></select></div></div>
                <div class="form-row"><div class="form-group"><label>Coeff</label><input type="number" name="coefficient" value="1" min="1"></div><div class="form-group"><label>Credits</label><input type="number" name="credits" value="3" min="1"></div></div>
                <div class="form-group"><label>Enseignant</label><select name="enseignant_id"><option value="">-- Aucun --</option><?php foreach ($enseignants as $e): ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-cancel" onclick="toggleModal('modalAdd')">Annuler</button><button type="submit" class="btn-add">Enregistrer</button></div>
        </form>
    </div>
</div>

<script>function toggleModal(id){const m=document.getElementById(id);m.style.display=(m.style.display==='none')?'flex':'none';}</script>
<?php include 'includes/footer.php'; ?>
