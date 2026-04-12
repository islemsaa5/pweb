<?php
require_once 'config.php';
requireLogin();

// Seul l'admin a accès à cette page
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$page_title = 'Interface Gestion des Modules';
$message = '';
$error = '';

// --- TRAITEMENT DU FORMULAIRE D'AJOUT/AFFECTATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // AJOUT D'UN MODULE
    if ($action === 'add') {
        $code = clean($_POST['code_module']);
        $intitule = clean($_POST['intitule']);
        $coefficient = (int)$_POST['coefficient'];
        $enseignant_id = !empty($_POST['enseignant_id']) ? (int)$_POST['enseignant_id'] : null;
        
        // Champs additionnels conservés en arrière-plan (facultatif selon l'énoncé)
        $section = clean($_POST['section'] ?? 'A');
        $semestre = (int)($_POST['semestre'] ?? 1);
        $credits = (int)($_POST['credits'] ?? 3);

        if (empty($code) || empty($intitule) || $coefficient < 1) {
            $error = 'Le code, l\'intitulé et le coefficient sont obligatoires.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO modules (code_module, intitule, coefficient, credits, semestre, section, enseignant_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $intitule, $coefficient, $credits, $semestre, $section, $enseignant_id]);
                $message = "Module créé et enseignant affecté avec succès !";
            } catch (PDOException $e) {
                $error = "Ce code module existe déjà ou une erreur SQL est survenue.";
            }
        }
    }
    // SUPPRESSION D'UN MODULE
    elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM modules WHERE id = ?")->execute([$id]);
        $message = "Le module a été supprimé.";
    }
}

// Récupérer la liste des modules avec l'enseignant responsable (conformément au point 5)
$modules = $pdo->query("
    SELECT m.*, e.nom as ens_nom, e.prenom as ens_prenom 
    FROM modules m 
    LEFT JOIN enseignants e ON m.enseignant_id = e.id
    ORDER BY m.intitule
")->fetchAll();

// Liste des enseignants pour le formulaire
$enseignants = $pdo->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Gestion des Modules</h1>
        <p>Interface d'administration du programme pédagogique</p>
    </div>

    <?php if ($message): ?><div class="msg-success animate-pop"><?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg-error animate-pop"><?= $error ?></div><?php endif; ?>

    <div style="margin-bottom: 20px;">
        <button class="btn-add" onclick="toggleModal('modalAdd')">➕ Ajouter un Module</button>
    </div>

    <!-- Affichage des modules sous forme de cartes structurées -->
    <div class="modules-grid">
        <?php foreach ($modules as $m): ?>
        <div class="module-card glass-effect" style="border-left: 5px solid #2c3e80;">
            <p style="font-size: 11px; color:#888; margin-bottom: 5px;">Section <?= $m['section'] ?> | Semestre <?= $m['semestre'] ?></p>
            <h4 style="color: #2c3e80; font-size: 16px; margin-bottom: 8px;"><?= htmlspecialchars($m['intitule']) ?></h4>
            <div style="font-size: 13px; line-height: 1.6;">
                <p><strong>Code:</strong> <span class="badge-code"><?= htmlspecialchars($m['code_module']) ?></span></p>
                <p><strong>Coefficient:</strong> <?= $m['coefficient'] ?></p>
                <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px;">
                    <p style="color: #555;"><strong>🎓 Enseignant responsable :</strong></p>
                    <p style="color: #2c3e80; font-weight: bold;">
                        <?= $m['enseignant_id'] ? htmlspecialchars($m['ens_nom'] . ' ' . $m['ens_prenom']) : '<span style="color:#f0ad4e;">Non affecté</span>' ?>
                    </p>
                </div>
            </div>
            
            <div class="actions" style="margin-top: 15px;">
                <form method="POST" onsubmit="return confirm('Supprimer ce module ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                    <button class="btn-action delete" style="width: 100%;">Supprimer le module</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if(empty($modules)): ?>
        <div class="empty-row" style="text-align: center; margin-top: 50px;">Aucun module disponible.</div>
    <?php endif; ?>
</div>

<!-- Modal conforme à l'énoncé (Point 5) -->
<div class="modal-overlay" id="modalAdd" style="display: none;">
    <div class="modal animate-pop">
        <div class="modal-header">
            <h3>Configuration du Module</h3>
            <button class="modal-close" onclick="toggleModal('modalAdd')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Code module *</label>
                    <input type="text" name="code_module" placeholder="ex: PWEB" required>
                </div>

                <div class="form-group">
                    <label>Intitulé du module *</label>
                    <input type="text" name="intitule" placeholder="ex: Programmation Web" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Coefficient *</label>
                        <input type="number" name="coefficient" min="1" max="10" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>Section (Optionnel)</label>
                        <select name="section">
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C" selected>Section C</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Enseignant responsable</label>
                    <select name="enseignant_id">
                        <option value="">-- Aucun enseignant affecté --</option>
                        <?php foreach ($enseignants as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Champs cachés pour compatibilité avec le reste du système USTHB -->
                <input type="hidden" name="credits" value="3">
                <input type="hidden" name="semestre" value="1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="toggleModal('modalAdd')">Annuler</button>
                <button type="submit" class="btn-add">Valider & Créer le Module</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal(id) {
    const modal = document.getElementById(id);
    modal.style.display = (modal.style.display === 'none') ? 'flex' : 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
